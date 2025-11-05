<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Client;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
                    // Prepare data untuk disimpan (sesuaikan dengan kolom yang ada di database)
                    $clientDataToSave = [
                        'code' => $data['code'] ?? '',
                        'company_name' => $data['company_name'],
                        'phone' => $data['phone'] ?? '',
                        'address' => $data['address'] ?? '',
                        'owner_name' => $data['owner_name'] ?? '',
                        'owner_role' => $data['owner_role'] ?? '',
                        'contact_person' => $data['contact_person'] ?? '',
                        'npwp' => $data['npwp'] ?? '',
                        'jenis_wp' => $data['jenis_wp'] ?? 'op',
                        'grade' => $data['grade'] ?? '',
                        'pph_25_reporting' => $data['pph_25_reporting'] ?? false,
                        'pph_23_reporting' => $data['pph_23_reporting'] ?? false,
                        'pph_21_reporting' => $data['pph_21_reporting'] ?? false,
                        'pph_4_reporting' => $data['pph_4_reporting'] ?? false,
                        'ppn_reporting' => $data['ppn_reporting'] ?? false,
                        'spt_reporting' => $data['spt_reporting'] ?? false,
                        'status' => $data['status'] ?? 'active',
                        'type' => $data['type'] ?? 'pt',
                    ];

                    $client = Client::updateOrCreate(
                        ['id' => $data['id']], // identifier unik
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
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Error syncing client', [
                        'client_id' => $data['id'] ?? 'unknown',
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
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
}
