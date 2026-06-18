# Smart Fingerprint Attendance & Door Access System

Aplikasi Laravel 12 untuk absensi fingerprint dan kontrol akses pintu berbasis mesin Biometrik Solution X606-S. Aplikasi ini digunakan admin kampus/lab untuk mengelola master data akademik, jadwal perkuliahan, perangkat fingerprint, enrollment fingerprint, absensi, akses pintu, laporan, detail pertemuan, dan audit log.

Laravel tidak membaca SDK ActiveX secara langsung. Integrasi perangkat dilakukan oleh middleware/service lokal yang berkomunikasi dengan mesin X606-S melalui SDK, lalu middleware mengirim status device, data scan, dan log akses pintu ke API Laravel.

## Teknologi

- Laravel 12
- Laravel Breeze authentication
- Blade
- Tailwind CSS
- MySQL
- Eloquent ORM
- Migration, seeder, factory
- API token sederhana untuk middleware device

## Install

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_fingerprint_attendance
DB_USERNAME=root
DB_PASSWORD=
DEVICE_API_TOKEN=local-device-token
```

## Setup Database

Buat database MySQL:

```sql
CREATE DATABASE IF NOT EXISTS smart_fingerprint_attendance
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Build asset dan jalankan server:

```bash
npm run build
php artisan serve
```

## Akun Default

```text
Email: admin@example.com
Password: password
```

## Struktur Modul

- Dashboard statistik
- Master Data: Admin/User, Dosen, Mahasiswa, Program Studi, Kelas, Mata Kuliah, Ruangan, Tahun Akademik
- Jadwal Perkuliahan
- Fingerprint: Device Fingerprint, Biometric Enrollment
- Absensi: Detail Pertemuan
- Akses Pintu: Manual Unlock
- Laporan: Per Kelas, Per Mata Kuliah, Per Dosen, Akses Ditolak
- Pengaturan: Audit Log

## API Device

Semua endpoint device memakai header:

```http
X-DEVICE-TOKEN: local-device-token
Accept: application/json
Content-Type: application/json
```

Endpoint:

- `POST /api/device/status`
- `POST /api/device/scan`
- `POST /api/device/door-log`

### POST /api/device/status

Request:

```json
{
  "device_code": "X606-LAB-001",
  "status": "online",
  "ip_address": "192.168.1.10"
}
```

Response:

```json
{
  "status": "accepted",
  "message": "Device status updated.",
  "device": {
    "device_code": "X606-LAB-001",
    "status": "online",
    "last_online_at": "2026-06-17T04:32:40.000000Z"
  }
}
```

### POST /api/device/scan

Request:

```json
{
  "device_code": "X606-LAB-001",
  "fingerprint_id": "X606-L-0001",
  "scan_time": "2026-06-15 08:00:00",
  "raw_payload": {
    "source": "local-middleware"
  }
}
```

Response dosen valid:

```json
{
  "status": "accepted",
  "message": "Dosen valid, pintu dibuka",
  "open_door": true,
  "unlock_duration": 5
}
```

Response mahasiswa sebelum dosen hadir:

```json
{
  "status": "accepted",
  "message": "Absensi mahasiswa berhasil, dosen belum hadir",
  "open_door": false
}
```

Response ditolak:

```json
{
  "status": "rejected",
  "message": "Tidak ada jadwal aktif atau kelas tidak sesuai",
  "open_door": false,
  "unlock_duration": 0
}
```

### POST /api/device/door-log

Request:

```json
{
  "device_code": "X606-LAB-001",
  "access_status": "granted",
  "open_door": true,
  "method": "manual_web",
  "reason": "Manual unlock dari operator"
}
```

Response:

```json
{
  "status": "accepted",
  "message": "Door access log stored.",
  "log_id": 1
}
```

## Catatan Integrasi X606-S

- Mesin X606-S berkomunikasi dengan middleware/service lokal melalui SDK/ActiveX.
- Middleware membaca event scan fingerprint dari mesin.
- Middleware mengirim `device_code`, `fingerprint_id`, `scan_time`, dan `raw_payload` ke API Laravel.
- Laravel memvalidasi jadwal, kelas, dosen, dan status pertemuan.
- Laravel mengembalikan `open_door` dan `unlock_duration`.
- Middleware yang menjalankan perintah buka pintu fisik melalui SDK.

## Keamanan Data Biometrik

Web hanya menyimpan `Fingerprint ID`.

Sistem tidak menyimpan:

- Gambar sidik jari mentah
- Template sidik jari mentah
- Data biometrik internal dari SDK

Data scan mentah dari middleware disimpan sebagai `raw_payload` hanya untuk audit teknis dan debugging. Jika payload dari SDK mengandung data sensitif, middleware harus membersihkannya sebelum mengirim ke Laravel.
