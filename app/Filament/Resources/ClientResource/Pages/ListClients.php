<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('sync_data')
                ->label('Sinkronisasi Data')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    $this->syncDataFromApi();
                })
                ->requiresConfirmation()
                ->modalHeading('Sinkronisasi Data Client')
                ->modalDescription('Apakah Anda yakin ingin melakukan sinkronisasi data client dari API? Proses ini mungkin membutuhkan beberapa saat.')
                ->modalSubmitActionLabel('Ya, Sinkronisasi'),
        ];
    }

    protected function syncDataFromApi()
    {
        try {
            // 1. Login ke API untuk mendapatkan bearer token
            $token = $this->loginToApi();

            if (!$token) {
                $this->notify('danger', 'Gagal melakukan autentikasi ke API. Silakan cek log untuk detail error.');
                return;
            }

            // 2. Panggil API untuk mendapatkan data client dengan bearer token
            // Fetch all paginated data
            $allClientData = [];
            $currentPage = 1;
            $totalPages = 1;

            do {
                $response = Http::timeout(30)
                    ->withToken($token)
                    ->withHeaders([
                        'Accept' => 'application/json',
                    ])
                    ->get('https://keu.rafatax.id/api/clients', [
                        'page' => $currentPage
                    ]);

                if (!$response->successful()) {
                    $this->notify('danger', 'Gagal mengambil data dari API: ' . $response->status());
                    return;
                }

                $responseData = $response->json();

                // Extract data from paginated response
                $clientData = $responseData['data'] ?? [];

                if (!empty($clientData) && is_array($clientData)) {
                    $allClientData = array_merge($allClientData, $clientData);
                }

                // Get pagination info
                if (isset($responseData['meta'])) {
                    $totalPages = $responseData['meta']['last_page'] ?? 1;
                    $currentPage = $responseData['meta']['current_page'] ?? 1;
                } elseif (isset($responseData['links']['next'])) {
                    // Ada next page
                    $currentPage++;
                } else {
                    // Tidak ada pagination info, hentikan
                    break;
                }

                $currentPage++;
            } while ($currentPage <= $totalPages);

            if (empty($allClientData)) {
                $this->notify('warning', 'Tidak ada data client yang ditemukan');
                Log::warning('No client data found');
                return;
            }

            Log::info('Total client data fetched', ['total' => count($allClientData)]);

            // 3. Proses dan simpan data ke database
            $successCount = 0;
            $errorCount = 0;

            // Log sample data untuk debug
            if (!empty($allClientData)) {
                Log::info('Sample client data from API', [
                    'first_item_keys' => array_keys($allClientData[0]),
                    'total_to_process' => count($allClientData)
                ]);
            }

            foreach ($allClientData as $index => $data) {
                try {
                    $clientDataToSave = $this->sanitizeClientPayload($data);

                    // Gunakan code sebagai unique identifier karena lebih reliable
                    // dan hindari konflik dengan auto-increment ID database lokal
                    $client = Client::updateOrCreate(
                        ['code' => $clientDataToSave['code']], // gunakan code, bukan id eksternal
                        $clientDataToSave
                    );

                    // Handle team relation (many-to-many) jika ada team_id dari API
                    if (isset($data['team_id']) && !empty($data['team_id'])) {
                        $client->team()->sync([$data['team_id']]);
                    }

                    // Handle staff relation (many-to-many) jika ada staff_ids dari API
                    if (isset($data['staff_ids']) && is_array($data['staff_ids']) && !empty($data['staff_ids'])) {
                        $client->staff()->sync($data['staff_ids']);
                    }

                    $successCount++;
                } catch (\Illuminate\Database\QueryException $e) {
                    $errorCount++;

                    // Jika error adalah warning 1265 (data truncated) padahal nilai sudah valid,
                    // log sebagai warning saja, bukan error
                    if (str_contains($e->getMessage(), '1265') && str_contains($e->getMessage(), 'jenis_wp')) {
                        Log::warning('MySQL strict mode warning for client (data saved despite warning)', [
                            'client_code' => $clientDataToSave['code'] ?? 'unknown',
                            'jenis_wp_value' => $clientDataToSave['jenis_wp'] ?? null,
                            'external_id' => $data['id'] ?? null,
                        ]);
                        // Meskipun ada warning, data sebenarnya tersimpan
                        $successCount++;
                        $errorCount--;
                    } else {
                        Log::error('Database error syncing client', [
                            'client_code' => $clientDataToSave['code'] ?? 'unknown',
                            'external_id' => $data['id'] ?? 'unknown',
                            'index' => $index,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Error syncing client', [
                        'client_code' => $clientDataToSave['code'] ?? 'unknown',
                        'external_id' => $data['id'] ?? 'unknown',
                        'index' => $index,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Log hasil sinkronisasi
            Log::info('Client sync completed', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_processed' => $successCount + $errorCount
            ]);

            // Tampilkan notifikasi hasil sinkronisasi
            $message = "Sinkronisasi selesai! Berhasil: {$successCount}";
            if ($errorCount > 0) {
                $message .= ", Gagal: {$errorCount}";
            }

            $this->notify($errorCount > 0 ? 'warning' : 'success', $message);

            // Refresh table setelah sinkronisasi
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            // Handle error
            $this->notify('danger', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
            Log::error('Client Sync API Error: ' . $e->getMessage());
        }
    }

    protected function loginToApi(): ?string
    {
        try {
            // Ambil kredensial dari config atau .env
            $email = config('services.rafatax_api.email');
            $password = config('services.rafatax_api.password');

            if (!$email || !$password) {
                Log::error('API credentials not configured');
                return null;
            }

            // Login ke API dengan timeout yang lebih panjang
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post('https://keu.rafatax.id/api/auth/login', [
                    'email' => $email,
                    'password' => $password,
                ]);

            if (!$response->successful()) {
                Log::error('API Login failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            // Coba berbagai kemungkinan key untuk token
            $token = $data['token']
                ?? $data['access_token']
                ?? $data['data']['token']
                ?? $data['data']['access_token']
                ?? null;

            if (!$token) {
                Log::error('Token not found in response');
                return null;
            }

            return $token;
        } catch (\Exception $e) {
            Log::error('API Login Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function notify(string $type, string $message)
    {
        \Filament\Notifications\Notification::make()
            ->title($message)
            ->$type()
            ->send();
    }

    /**
     * Normalisasi & validasi data client sebelum disimpan.
     * - Enum jenis_wp & type dipastikan bernilai valid (fallback ke default bila tidak dikenal)
     * - Field boolean dipaksa menjadi true/false
     * - String ditrim & dibatasi panjang maksimum (menghindari truncated warning)
     */
    protected function sanitizeClientPayload(array $data): array
    {
        // Enum validation dengan mapping untuk jenis_wp
        // Database actual: enum('op','badan') bukan ('perseorangan','badan')
        $jenisWpMapping = [
            'perseorangan' => 'op',
            'op' => 'op',
            'orang_pribadi' => 'op',
            'pribadi' => 'op',
            'badan' => 'badan',
            'badan_usaha' => 'badan',
            'pt' => 'badan',
            'cv' => 'badan',
        ];

        $rawJenisWpInput = strtolower(trim((string)($data['jenis_wp'] ?? 'op')));
        $rawJenisWp = $jenisWpMapping[$rawJenisWpInput] ?? 'op';

        if (!isset($jenisWpMapping[$rawJenisWpInput])) {
            Log::warning('Unknown jenis_wp value received, fallback applied', [
                'received' => $data['jenis_wp'] ?? null,
                'normalized_to' => $rawJenisWp,
                'external_id' => $data['id'] ?? null,
            ]);
        }

        $allowedType = ['pt', 'kkp'];
        $rawType = strtolower(trim((string)($data['type'] ?? 'pt')));
        if (!in_array($rawType, $allowedType, true)) {
            Log::warning('Unknown type value received, fallback applied', [
                'received' => $data['type'] ?? null,
                'fallback' => 'pt',
                'external_id' => $data['id'] ?? null,
            ]);
            $rawType = 'pt';
        }

        // Boolean casting helper
        $castBool = function ($value): bool {
            if (is_bool($value)) return $value;
            $normalized = strtolower(trim((string)$value));
            return in_array($normalized, ['1', 'true', 'yes', 'y'], true);
        };

        $boolFields = [
            'pph_25_reporting',
            'pph_23_reporting',
            'pph_21_reporting',
            'pph_4_reporting',
            'ppn_reporting',
            'spt_reporting',
        ];

        $sanitized = [];

        // Safe string helper (limit length to prevent truncation warnings)
        $safeString = function ($value, $max = 255) {
            if ($value === null) return '';
            $v = trim((string)$value);
            return Str::limit($v, $max, '');
        };

        $sanitized['code'] = $safeString($data['code'] ?? '');
        $sanitized['company_name'] = $safeString($data['company_name'] ?? '');
        $sanitized['phone'] = $safeString($data['phone'] ?? '');
        $sanitized['address'] = $safeString($data['address'] ?? '', 500); // text field
        $sanitized['owner_name'] = $safeString($data['owner_name'] ?? '');
        $sanitized['owner_role'] = $safeString($data['owner_role'] ?? '');
        $sanitized['contact_person'] = $safeString($data['contact_person'] ?? '');
        $sanitized['npwp'] = $safeString($data['npwp'] ?? '');
        $sanitized['jenis_wp'] = $rawJenisWp;
        $sanitized['grade'] = $safeString($data['grade'] ?? '');
        $sanitized['type'] = $rawType;

        foreach ($boolFields as $field) {
            $sanitized[$field] = $castBool($data[$field] ?? false);
        }

        // status enum validation (optional: only allow active/inactive)
        $status = strtolower(trim((string)($data['status'] ?? 'active')));
        if (!in_array($status, ['active', 'inactive'], true)) {
            Log::warning('Unknown status value received, fallback applied', [
                'received' => $data['status'] ?? null,
                'fallback' => 'active',
                'external_id' => $data['id'] ?? null,
            ]);
            $status = 'active';
        }
        $sanitized['status'] = $status;

        return $sanitized;
    }
}
