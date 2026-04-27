╔══════════════════════════════════════════════════════════════════════╗
║          SISTEM POINT OF SALE (POS) - TOKO WARIS                    ║
║          Backend Laravel 11 | REST API | MySQL                       ║
╚══════════════════════════════════════════════════════════════════════╝

Dibuat berdasarkan dokumen SRS Toko Waris
Prodi Sistem Informasi, Universitas Siliwangi 2025

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 PERSYARATAN SISTEM
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  - PHP >= 8.3
  - Composer >= 2.x
  - MySQL >= 8.0
  - Node.js (opsional, untuk assets)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🚀 CARA INSTALASI & MENJALANKAN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. CLONE / EXTRACT PROJECT
   Pastikan Anda berada di folder project:
   cd pos-toko-waris

2. INSTALL DEPENDENCIES
   composer install

3. KONFIGURASI ENVIRONMENT
   cp .env.example .env

   Edit file .env, sesuaikan:
     DB_DATABASE=pos_toko_waris
     DB_USERNAME=root
     DB_PASSWORD=your_password

   Opsional (konfigurasi toko):
     POS_STORE_NAME="Toko Waris"
     POS_STORE_ADDRESS="Jl. Contoh No. 1, Tasikmalaya"
     POS_TAX_RATE=0          (0 = tidak ada pajak, 11 = PPN 11%)

4. GENERATE APP KEY
   php artisan key:generate

5. BUAT DATABASE
   Buat database MySQL terlebih dahulu:
   CREATE DATABASE pos_toko_waris CHARACTER SET utf8mb4;

6. JALANKAN MIGRASI + SEEDER
   php artisan migrate --seed

   Output yang diharapkan:
   ✅ Users seeded: 1 pemilik, 2 kasir
   ✅ Kategori seeded: 8 kategori
   ✅ Pemasok seeded: 4 pemasok
   ✅ Produk seeded: 30 produk dengan stok
   ✅ Transaksi seeded: ~200+ transaksi (30 hari historis)

7. JALANKAN SERVER
   php artisan serve

   API tersedia di: http://localhost:8000/api

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
👤 AKUN DEMO (SETELAH SEED)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Role     | Username   | Password
  ---------|------------|-------------
  Pemilik  | pemilik    | password123
  Kasir 1  | kasir1     | password123
  Kasir 2  | kasir2     | password123

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📡 API ENDPOINT UTAMA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  POST   /api/auth/login                  Login (semua role)
  GET    /api/auth/me                     Info user aktif
  POST   /api/auth/logout                 Logout

  GET    /api/produk                      Daftar produk (+ search)
  GET    /api/produk/barcode/{barcode}    Cari by barcode (kasir)
  POST   /api/produk                      Tambah produk (pemilik)
  PUT    /api/produk/{id}                 Update produk (pemilik)
  DELETE /api/produk/{id}                 Hapus/nonaktifkan (pemilik)

  GET    /api/transaksi                   Daftar transaksi
  POST   /api/transaksi                   Buat transaksi baru
  GET    /api/transaksi/{id}              Detail transaksi
  POST   /api/transaksi/{id}/selesaikan   Bayar & selesaikan
  POST   /api/transaksi/{id}/batalkan     Batalkan transaksi
  DELETE /api/transaksi/{id}/item/{did}   Hapus item dari transaksi
  GET    /api/transaksi/{id}/struk        Data struk cetak

  GET    /api/stok                        Daftar stok
  GET    /api/stok/notifikasi             Notifikasi stok min & exp
  POST   /api/stok/{id}/tambah            Tambah stok manual
  POST   /api/stok/{id}/penyesuaian       Penyesuaian (opname)
  GET    /api/stok/{id}/riwayat           Riwayat pergerakan stok

  GET    /api/pemasok                     Daftar pemasok
  POST   /api/pemasok                     Tambah pemasok
  GET    /api/pemasok/{id}/riwayat-pembelian  Riwayat PO

  GET    /api/pembelian                   Daftar purchase order
  POST   /api/pembelian                   Buat PO baru
  POST   /api/pembelian/{id}/terima       Terima barang + update stok
  POST   /api/pembelian/{id}/batalkan     Batalkan PO

  GET    /api/users                       Manajemen pengguna (pemilik)
  POST   /api/users                       Tambah akun kasir
  POST   /api/users/{id}/reset-password   Reset password

  GET    /api/laporan/dashboard           Ringkasan hari ini
  GET    /api/laporan/penjualan           Laporan penjualan (periode)
  GET    /api/laporan/harian              Rekap per hari
  GET    /api/laporan/mingguan            7 hari terakhir
  GET    /api/laporan/bulanan             Laporan bulanan
  GET    /api/laporan/keuangan            HPP, laba bruto
  GET    /api/laporan/produk-terlaris     Top produk
  GET    /api/laporan/export/pdf          Export PDF
  GET    /api/laporan/export/excel        Export Excel (.xlsx)

  GET    /api/kategori                    Daftar kategori
  GET    /api/ping                        Health check

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📁 STRUKTUR PROJECT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  app/
  ├── Console/Commands/
  │   └── CekStokCommand.php          Artisan: cek stok harian
  ├── Exports/
  │   └── LaporanPenjualanExport.php  Export Excel
  ├── Helpers/
  │   └── ApiResponse.php             Trait response JSON konsisten
  ├── Http/
  │   ├── Controllers/Api/
  │   │   ├── AuthController.php      Login, logout, profile
  │   │   ├── ProdukController.php    CRUD produk + barcode
  │   │   ├── TransaksiController.php POS: buat, bayar, batal
  │   │   ├── StokController.php      Manajemen stok
  │   │   ├── PemasokController.php   CRUD pemasok
  │   │   ├── PembelianController.php Purchase order
  │   │   ├── UserController.php      Manajemen user
  │   │   ├── LaporanController.php   Laporan + export
  │   │   └── KategoriController.php  CRUD kategori
  │   └── Middleware/
  │       ├── RoleMiddleware.php       Role-based access (kasir/pemilik)
  │       └── ApiRequestLogger.php    Logging API request
  ├── Models/
  │   ├── User.php                    Pengguna (kasir/pemilik)
  │   ├── Produk.php                  Produk toko
  │   ├── Kategori.php                Kategori produk
  │   ├── Stok.php                    Data stok + low stock scope
  │   ├── StokMovement.php            Riwayat pergerakan stok
  │   ├── Transaksi.php               Header transaksi
  │   ├── DetailTransaksi.php         Item per transaksi
  │   ├── Pemasok.php                 Data pemasok
  │   ├── PembelianPemasok.php        Purchase order
  │   ├── DetailPembelianPemasok.php  Item per PO
  │   └── ActivityLog.php             Log aktivitas sistem
  └── Services/
      ├── TransaksiService.php        Business logic POS
      ├── StokService.php             Mutasi stok (masuk/keluar)
      ├── LaporanService.php          Query laporan & keuangan
      └── PembelianService.php        Business logic PO

  database/
  ├── migrations/                     6 file migration
  └── seeders/                        5 seeder realistis

  docs/
  └── POS-Toko-Waris.postman_collection.json

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ FITUR YANG DIIMPLEMENTASIKAN (dari SRS)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  FR-001  ✅ Transaksi via barcode/manual
  FR-002  ✅ Perhitungan otomatis (diskon per item + pajak)
  FR-003  ✅ Data struk lengkap (siap dikirim ke printer)
  FR-004  ✅ Stok berkurang otomatis saat transaksi selesai
  FR-005  ✅ Barang masuk via PO pemasok + update stok otomatis
  FR-006  ✅ Notifikasi stok minimal (scope + endpoint + artisan)
  FR-007  ✅ Pelacakan & notifikasi tanggal kedaluwarsa
  FR-008  ✅ Transaksi pending (simpan & lanjutkan)
  FR-009  ✅ Batalkan transaksi / hapus item per item
  FR-010  ✅ Laporan harian, mingguan, bulanan, keuangan
  FR-011  ✅ Manajemen user (CRUD, reset password, role-based access)
  FR-012  ✅ Manajemen pemasok + riwayat pembelian
  FR-013  ✅ API stateless → real-time via HTTP (siap SSE/websocket)
  FR-014  ✅ is_synced flag + device_id untuk simulasi offline sync

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🛠️  PERINTAH ARTISAN BERGUNA
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  php artisan route:list                  Lihat semua route
  php artisan pos:cek-stok               Cek stok minimal & kedaluwarsa
  php artisan pos:cek-stok --days=7      Cek kedaluwarsa 7 hari ke depan
  php artisan migrate:fresh --seed        Reset + seed ulang database
  php artisan schedule:run               Jalankan jadwal cek stok

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📮 DOKUMENTASI API (POSTMAN)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

  Import file: docs/POS-Toko-Waris.postman_collection.json
  ke Postman untuk mencoba semua endpoint dengan mudah.

  Token otomatis tersimpan setelah Login berhasil.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
