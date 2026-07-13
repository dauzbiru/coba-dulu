# MARS — Monitoring Assessment and Reporting System

Sistem monitoring dan assessment untuk gerai depot air minum isi ulang.

## Fitur

- **Manajemen Pengguna** - CRUD user dengan role admin/guest
- **Gerai (Outlet)** - CRUD gerai dengan import/export Excel
- **Kategori & Item** - Hierarki kategori checklist penilaian
- **Kriteria** - Opsi penilaian per item dengan bobot
- **Monitoring** - Assessment workflow: checkin → penilaian → temuan → grade
- **Pra-Monitoring** - Assessment sederhana tanpa periode semester
- **Ranking** - Peringkat skor antar gerai per periode
- **Reports** - Laporan dengan export PDF dan Excel

## Persyaratan

- PHP 8.3+
- Composer
- SQLite

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Akun Default

Setelah `db:seed`, login dengan:

- **Admin:** username `admin`, password `password`

## Artisan Commands

- `reports:cleanup` - Hapus laporan yang belum disubmit >5 jam (dijalankan otomatis tiap jam)
