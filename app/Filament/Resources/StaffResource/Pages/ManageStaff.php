<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\Staff;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ManageStaff extends ManageRecords
{
    protected static string $resource = StaffResource::class;

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
                ->modalHeading('Sinkronisasi Data Staff')
                ->modalDescription('Apakah Anda yakin ingin melakukan sinkronisasi data staff dari API? Proses ini mungkin membutuhkan beberapa saat.')
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

            // 2. Panggil API untuk mendapatkan data staff dengan bearer token
            $response = Http::timeout(30)
                ->withToken($token)
                ->withHeaders([
                    'Accept' => 'application/json',
                ])
                ->get('https://keu.rafatax.id/api/staff');

            if (!$response->successful()) {
                $this->notify('danger', 'Gagal mengambil data dari API: ' . $response->status());
                return;
            }

            $responseData = $response->json();

            // Handle paginated response - extract data array
            $staffData = $responseData['data'] ?? $responseData;

            if (empty($staffData) || !is_array($staffData)) {
                $this->notify('warning', 'Tidak ada data staff yang ditemukan');
                Log::warning('No staff data found', ['response' => $responseData]);
                return;
            }

            // 3. Proses dan simpan data ke database
            $successCount = 0;
            $errorCount = 0;

            foreach ($staffData as $index => $data) {
                try {
                    // Extract IDs from nested objects
                    $positionId = $data['position']['id'] ?? $data['position_id'] ?? $data['position_reference_id'] ?? null;
                    $departmentId = $data['department']['id'] ?? $data['department_id'] ?? $data['department_reference_id'] ?? null;

                    // Prepare data untuk disimpan
                    $staffDataToSave = [
                        'name' => $data['name'],
                        'birth_place' => $data['birth_place'] ?? '',
                        'birth_date' => $data['birth_date'],
                        'address' => $data['address'] ?? '',
                        'no_ktp' => $data['no_ktp'] ?? '',
                        'no_spk' => $data['no_spk'] ?? '',
                        'phone' => $data['phone'] ?? '',
                        'jenjang' => $data['jenjang'] ?? 'SMA',
                        'jurusan' => $data['jurusan'] ?? '',
                        'university' => $data['university'] ?? '',
                        'no_ijazah' => $data['no_ijazah'] ?? '',
                        'tmt_training' => $data['tmt_training'] ?? null,
                        'periode' => $data['periode'] ?? '',
                        'selesai_training' => $data['selesai_training'] ?? null,
                        'position_reference_id' => $positionId,
                        'department_reference_id' => $departmentId,
                        'is_active' => $data['is_active'] ?? true,
                    ];

                    $staff = Staff::updateOrCreate(
                        ['id' => $data['id']], // identifier unik
                        $staffDataToSave
                    );

                    // Handle team relation (many-to-many) jika ada team_id dari API
                    if (isset($data['team_id']) && !empty($data['team_id'])) {
                        // Sync team relation
                        $staff->team()->sync([$data['team_id']]);
                    }

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error('Error syncing staff', [
                        'staff_id' => $data['id'] ?? 'unknown',
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Log hasil sinkronisasi
            Log::info('Sync completed', [
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
            $this->refreshTable();
        } catch (\Exception $e) {
            // Handle error
            $this->notify('danger', 'Gagal melakukan sinkronisasi: ' . $e->getMessage());
            Log::error('Sync API Error: ' . $e->getMessage());
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

    protected function refreshTable()
    {
        $this->dispatch('$refresh');
    }
}
