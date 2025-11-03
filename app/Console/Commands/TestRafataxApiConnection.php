<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRafataxApiConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rafatax:test-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test koneksi ke Rafatax API dan login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing Rafatax API Connection ===');
        $this->newLine();

        // 1. Check config
        $this->info('1. Checking configuration...');
        $email = config('services.rafatax_api.email');
        $password = config('services.rafatax_api.password');
        $url = config('services.rafatax_api.url', 'https://keu.rafatax.id/api');

        $this->table(
            ['Config Key', 'Value', 'Status'],
            [
                ['RAFATAX_API_URL', $url, $url ? '✓' : '✗'],
                ['RAFATAX_API_EMAIL', $email ? substr($email, 0, 3) . '***' : 'NOT SET', $email ? '✓' : '✗'],
                ['RAFATAX_API_PASSWORD', $password ? '***' : 'NOT SET', $password ? '✓' : '✗'],
            ]
        );

        if (!$email || !$password) {
            $this->error('❌ Email atau password tidak dikonfigurasi!');
            $this->info('Tambahkan ke file .env:');
            $this->info('RAFATAX_API_EMAIL=your-email@example.com');
            $this->info('RAFATAX_API_PASSWORD=your-password');
            return 1;
        }

        $this->newLine();

        // 2. Test login
        $this->info('2. Testing login to API...');
        $loginUrl = $url . '/auth/login';
        $this->info("URL: {$loginUrl}");

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($loginUrl, [
                    'email' => $email,
                    'password' => $password,
                ]);

            $this->info("Response Status: {$response->status()}");

            if ($response->successful()) {
                $this->info('✅ Login berhasil!');

                $data = $response->json();
                $this->info('Response structure:');
                $this->line(json_encode(array_keys($data), JSON_PRETTY_PRINT));

                // Check for token
                $token = $data['token']
                    ?? $data['access_token']
                    ?? $data['data']['token'] ??
                    $data['data']['access_token']
                    ?? null;

                if ($token) {
                    $this->info('✅ Token ditemukan: ' . substr($token, 0, 20) . '...');

                    // 3. Test get staff with token
                    $this->newLine();
                    $this->info('3. Testing get staff data...');
                    $staffUrl = $url . '/staff';
                    $this->info("URL: {$staffUrl}");

                    $staffResponse = Http::timeout(30)
                        ->withToken($token)
                        ->withHeaders([
                            'Accept' => 'application/json',
                        ])
                        ->get($staffUrl);

                    $this->info("Response Status: {$staffResponse->status()}");

                    if ($staffResponse->successful()) {
                        $staffData = $staffResponse->json();

                        $this->info("✅ Berhasil mengambil data staff!");
                        $this->info("Response type: " . gettype($staffData));

                        // Check if data is wrapped in a key
                        if (is_array($staffData)) {
                            $this->info("Top level keys: " . json_encode(array_keys($staffData)));

                            // Try to find the actual staff array
                            $actualData = $staffData['data'] ?? $staffData['staff'] ?? $staffData;
                            $count = is_array($actualData) ? count($actualData) : 0;

                            $this->info("Total staff: {$count}");

                            if ($count > 0 && is_array($actualData)) {
                                $this->info('Sample data (first record):');
                                $this->line(json_encode($actualData[0] ?? [], JSON_PRETTY_PRINT));
                            } elseif ($count === 0) {
                                $this->info('Full response:');
                                $this->line(json_encode($staffData, JSON_PRETTY_PRINT));
                            }
                        } else {
                            $this->error('Response is not an array!');
                            $this->line(json_encode($staffData, JSON_PRETTY_PRINT));
                        }
                    } else {
                        $this->error("❌ Gagal mengambil data staff: {$staffResponse->status()}");
                        $this->error($staffResponse->body());
                    }
                } else {
                    $this->error('❌ Token tidak ditemukan dalam response!');
                    $this->info('Full response:');
                    $this->line(json_encode($data, JSON_PRETTY_PRINT));
                }
            } else {
                $this->error("❌ Login gagal dengan status: {$response->status()}");
                $this->error('Response body:');
                $this->line($response->body());

                // Check common issues
                if ($response->status() === 422) {
                    $this->warn('⚠️  Status 422 biasanya berarti validasi gagal. Cek format email dan password.');
                } elseif ($response->status() === 401) {
                    $this->warn('⚠️  Status 401 berarti kredensial salah.');
                } elseif ($response->status() === 404) {
                    $this->warn('⚠️  Status 404 berarti endpoint tidak ditemukan. Cek URL API.');
                }
            }
        } catch (\Exception $e) {
            $this->error('❌ Exception terjadi:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->info('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }

        $this->newLine();
        $this->info('=== Test selesai ===');
        return 0;
    }
}
