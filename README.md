# POS Toko Waris

POS Toko Waris adalah aplikasi Point of Sale berbasis Laravel 11 untuk membantu operasional toko, mulai dari pengelolaan produk, stok, transaksi kasir, pembelian dari pemasok, sampai laporan penjualan.

Project ini dikembangkan sebagai Tugas Besar mata kuliah Rancang Bangun Perangkat Lunak dengan studi kasus Toko Waris.

## Ringkasan Aplikasi

Aplikasi ini menyediakan REST API dan halaman web sederhana untuk kebutuhan operasional POS. Sistem mendukung dua role utama:

- `pemilik`: mengelola produk, stok, pemasok, pembelian, pengguna, dan laporan.
- `kasir`: melakukan transaksi penjualan, mencari produk, mencetak data struk, dan melihat stok.

## Fitur Utama

- Autentikasi login menggunakan Laravel Sanctum.
- Manajemen produk, kategori, barcode, harga, diskon, dan status aktif produk.
- Manajemen stok, stok minimal, notifikasi stok rendah, tanggal kedaluwarsa, dan riwayat mutasi stok.
- Transaksi penjualan dengan item produk, diskon, pajak, pembayaran, kembalian, struk, pembatalan, dan transaksi pending.
- Pengurangan stok otomatis saat transaksi selesai.
- Pembelian dari pemasok untuk menambah stok barang.
- Manajemen pemasok dan riwayat pembelian.
- Manajemen pengguna berdasarkan role.
- Laporan dashboard, laporan penjualan, laporan harian, mingguan, bulanan, keuangan, produk terlaris, export PDF, dan export Excel.
- Dokumentasi API melalui Postman collection.

## Teknologi

- PHP 8.3 atau lebih baru
- Laravel 11
- Laravel Sanctum
- MySQL 8
- Composer
- PHPUnit
- Maatwebsite Excel
- DomPDF

## Struktur Project

```text
app/
  Console/Commands/       Perintah artisan, termasuk cek stok
  Exports/                Export laporan ke Excel
  Helpers/                Helper response API
  Http/Controllers/Api/   Controller REST API
  Http/Middleware/        Middleware autentikasi, role, dan logger
  Models/                 Model database
  Services/               Business logic transaksi, stok, laporan, pembelian

database/
  migrations/             Struktur tabel database
  seeders/                Data awal pengguna, kategori, pemasok, produk, transaksi

docs/
  POS-Toko-Waris.postman_collection.json

resources/views/
  Halaman web kasir, dashboard, produk, transaksi, laporan, dan struk

routes/
  api.php                 Route REST API
  web.php                 Route halaman web

tests/
  Feature/                Pengujian fitur utama aplikasi
```

## Instalasi

Clone repository:

```bash
git clone https://github.com/fyasa21/pos-toko-waris.git
cd pos-toko-waris
```

Install dependency PHP:

```bash
composer install
```

Buat file environment:

```bash
cp .env.example .env
```

Untuk Windows CMD:

```cmd
copy .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Atur koneksi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pos_toko_waris
DB_USERNAME=root
DB_PASSWORD=
```

Buat database MySQL:

```sql
CREATE DATABASE pos_toko_waris CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Jalankan server:

```bash
php artisan serve
```

Aplikasi berjalan di:

```text
http://127.0.0.1:8000
```

API berjalan di:

```text
http://127.0.0.1:8000/api
```

## Akun Demo

Setelah menjalankan seeder, gunakan akun berikut:

| Role | Username | Password |
| --- | --- | --- |
| Pemilik | `pemilik` | `password123` |
| Kasir 1 | `kasir1` | `password123` |
| Kasir 2 | `kasir2` | `password123` |

## Endpoint API Utama

Autentikasi:

```text
POST /api/auth/login
GET  /api/auth/me
POST /api/auth/logout
```

Produk:

```text
GET    /api/produk
GET    /api/produk/{id}
GET    /api/produk/barcode/{barcode}
POST   /api/produk
PUT    /api/produk/{id}
DELETE /api/produk/{id}
```

Transaksi:

```text
GET    /api/transaksi
POST   /api/transaksi
GET    /api/transaksi/{id}
POST   /api/transaksi/{id}/item
PATCH  /api/transaksi/{id}/item/{detailId}
DELETE /api/transaksi/{id}/item/{detailId}
POST   /api/transaksi/{id}/selesaikan
POST   /api/transaksi/{id}/batalkan
GET    /api/transaksi/{id}/struk
```

Stok:

```text
GET  /api/stok
GET  /api/stok/notifikasi
GET  /api/stok/{produkId}/riwayat
PUT  /api/stok/{produkId}
POST /api/stok/{produkId}/tambah
POST /api/stok/{produkId}/penyesuaian
```

Laporan:

```text
GET /api/laporan/dashboard
GET /api/laporan/penjualan
GET /api/laporan/harian
GET /api/laporan/mingguan
GET /api/laporan/bulanan
GET /api/laporan/keuangan
GET /api/laporan/produk-terlaris
GET /api/laporan/export/pdf
GET /api/laporan/export/excel
```

Endpoint lain:

```text
GET  /api/kategori
GET  /api/pemasok
POST /api/pemasok
GET  /api/pembelian
POST /api/pembelian
GET  /api/users
POST /api/users
GET  /api/ping
```

## Dokumentasi Postman

Import file berikut ke Postman:

```text
docs/POS-Toko-Waris.postman_collection.json
```

Gunakan endpoint login terlebih dahulu untuk mendapatkan token. Token digunakan sebagai Bearer Token saat mengakses endpoint yang membutuhkan autentikasi.

## Perintah Artisan Berguna

```bash
php artisan route:list
php artisan migrate:fresh --seed
php artisan test
php artisan test --filter=ProdukTest
php artisan test --filter=TransaksiTest
php artisan pos:cek-stok
php artisan pos:cek-stok --days=7
```

## Alur Kerja Git

Branch utama:

- `main`: versi stabil.
- `develop`: branch pengembangan untuk anggota kelompok.

Sebelum mulai kerja:

```bash
git checkout develop
git pull origin develop
```

Setelah selesai mengubah kode:

```bash
git status
git add nama-file-yang-diubah
git commit -m "Nama: deskripsi perubahan"
git push origin develop
```

Hindari push langsung ke `main`. Perubahan dari `develop` dapat digabungkan ke `main` setelah diperiksa.

## Catatan Penting

Jangan commit file atau folder berikut:

```text
.env
vendor/
node_modules/
storage/logs/
storage/framework/cache/
storage/framework/sessions/
storage/framework/views/
```

File tersebut sudah diabaikan melalui `.gitignore`.

## Tim Pengembang

- Fathir Yasa Ramadhan
- Muhammad Luthfi Novansyah
- Rifza Hamdalah Putra
- Ghyfa Galang Ahmad Wahyudin
- Imam Budiansyah

## Lisensi

Project ini menggunakan lisensi MIT sesuai konfigurasi package project.
