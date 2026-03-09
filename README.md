# eLTA - Elektronik Laporan Tenaga Ahli

Sistem pelaporan kinerja harian tenaga ahli Disgulkarmat Provinsi DKI Jakarta, berbasis Laravel.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Compatible-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PHPWord](https://img.shields.io/badge/PHPWord-1.4-777BB4?style=for-the-badge&logo=php&logoColor=white)

## Ringkasan Aplikasi

eLTA dipakai untuk:
- Manajemen master data pegawai, paket pekerjaan, scope aktivitas, pejabat penandatangan, dan kontrak.
- Input kegiatan harian per bulan beserta upload foto bukti.
- Pengajuan cuti tahunan dengan pengurangan kuota otomatis.
- Generate laporan bulanan `.docx` berdasarkan template Word.
- Monitoring data lewat dashboard berbeda untuk admin dan pegawai.

## Teknologi yang Digunakan

### Backend
- PHP `^8.2`
- Laravel Framework `^12.0`
- Laravel Breeze (autentikasi)
- PhpOffice PHPWord `^1.4` untuk ekspor Word

### Frontend
- Blade
- Tailwind CSS
- Alpine.js
- Vite
- Chart.js via CDN (`https://cdn.jsdelivr.net/npm/chart.js`)

### Database
- MySQL/MariaDB (melalui driver default Laravel)

## Fitur Aplikasi

### Fitur Umum (semua user login)
- Login, register, logout, lupa password, reset password.
- Kelola profil (ubah nama/email/password, hapus akun).
- Akses dashboard.
- Kelola laporan bulanan milik sendiri (`reports`).
- Tambah/hapus kegiatan harian pada laporan.
- Upload banyak foto bukti per kegiatan.
- Kelola cuti (lihat, ajukan, batalkan).
- Ekspor laporan ke format Word (`.docx`).

### Fitur Admin
- Dashboard admin:
  - total pegawai
  - jumlah kontrak aktif
  - jumlah laporan bulan berjalan
  - jumlah pegawai cuti hari ini
  - daftar cuti terdekat
- CRUD master pegawai (`users`).
- CRUD master paket pekerjaan + scope aktivitas (`job_packages`, `scopes`).
- CRUD master pejabat penandatangan (`approvers`).
- CRUD kontrak pegawai (`contracts`).
- Lihat seluruh data cuti pegawai.

### Fitur Pegawai
- Dashboard pegawai:
  - total laporan
  - jumlah kegiatan bulan berjalan
  - target aktivitas dari paket pekerjaan pada kontrak aktif
  - kalkulator cuti tahunan (jatah, terpakai, sisa)
  - grafik produktivitas harian bulan berjalan
  - 5 aktivitas terakhir
- Buat laporan bulanan baru.
- Input kegiatan dengan pilihan scope dari paket kontrak.
- Input kegiatan non-scope (misal cuti/libur) dengan `scope_id = null`.

## Alur Bisnis Inti

### 1) Kontrak aktif sebagai basis laporan
- Saat pegawai membuat laporan, sistem wajib menemukan `activeContract` (berdasarkan tanggal hari ini).
- Jika tidak ada kontrak aktif, pembuatan laporan ditolak.
- Satu laporan mengikat ke satu kontrak melalui `contract_id`.

### 2) Kegiatan harian dan dokumentasi
- Kegiatan disimpan di `daily_tasks`.
- Foto bukti per kegiatan disimpan di `storage/app/public/tasks` dan path-nya dicatat di `task_images`.
- Hapus kegiatan akan menghapus file foto fisik sekaligus record DB.

### 3) Cuti terintegrasi ke laporan
- Saat cuti diajukan:
  - sistem cek kuota cuti tahunan dari penjumlahan `kuota_cuti` kontrak di tahun terkait
  - sistem menolak jika kuota habis
  - sistem menolak tanggal cuti ganda pada user yang sama
- Jika laporan bulan terkait belum ada, sistem akan membuat laporan otomatis (jika ada kontrak aktif pada tanggal cuti).
- Sistem otomatis menambah satu `daily_task` bertuliskan `Cuti: ...`.

### 4) Ekspor laporan Word
- Template: `resources/templates/template_laporan.docx`.
- Data yang diekspor:
  - identitas pegawai
  - data SPK/SPMK
  - tujuan/sasaran/ruang lingkup kontrak
  - tabel target vs realisasi berdasarkan scope
  - daftar kegiatan bulanan
  - lampiran foto dokumentasi
- File hasil ekspor dibuat sementara di `storage/app/public` lalu langsung didownload.

## Struktur Database

### Tabel Inti

#### `users`
- `id`
- `name`
- `email` (unique)
- `email_verified_at`
- `password`
- `nik` (nullable)
- `role` enum: `admin|pegawai` (default: `pegawai`)
- timestamps

#### `approvers`
- `id`
- `nama`
- `nip`
- `jabatan`
- timestamps

#### `job_packages`
- `id`
- `nama_paket`
- `approver_id` (nullable, FK -> `approvers.id`, `nullOnDelete`)
- timestamps

#### `scopes`
- `id`
- `job_package_id` (FK -> `job_packages.id`, `cascadeOnDelete`)
- `kode_aktivitas`
- `uraian`
- timestamps

#### `contracts`
- `id`
- `user_id` (FK -> `users.id`, `cascadeOnDelete`)
- `job_package_id` (FK -> `job_packages.id`, `cascadeOnDelete`)
- `nama_kontrak`
- `jabatan`
- `tujuan` (nullable)
- `sasaran` (nullable)
- `ruang_lingkup` (nullable)
- `tanggal_mulai`
- `tanggal_selesai`
- `spk_nomor` (nullable)
- `spk_tanggal` (nullable)
- `spmk_nomor` (nullable)
- `spmk_tanggal` (nullable)
- `kuota_cuti` (default `6`)
- timestamps

#### `reports`
- `id`
- `user_id` (FK -> `users.id`, `cascadeOnDelete`)
- `contract_id` (FK -> `contracts.id`, `cascadeOnDelete`)
- `bulan` (1-12)
- `tahun`
- `tanggal_cetak` (nullable)
- timestamps

#### `daily_tasks`
- `id`
- `report_id` (FK -> `reports.id`, `cascadeOnDelete`)
- `scope_id` (nullable, FK -> `scopes.id`, `nullOnDelete`)
- `tanggal`
- `deskripsi_pekerjaan`
- timestamps

#### `task_images`
- `id`
- `daily_task_id` (FK -> `daily_tasks.id`, `cascadeOnDelete`)
- `image_path`
- `caption` (nullable)
- timestamps

#### `leaves`
- `id`
- `user_id` (FK -> `users.id`, `cascadeOnDelete`)
- `tanggal_cuti`
- `keterangan`
- timestamps

### Relasi Utama (Ringkas)

```text
users 1---* contracts
users 1---* reports
users 1---* leaves

approvers 1---* job_packages
job_packages 1---* scopes
job_packages 1---* contracts

contracts 1---* reports
reports 1---* daily_tasks
scopes 1---* daily_tasks
daily_tasks 1---* task_images
```

## Seeder Default

Seeder utama: `DatabaseSeeder`.

Data yang dibuat:
- 1 akun admin
- 1 akun pegawai
- 1 pejabat penandatangan
- 1 paket pekerjaan
- 18 scope aktivitas default
- 1 kontrak aktif milik pegawai

## Akun Default Hasil Seed

### Admin
- Email: `admin@pemadam.jakarta.go.id`
- Password: `password123`

### Pegawai
- Email: `rizvan@pemadam.jakarta.go.id`
- Password: `password123`

## Instalasi dan Menjalankan Project

### Prasyarat
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL/MariaDB

### Langkah Setup

1. Clone repository
```bash
git clone https://github.com/jasinfo113/laporan.git
cd laporan
```

2. Install dependency
```bash
composer install
npm install
```

3. Salin environment
```bash
cp .env.example .env
```

4. Atur koneksi database di `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

5. Generate key aplikasi
```bash
php artisan key:generate
```

6. Migrasi dan seed data
```bash
php artisan migrate:fresh --seed
```

7. Buat symbolic link storage (wajib untuk foto)
```bash
php artisan storage:link
```

8. Jalankan aplikasi
```bash
npm run dev
php artisan serve
```

Akses di `http://127.0.0.1:8000`.

## Daftar Route Utama

- `/` : landing page
- `/dashboard` : dashboard (auth)
- `/reports` : CRUD laporan bulanan
- `/reports/{report}/tasks` : tambah kegiatan harian
- `/daily-tasks/{id}` : hapus kegiatan harian
- `/reports/{report}/export` : export Word
- `/leaves` : manajemen cuti
- `/contracts` : manajemen kontrak
- `/users` : master user (admin only)
- `/job_packages` : master paket/scope (admin only)
- `/approvers` : master pejabat (admin only)

## Struktur Folder Penting

- `app/Http/Controllers` : logika aplikasi
- `app/Models` : model + relasi Eloquent
- `database/migrations` : skema database
- `database/seeders` : data awal
- `resources/views` : halaman Blade
- `resources/templates/template_laporan.docx` : template ekspor laporan
- `storage/app/public/tasks` : foto bukti kegiatan (runtime)

## Catatan Implementasi

- Middleware admin menggunakan `App\\Http\\Middleware\\IsAdmin`.
- Dashboard dibedakan berdasarkan `role` (`admin`/`pegawai`).
- Verifikasi email tidak dipaksakan karena model `User` tidak mengimplementasikan `MustVerifyEmail`.
- Chart.js dipanggil via CDN, bukan npm package.
- Pada pembuatan laporan manual, kontrak yang dipakai adalah kontrak aktif saat ini (bukan otomatis berdasarkan bulan yang dipilih).

## Screenshot Aplikasi

<table>
  <tr>
    <td align="center"><strong>Landing Page</strong></td>
    <td align="center"><strong>Dashboard Pegawai</strong></td>
  </tr>
  <tr>
    <td><img src="public/screenshot/landing.png" alt="Landing Page"></td>
    <td><img src="public/screenshot/dashboard.png" alt="Dashboard Pegawai"></td>
  </tr>
  <tr>
    <td align="center"><strong>Halaman Cuti</strong></td>
    <td align="center"><strong>Profil Pengguna</strong></td>
  </tr>
  <tr>
    <td><img src="public/screenshot/leave.png" alt="Halaman Cuti"></td>
    <td><img src="public/screenshot/profile.png" alt="Profil Pengguna"></td>
  </tr>
  <tr>
    <td align="center"><strong>Hasil Laporan</strong></td>
    <td></td>
  </tr>
  <tr>
    <td><img src="public/screenshot/report.png" alt="Hasil Laporan"></td>
    <td></td>
  </tr>
</table>

## Pengembang

Rizvan Primadita  
Tenaga Ahli Web Programmer - Disgulkarmat Provinsi DKI Jakarta
