# Setup Sinkronisasi API Rafatax

## Deskripsi

Fitur ini memungkinkan sinkronisasi data staff dari API eksternal Rafatax yang menggunakan Laravel Sanctum authentication.

## Cara Setup

### 1. Konfigurasi Environment Variables

Tambahkan credential API ke file `.env` Anda:

```env
# Rafatax API Configuration
RAFATAX_API_URL=https://keu.rafatax.id/api
RAFATAX_API_EMAIL=your-api-email@example.com
RAFATAX_API_PASSWORD=your-api-password
```

**Penting:**

-   Ganti `your-api-email@example.com` dengan email yang valid untuk login ke API
-   Ganti `your-api-password` dengan password yang sesuai
-   Jangan commit file `.env` ke repository

### 2. Cara Menggunakan

1. Login ke aplikasi Filament
2. Buka halaman **Staff Management**
3. Klik tombol **"Sinkronisasi Data"** di bagian header (icon refresh berwarna biru)
4. Konfirmasi dialog yang muncul
5. Tunggu proses sinkronisasi selesai
6. Notifikasi akan muncul dengan informasi hasil sinkronisasi

### 3. Proses yang Terjadi

1. **Login ke API**: Sistem akan login ke API menggunakan credential dari `.env`
2. **Mendapatkan Bearer Token**: Token autentikasi akan didapat dari response login
3. **Mengambil Data Staff**: Request ke endpoint `/api/staff` dengan bearer token
4. **Menyimpan Data**: Data akan di-update atau dibuat baru menggunakan `updateOrCreate`
5. **Refresh Table**: Table akan otomatis di-refresh untuk menampilkan data terbaru

### 4. Mapping Field

Pastikan struktur response API sesuai dengan field di database:

| Field Database          | Field API               | Keterangan               |
| ----------------------- | ----------------------- | ------------------------ |
| id                      | id                      | Primary Key              |
| name                    | name                    | Nama staff               |
| birth_place             | birth_place             | Tempat lahir             |
| birth_date              | birth_date              | Tanggal lahir            |
| address                 | address                 | Alamat                   |
| no_ktp                  | no_ktp                  | Nomor KTP                |
| no_spk                  | no_spk                  | Nomor SPK                |
| phone                   | phone                   | Nomor telepon            |
| jenjang                 | jenjang                 | Jenjang pendidikan       |
| jurusan                 | jurusan                 | Jurusan                  |
| university              | university              | Universitas              |
| no_ijazah               | no_ijazah               | Nomor ijazah             |
| tmt_training            | tmt_training            | TMT Training             |
| periode                 | periode                 | Periode                  |
| selesai_training        | selesai_training        | Tanggal selesai training |
| position_reference_id   | position_reference_id   | ID Posisi                |
| department_reference_id | department_reference_id | ID Departemen            |
| is_active               | is_active               | Status aktif             |
| team_id                 | team_id                 | ID Team                  |

### 5. Troubleshooting

#### Error: "API credentials not configured"

-   Pastikan variabel `RAFATAX_API_EMAIL` dan `RAFATAX_API_PASSWORD` sudah diset di `.env`
-   Jalankan `php artisan config:clear` untuk clear cache config

#### Error: "Gagal melakukan autentikasi ke API"

-   Cek kredensial yang digunakan sudah benar
-   Pastikan akun API memiliki akses ke endpoint staff
-   Cek log di `storage/logs/laravel.log` untuk detail error

#### Error: "Gagal mengambil data dari API"

-   Pastikan endpoint API tersedia dan accessible
-   Cek apakah token yang didapat valid
-   Verifikasi URL API di config sudah benar

#### Data tidak muncul setelah sinkronisasi

-   Pastikan response API berisi data dalam format array
-   Cek log untuk melihat error pada proses individual staff
-   Verifikasi foreign key (position_reference_id, department_reference_id, team_id) sudah ada di database

### 6. Endpoint API yang Digunakan

**Login Endpoint:**

```
POST https://keu.rafatax.id/api/login
Content-Type: application/json

{
  "email": "your-email@example.com",
  "password": "your-password"
}
```

**Response Login:**

```json
{
    "token": "your-bearer-token-here"
}
```

atau

```json
{
    "access_token": "your-bearer-token-here"
}
```

**Staff Data Endpoint:**

```
GET https://keu.rafatax.id/api/staff
Authorization: Bearer {token}
```

**Response Staff:**

```json
[
  {
    "id": 1,
    "name": "John Doe",
    "birth_place": "Jakarta",
    "birth_date": "1990-01-01",
    ...
  }
]
```

### 7. Logging

Semua error dan proses sinkronisasi akan dicatat di log Laravel:

-   Lokasi: `storage/logs/laravel.log`
-   Error login API
-   Error fetching data
-   Error per-staff saat insert/update

### 8. Security Notes

⚠️ **Penting untuk keamanan:**

-   Jangan commit credential ke repository
-   Gunakan environment variables untuk menyimpan credential
-   Pastikan `.env` ada di `.gitignore`
-   Gunakan HTTPS untuk komunikasi dengan API
-   Pertimbangkan untuk menyimpan token dengan cache jika API memiliki rate limit
