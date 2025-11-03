# Troubleshooting - Rafatax API Sync

## Masalah: "Gagal melakukan autentikasi ke API"

Jika Anda mendapat error ini, ikuti langkah-langkah berikut untuk debugging:

## Langkah 1: Verifikasi Konfigurasi

### 1.1 Cek file `.env`

Pastikan kredensial sudah ditambahkan ke file `.env`:

```env
RAFATAX_API_URL=https://keu.rafatax.id/api
RAFATAX_API_EMAIL=your-email@example.com
RAFATAX_API_PASSWORD=your-password
```

### 1.2 Clear config cache

Setelah mengubah `.env`, jalankan:

```bash
php artisan config:clear
php artisan cache:clear
```

### 1.3 Test dengan command

Jalankan command untuk test koneksi:

```bash
php artisan rafatax:test-api
```

Command ini akan:

-   ✅ Cek apakah kredensial sudah dikonfigurasi
-   ✅ Test login ke API
-   ✅ Tampilkan response dan error secara detail
-   ✅ Test get data staff jika login berhasil

## Langkah 2: Cek Log File

Buka file log Laravel untuk melihat detail error:

```bash
# Windows
type storage\logs\laravel.log | Select-String "API"

# Linux/Mac
tail -f storage/logs/laravel.log | grep API
```

Cari log dengan keyword:

-   `Attempting API login`
-   `API Login Response`
-   `API Login failed`
-   `API Login Exception`

## Langkah 3: Common Issues & Solutions

### Issue 1: Kredensial tidak terkonfigurasi

**Log error:**

```
API credentials not configured
```

**Solusi:**

1. Pastikan `RAFATAX_API_EMAIL` dan `RAFATAX_API_PASSWORD` ada di `.env`
2. Jangan ada spasi atau karakter aneh
3. Jalankan `php artisan config:clear`

### Issue 2: HTTP Status 401 (Unauthorized)

**Log error:**

```
API Login failed: status: 401
```

**Solusi:**

1. Email atau password salah
2. Verifikasi kredensial bisa login di web/postman
3. Pastikan akun tidak terkunci
4. Cek tidak ada karakter spasi di awal/akhir password

### Issue 3: HTTP Status 422 (Validation Error)

**Log error:**

```
API Login failed: status: 422
```

**Solusi:**

1. Format email tidak valid
2. Password terlalu pendek
3. Cek response body untuk detail validasi error:
    ```bash
    tail storage/logs/laravel.log
    ```

### Issue 4: HTTP Status 404 (Not Found)

**Log error:**

```
API Login failed: status: 404
```

**Solusi:**

1. URL endpoint salah
2. Cek `RAFATAX_API_URL` di `.env`
3. Pastikan endpoint login adalah `/api/login`
4. Test manual dengan curl:
    ```bash
    curl -X POST https://keu.rafatax.id/api/login \
      -H "Content-Type: application/json" \
      -H "Accept: application/json" \
      -d '{"email":"your-email@example.com","password":"your-password"}'
    ```

### Issue 5: Token tidak ditemukan dalam response

**Log error:**

```
Token not found in response
```

**Solusi:**

1. Struktur response API berbeda dari yang diharapkan
2. Lihat log untuk melihat struktur response:
    ```
    API Login successful: response_keys: [...]
    ```
3. Update code jika token ada di key yang berbeda

### Issue 6: Connection Timeout

**Log error:**

```
Connection timed out
Connection refused
```

**Solusi:**

1. API server tidak bisa diakses
2. Cek koneksi internet
3. Cek firewall/proxy
4. Test dengan ping/curl:
    ```bash
    curl -I https://keu.rafatax.id/api/login
    ```

### Issue 7: SSL Certificate Error

**Log error:**

```
SSL certificate problem
cURL error 60
```

**Solusi:**

```php
// Sementara untuk development (TIDAK untuk production!)
Http::withOptions(['verify' => false])
    ->post(...)
```

## Langkah 4: Test Manual dengan Postman/Insomnia

### Request Login:

```
POST https://keu.rafatax.id/api/login
Headers:
  Content-Type: application/json
  Accept: application/json

Body (JSON):
{
  "email": "your-email@example.com",
  "password": "your-password"
}
```

### Expected Response:

```json
{
    "token": "1|abc123def456..."
}
```

atau

```json
{
    "access_token": "1|abc123def456..."
}
```

atau

```json
{
    "data": {
        "token": "1|abc123def456..."
    }
}
```

## Langkah 5: Debug Mode

### Aktifkan detailed logging

Edit file `ManageStaff.php` dan pastikan logging sudah aktif (sudah diimplementasikan di update terbaru).

### Cek log real-time saat sync

```bash
# Terminal 1: Watch log
tail -f storage/logs/laravel.log

# Terminal 2: Trigger sync dari browser
# Klik tombol "Sinkronisasi Data"
```

## Langkah 6: Test dengan Tinker

```bash
php artisan tinker
```

```php
// Test config
config('services.rafatax_api.email')
config('services.rafatax_api.password')

// Test HTTP request
$response = \Http::post('https://keu.rafatax.id/api/login', [
    'email' => config('services.rafatax_api.email'),
    'password' => config('services.rafatax_api.password')
]);

$response->status()
$response->successful()
$response->json()
```

## Langkah 7: Contact API Administrator

Jika semua langkah di atas sudah dicoba dan masih gagal:

1. **Verifikasi dengan admin API:**

    - Apakah endpoint `/api/login` tersedia?
    - Apakah akun Anda memiliki akses?
    - Apakah ada whitelist IP?
    - Format request yang benar?

2. **Informasi yang perlu disiapkan:**
    - Error message lengkap dari log
    - HTTP status code
    - Response body dari API
    - Timestamp error terjadi

## Quick Reference: Command yang Berguna

```bash
# Test koneksi API
php artisan rafatax:test-api

# Clear semua cache
php artisan optimize:clear

# Clear config cache
php artisan config:clear

# Clear cache
php artisan cache:clear

# Lihat log terbaru
tail -20 storage/logs/laravel.log

# Watch log real-time
tail -f storage/logs/laravel.log

# Search API errors in log
grep "API" storage/logs/laravel.log
```

## Checklist Troubleshooting

-   [ ] Kredensial sudah ada di `.env`
-   [ ] Config cache sudah di-clear
-   [ ] Test dengan `php artisan rafatax:test-api`
-   [ ] Cek log file untuk detail error
-   [ ] Test manual dengan Postman/curl
-   [ ] Verifikasi kredensial bisa login di sistem lain
-   [ ] Cek koneksi internet dan firewall
-   [ ] Hubungi admin API jika masih gagal
