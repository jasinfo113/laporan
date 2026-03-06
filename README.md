# E-Laporan Tenaga Ahli (ELTA)

Sistem pelaporan kinerja harian tenaga ahli Disgulkarmat Provinsi DKI Jakarta.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PHPWord](https://img.shields.io/badge/PHPWord-777BB4?style=for-the-badge&logo=php&logoColor=white)

## Deskripsi

**E-Laporan Tenaga Ahli (ELTA)** adalah aplikasi yang digunakan untuk mengelola:

- Pencatatan kegiatan harian
- Manajemen kontrak kerja
- Pengajuan dan perhitungan cuti
- Generate laporan bulanan Microsoft Word (`.docx`)

## Fitur Utama

- Auto-generate laporan Word lengkap dengan data aktivitas
- Role-based access (`Admin` dan `Pegawai`)
- Riwayat kontrak
- Kalkulasi cuti terintegrasi dengan laporan
- Dashboard interaktif berbasis Chart.js
- UI responsif (mobile dan desktop)

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

## Prasyarat

Pastikan environment sudah memiliki:

- PHP 8.1+
- Composer
- Node.js dan npm
- MySQL atau MariaDB

## Instalasi

1. Clone repository

```bash
git clone https://github.com/jasinfo113/laporan.git
cd laporan
```

2. Install dependensi

```bash
composer install
npm install
```

3. Setup environment

```bash
cp .env.example .env
```

Lalu atur `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` pada file `.env`.

4. Generate app key

```bash
php artisan key:generate
```

5. Migrasi dan seed database

```bash
php artisan migrate:fresh --seed
```

6. Link storage

```bash
php artisan storage:link
```

7. Build asset dan jalankan server

```bash
npm run build
php artisan serve
```

Akses aplikasi di `http://localhost:8000`.

## Akun Default

### Admin
- Email: `admin@pemadam.jakarta.go.id`
- Password: `password`

### Pegawai
- Email: `rizvan@pemadam.jakarta.go.id`
- Password: `password`

## Pengembang

**Rizvan Primadita** ![Static Badge](https://img.shields.io/badge/my_github-da2724?style=for-the-badge&logo=git&logoColor=299bd3&link=https%3A%2F%2Fgithub.com%2Fr12van)

Tenaga Ahli Web Programmer - Disgulkarmat Provinsi DKI Jakarta
