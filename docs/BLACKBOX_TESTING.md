# BlackBox Testing POS Toko Waris

Dokumen ini berisi rancangan BlackBox Testing untuk aplikasi POS Toko Waris. Pengujian difokuskan pada perilaku sistem dari sisi pengguna dan API client tanpa melihat implementasi internal kode program.

## 1. Identitas Pengujian

| Item | Keterangan |
| --- | --- |
| Nama sistem | POS Toko Waris |
| Jenis pengujian | BlackBox Testing |
| Platform | Web dan REST API |
| Framework aplikasi | Laravel 11 |
| Metode utama | Equivalence Partitioning, Boundary Value Analysis, Decision Table, dan Scenario Testing |
| Role pengguna | Pemilik dan Kasir |
| Basis endpoint | `http://127.0.0.1:8000/api` |

## 2. Tujuan Pengujian

1. Memastikan fitur utama berjalan sesuai kebutuhan pengguna.
2. Memastikan validasi input menerima data valid dan menolak data tidak valid.
3. Memastikan hak akses role `pemilik` dan `kasir` diterapkan dengan benar.
4. Memastikan proses transaksi, pembayaran, stok, pembelian, dan laporan menghasilkan keluaran yang sesuai.
5. Memastikan respons error tampil saat data tidak ditemukan, stok tidak cukup, atau input tidak lengkap.

## 3. Ruang Lingkup Pengujian

| Modul | Cakupan |
| --- | --- |
| Autentikasi | Login, logout, profil, ganti password, session web |
| Hak akses | Akses pemilik, akses kasir, akses tanpa token |
| Produk | Daftar produk, detail, tambah, ubah, nonaktifkan, barcode |
| Kategori | Daftar, tambah, ubah, hapus kategori |
| Stok | Daftar stok, notifikasi stok rendah, tambah stok, penyesuaian stok, riwayat stok |
| Transaksi | Buat transaksi, tambah item, ubah item, hapus item, selesaikan, batalkan, struk |
| Pemasok | Daftar, tambah, ubah, nonaktifkan, riwayat pembelian |
| Pembelian | Buat purchase order, terima pembelian, batalkan pembelian |
| Pengguna | Daftar user, tambah user, ubah user, nonaktifkan user, reset password |
| Laporan | Dashboard, penjualan, harian, mingguan, bulanan, keuangan, produk terlaris, export PDF/Excel |
| Web | Login, dashboard, kasir, produk, transaksi, laporan, struk |

## 4. Data Uji Awal

Data berikut dapat dibuat melalui seeder atau input manual.

| Data | Nilai |
| --- | --- |
| Akun pemilik | username `pemilik`, password `password123`, role `pemilik` |
| Akun kasir | username `kasir1`, password `password123`, role `kasir` |
| Kategori | Sembako |
| Produk valid | Kode `PRD-001`, nama `Beras 5kg`, harga beli `60000`, harga jual `65000`, stok awal `20` |
| Produk stok rendah | Kode `PRD-LOW`, stok `2`, stok minimal `10` |
| Produk barcode | Barcode `8999999999999` |
| Pemasok | `PT Sumber Waris` |
| Metode pembayaran valid | `cash`, `cashless`, `qris`, `transfer` |

## 5. Matriks Hak Akses

| Fitur | Tanpa login | Kasir | Pemilik |
| --- | --- | --- | --- |
| Login | Bisa | Bisa | Bisa |
| Lihat produk | Tidak | Bisa | Bisa |
| Tambah/ubah/hapus produk | Tidak | Tidak | Bisa |
| Lihat stok | Tidak | Bisa | Bisa |
| Tambah/penyesuaian stok | Tidak | Tidak | Bisa |
| Transaksi penjualan | Tidak | Bisa | Bisa |
| Lihat transaksi kasir lain | Tidak | Tidak | Bisa |
| Pemasok dan pembelian | Tidak | Tidak | Bisa |
| Manajemen pengguna | Tidak | Tidak | Bisa |
| Laporan dan export | Tidak | Tidak | Bisa |

## 6. Teknik BlackBox

### 6.1 Equivalence Partitioning

| Field | Kelas valid | Kelas tidak valid |
| --- | --- | --- |
| `username` login | Terdaftar dan aktif | Kosong, tidak terdaftar, role tidak sesuai |
| `password` login | Sesuai akun | Kosong, salah |
| `role` login | `kasir`, `pemilik` | Selain `kasir` dan `pemilik` |
| `harga_beli` | Angka >= 0 | Negatif, teks, kosong saat tambah produk |
| `harga_jual` | Angka >= `harga_beli` | Lebih kecil dari `harga_beli`, negatif, teks |
| `diskon_persen` | 0 sampai 100 | Kurang dari 0, lebih dari 100 |
| `jumlah` item transaksi | Integer >= 1 | 0, negatif, desimal, teks |
| `jumlah_bayar` | Angka >= total pembayaran | Kurang dari total, negatif, teks |
| `stok_minimal` | Integer >= 0 | Negatif, desimal, teks |
| `email` | Format email valid | Format email salah |

### 6.2 Boundary Value Analysis

| Field | Nilai batas yang diuji | Expected result |
| --- | --- | --- |
| `stok_minimal` | 0 | Diterima |
| `stok_minimal` | -1 | Ditolak |
| `jumlah` item | 1 | Diterima |
| `jumlah` item | 0 | Ditolak |
| `diskon_persen` | 0 dan 100 | Diterima |
| `diskon_persen` | -1 dan 101 | Ditolak |
| `per_page` | 100 | Diterima maksimal 100 data per halaman |
| `per_page` | 101 | Sistem membatasi menjadi maksimal 100 |
| `password_baru` | 6 karakter | Diterima |
| `password_baru` | 5 karakter | Ditolak |

## 7. Test Case

Kolom "Status" diisi setelah pengujian manual atau via Postman dilakukan.

| ID | Modul | Skenario | Input/Langkah | Expected result | Status |
| --- | --- | --- | --- | --- | --- |
| BB-AUTH-001 | Autentikasi | Login pemilik berhasil | POST `/api/auth/login` dengan username `pemilik`, password benar, role `pemilik` | Status 200, token dan data user dikembalikan | Belum diuji |
| BB-AUTH-002 | Autentikasi | Login kasir berhasil | POST `/api/auth/login` dengan username `kasir1`, password benar, role `kasir` | Status 200, token dan role `kasir` dikembalikan | Belum diuji |
| BB-AUTH-003 | Autentikasi | Login gagal karena password salah | Password diisi salah | Status 422, login ditolak | Belum diuji |
| BB-AUTH-004 | Autentikasi | Login gagal karena role tidak sesuai | Akun kasir login dengan role `pemilik` | Status 422, login ditolak | Belum diuji |
| BB-AUTH-005 | Autentikasi | Login gagal karena field kosong | Username/password/role kosong | Status 422, error validasi muncul | Belum diuji |
| BB-AUTH-006 | Autentikasi | Akses endpoint tanpa token | GET `/api/auth/me` tanpa Bearer Token | Status 401 atau diarahkan sebagai unauthenticated | Belum diuji |
| BB-AUTH-007 | Autentikasi | Melihat profil user aktif | GET `/api/auth/me` memakai token valid | Status 200, data user sesuai token | Belum diuji |
| BB-AUTH-008 | Autentikasi | Ganti password berhasil | PUT `/api/auth/change-password` dengan password lama benar dan konfirmasi password baru sesuai | Status 200, user diminta login ulang | Belum diuji |
| BB-AUTH-009 | Autentikasi | Ganti password gagal | Password lama salah | Status 422, password tidak berubah | Belum diuji |
| BB-AUTH-010 | Autentikasi | Logout berhasil | POST `/api/auth/logout` dengan token valid | Status 200, token tidak dapat dipakai kembali | Belum diuji |
| BB-ROLE-001 | Hak akses | Kasir tidak bisa akses laporan | Token kasir GET `/api/laporan/dashboard` | Status 403 | Belum diuji |
| BB-ROLE-002 | Hak akses | Kasir tidak bisa tambah produk | Token kasir POST `/api/produk` | Status 403 | Belum diuji |
| BB-ROLE-003 | Hak akses | Pemilik bisa akses laporan | Token pemilik GET `/api/laporan/dashboard` | Status 200, data dashboard tampil | Belum diuji |
| BB-ROLE-004 | Hak akses | Pemilik bisa mengelola stok | Token pemilik POST `/api/stok/{produkId}/tambah` | Status 200, stok bertambah | Belum diuji |
| BB-PROD-001 | Produk | Menampilkan daftar produk aktif | GET `/api/produk` dengan token valid | Status 200, data dan pagination tampil | Belum diuji |
| BB-PROD-002 | Produk | Mencari produk berdasarkan kata kunci | GET `/api/produk?search=Beras` | Status 200, daftar berisi produk relevan | Belum diuji |
| BB-PROD-003 | Produk | Filter produk stok rendah | GET `/api/produk?stok_rendah=1` | Status 200, hanya produk stok rendah tampil | Belum diuji |
| BB-PROD-004 | Produk | Detail produk ditemukan | GET `/api/produk/{id}` dengan ID valid | Status 200, detail produk, kategori, stok tampil | Belum diuji |
| BB-PROD-005 | Produk | Detail produk tidak ditemukan | GET `/api/produk/999999` | Status 404 | Belum diuji |
| BB-PROD-006 | Produk | Tambah produk valid | POST `/api/produk` dengan kode unik, harga jual >= harga beli, stok awal valid | Status 201, produk dan stok dibuat | Belum diuji |
| BB-PROD-007 | Produk | Tambah produk ditolak karena kode duplikat | POST kode produk yang sudah ada | Status 422 | Belum diuji |
| BB-PROD-008 | Produk | Tambah produk ditolak karena harga jual lebih kecil dari harga beli | `harga_beli=10000`, `harga_jual=9000` | Status 422, produk tidak dibuat | Belum diuji |
| BB-PROD-009 | Produk | Tambah produk ditolak karena diskon lebih dari 100 | `diskon_persen=101` | Status 422 | Belum diuji |
| BB-PROD-010 | Produk | Update produk valid | PUT `/api/produk/{id}` dengan perubahan nama/harga/stok minimal | Status 200, data berubah | Belum diuji |
| BB-PROD-011 | Produk | Set stok minimal menjadi nol | PUT `/api/produk/{id}` dengan `stok_minimal=0` | Status 200, nilai stok minimal tersimpan 0 | Belum diuji |
| BB-PROD-012 | Produk | Nonaktifkan produk | DELETE `/api/produk/{id}` untuk produk tanpa transaksi pending | Status 200, `is_active=false` | Belum diuji |
| BB-PROD-013 | Produk | Produk pending tidak bisa dihapus | DELETE produk yang sedang ada di transaksi pending | Status 400, produk tetap aktif | Belum diuji |
| BB-PROD-014 | Produk | Lookup barcode berhasil | GET `/api/produk/barcode/8999999999999` | Status 200, data produk barcode tampil | Belum diuji |
| BB-PROD-015 | Produk | Lookup barcode gagal | GET barcode tidak terdaftar | Status 404 | Belum diuji |
| BB-KAT-001 | Kategori | Tampilkan kategori | GET `/api/kategori` | Status 200, daftar kategori tampil | Belum diuji |
| BB-KAT-002 | Kategori | Tambah kategori valid | POST `/api/kategori` dengan nama unik | Status 201, kategori dibuat | Belum diuji |
| BB-KAT-003 | Kategori | Tambah kategori duplikat | POST nama kategori yang sudah ada | Status 422 | Belum diuji |
| BB-KAT-004 | Kategori | Ubah kategori | PUT `/api/kategori/{id}` dengan nama valid | Status 200, kategori berubah | Belum diuji |
| BB-KAT-005 | Kategori | Hapus kategori tanpa produk | DELETE `/api/kategori/{id}` yang tidak dipakai | Status 200, kategori terhapus | Belum diuji |
| BB-KAT-006 | Kategori | Hapus kategori yang masih dipakai produk | DELETE kategori yang memiliki produk | Status 400, kategori tidak dihapus | Belum diuji |
| BB-STOK-001 | Stok | Tampilkan daftar stok | GET `/api/stok` | Status 200, data stok dan pagination tampil | Belum diuji |
| BB-STOK-002 | Stok | Filter stok rendah | GET `/api/stok?stok_rendah=1` | Status 200, data stok di bawah minimal tampil | Belum diuji |
| BB-STOK-003 | Stok | Filter kedaluwarsa | GET `/api/stok?kedaluwarsa=1&hari=30` | Status 200, produk mendekati kedaluwarsa tampil | Belum diuji |
| BB-STOK-004 | Stok | Tambah stok valid | POST `/api/stok/{produkId}/tambah` dengan `jumlah=7` | Status 200, stok bertambah 7 dan riwayat stok dibuat | Belum diuji |
| BB-STOK-005 | Stok | Tambah stok ditolak jumlah nol | `jumlah=0` | Status 422 | Belum diuji |
| BB-STOK-006 | Stok | Penyesuaian stok valid | POST `/api/stok/{produkId}/penyesuaian` dengan `jumlah_baru=15` | Status 200, stok menjadi 15 | Belum diuji |
| BB-STOK-007 | Stok | Penyesuaian stok menjadi nol | `jumlah_baru=0` | Status 200, stok menjadi 0 | Belum diuji |
| BB-STOK-008 | Stok | Notifikasi stok rendah | GET `/api/stok/notifikasi` saat ada stok di bawah minimal | Status 200, count stok minimal bertambah | Belum diuji |
| BB-STOK-009 | Stok | Riwayat stok produk | GET `/api/stok/{produkId}/riwayat` | Status 200, daftar movement stok tampil | Belum diuji |
| BB-TRX-001 | Transaksi | Buat transaksi valid | POST `/api/transaksi` dengan item produk valid dan jumlah 2 | Status 201, status `pending`, total sesuai harga item | Belum diuji |
| BB-TRX-002 | Transaksi | Buat transaksi tanpa item | `items=[]` | Status 422 | Belum diuji |
| BB-TRX-003 | Transaksi | Buat transaksi dengan jumlah nol | `jumlah=0` | Status 422 | Belum diuji |
| BB-TRX-004 | Transaksi | Buat transaksi produk tidak ada | `produk_id=999999` | Status 422 atau 404 sesuai respons validasi | Belum diuji |
| BB-TRX-005 | Transaksi | Buat transaksi melebihi stok | Jumlah item lebih besar dari stok tersedia | Status 400, transaksi gagal | Belum diuji |
| BB-TRX-006 | Transaksi | Tambah item ke transaksi pending | POST `/api/transaksi/{id}/item` dengan produk valid | Status 200, detail item bertambah atau jumlah tergabung | Belum diuji |
| BB-TRX-007 | Transaksi | Ubah jumlah item transaksi pending | PATCH `/api/transaksi/{id}/item/{detailId}` dengan jumlah baru | Status 200, subtotal dan total berubah | Belum diuji |
| BB-TRX-008 | Transaksi | Hapus item transaksi pending | DELETE `/api/transaksi/{id}/item/{detailId}` | Status 200, item terhapus dan total berubah | Belum diuji |
| BB-TRX-009 | Transaksi | Selesaikan transaksi cash dengan bayar cukup | POST `/api/transaksi/{id}/selesaikan` jumlah bayar >= total | Status 200, status `selesai`, stok berkurang, kembalian benar | Belum diuji |
| BB-TRX-010 | Transaksi | Selesaikan transaksi cash dengan bayar kurang | Jumlah bayar < total | Status 400, transaksi tetap belum lunas | Belum diuji |
| BB-TRX-011 | Transaksi | Selesaikan transaksi QRIS tanpa input bayar penuh | Metode `qris`, jumlah bayar 0 | Status 200, sistem menganggap lunas sesuai total | Belum diuji |
| BB-TRX-012 | Transaksi | Pembayaran non-cash kurang dari total | Metode `transfer`, jumlah bayar kurang | Status 400 | Belum diuji |
| BB-TRX-013 | Transaksi | Batalkan transaksi pending | POST `/api/transaksi/{id}/batalkan` | Status 200, status `batal` | Belum diuji |
| BB-TRX-014 | Transaksi | Ambil struk transaksi selesai | GET `/api/transaksi/{id}/struk` untuk transaksi selesai | Status 200, data toko, transaksi, tanggal cetak tampil | Belum diuji |
| BB-TRX-015 | Transaksi | Ambil struk transaksi pending | GET struk untuk transaksi belum selesai | Status 400, struk tidak tersedia | Belum diuji |
| BB-TRX-016 | Transaksi | Kasir tidak bisa melihat transaksi kasir lain | Token kasir A GET transaksi milik kasir B | Status 403 | Belum diuji |
| BB-TRX-017 | Transaksi | Pemilik melihat semua transaksi | Token pemilik GET `/api/transaksi` | Status 200, transaksi semua kasir dapat tampil | Belum diuji |
| BB-TRX-018 | Transaksi | Filter transaksi berdasarkan status | GET `/api/transaksi?status=selesai` | Status 200, data yang tampil berstatus selesai | Belum diuji |
| BB-PEM-001 | Pemasok | Tampilkan daftar pemasok | GET `/api/pemasok` | Status 200, daftar pemasok aktif tampil | Belum diuji |
| BB-PEM-002 | Pemasok | Tambah pemasok valid | POST `/api/pemasok` dengan nama pemasok valid | Status 201, pemasok dibuat | Belum diuji |
| BB-PEM-003 | Pemasok | Tambah pemasok tanpa nama | `nama_pemasok` kosong | Status 422 | Belum diuji |
| BB-PEM-004 | Pemasok | Ubah pemasok valid | PUT `/api/pemasok/{id}` | Status 200, data pemasok berubah | Belum diuji |
| BB-PEM-005 | Pemasok | Nonaktifkan pemasok | DELETE `/api/pemasok/{id}` | Status 200, `is_active=false` | Belum diuji |
| BB-PEM-006 | Pemasok | Lihat riwayat pembelian pemasok | GET `/api/pemasok/{id}/riwayat-pembelian` | Status 200, riwayat pembelian tampil | Belum diuji |
| BB-BELI-001 | Pembelian | Buat purchase order valid | POST `/api/pembelian` dengan pemasok dan item valid | Status 201, pembelian berstatus awal sesuai sistem | Belum diuji |
| BB-BELI-002 | Pembelian | Buat purchase order tanpa item | `items=[]` | Status 422 | Belum diuji |
| BB-BELI-003 | Pembelian | Terima pembelian | POST `/api/pembelian/{id}/terima` untuk PO valid | Status 200, status berubah dan stok bertambah | Belum diuji |
| BB-BELI-004 | Pembelian | Batalkan pembelian | POST `/api/pembelian/{id}/batalkan` | Status 200, status menjadi batal | Belum diuji |
| BB-BELI-005 | Pembelian | Detail pembelian tidak ditemukan | GET `/api/pembelian/999999` | Status 404 | Belum diuji |
| BB-USER-001 | Pengguna | Tampilkan daftar user | GET `/api/users` dengan token pemilik | Status 200, daftar user tampil tanpa password hash | Belum diuji |
| BB-USER-002 | Pengguna | Tambah user valid | POST `/api/users` dengan username/email unik dan password minimal 6 karakter | Status 201, user dibuat aktif | Belum diuji |
| BB-USER-003 | Pengguna | Tambah user email duplikat | Email sudah digunakan | Status 422 | Belum diuji |
| BB-USER-004 | Pengguna | Tambah user password terlalu pendek | Password 5 karakter | Status 422 | Belum diuji |
| BB-USER-005 | Pengguna | Ubah user valid | PUT `/api/users/{id}` | Status 200, data user berubah | Belum diuji |
| BB-USER-006 | Pengguna | Reset password user | POST `/api/users/{id}/reset-password` dengan password valid | Status 200, user harus login ulang | Belum diuji |
| BB-USER-007 | Pengguna | Pemilik tidak bisa menghapus akun sendiri | DELETE `/api/users/{id}` dengan id user sendiri | Status 400, akun tetap aktif | Belum diuji |
| BB-USER-008 | Pengguna | Nonaktifkan user lain | DELETE `/api/users/{id}` untuk user lain | Status 200, user tidak aktif | Belum diuji |
| BB-LAP-001 | Laporan | Dashboard laporan | GET `/api/laporan/dashboard` | Status 200, ringkasan hari ini dan bulan ini tampil | Belum diuji |
| BB-LAP-002 | Laporan | Laporan penjualan periode valid | GET `/api/laporan/penjualan?dari=2026-01-01&sampai=2026-01-31` | Status 200, data penjualan periode tampil | Belum diuji |
| BB-LAP-003 | Laporan | Laporan harian | GET `/api/laporan/harian?tanggal=2026-01-01` | Status 200, rekap harian tampil | Belum diuji |
| BB-LAP-004 | Laporan | Laporan mingguan | GET `/api/laporan/mingguan` | Status 200, rekap mingguan tampil | Belum diuji |
| BB-LAP-005 | Laporan | Laporan bulanan | GET `/api/laporan/bulanan?tahun=2026&bulan=1` | Status 200, rekap bulanan tampil | Belum diuji |
| BB-LAP-006 | Laporan | Laporan produk terlaris | GET `/api/laporan/produk-terlaris` | Status 200, produk terlaris tampil berurutan | Belum diuji |
| BB-LAP-007 | Laporan | Export PDF valid | GET `/api/laporan/export/pdf?dari=2026-01-01&sampai=2026-01-31&tipe=penjualan` | Status 200, file PDF dihasilkan | Belum diuji |
| BB-LAP-008 | Laporan | Export Excel valid | GET `/api/laporan/export/excel?dari=2026-01-01&sampai=2026-01-31` | Status 200, file Excel dihasilkan | Belum diuji |
| BB-WEB-001 | Web | Halaman login tampil | Buka `/login` tanpa session | Halaman login tampil | Belum diuji |
| BB-WEB-002 | Web | Session login valid diarahkan ke dashboard | Login via API, simpan token melalui `/auth/session` | User diarahkan ke `/dashboard` | Belum diuji |
| BB-WEB-003 | Web | Akses dashboard tanpa session | Buka `/dashboard` tanpa login | User diarahkan ke `/login` | Belum diuji |
| BB-WEB-004 | Web | Kasir membuka halaman kasir | Login kasir, buka `/kasir` | Halaman kasir tampil | Belum diuji |
| BB-WEB-005 | Web | Kasir tidak bisa membuka halaman laporan | Login kasir, buka `/laporan` | Akses ditolak atau diarahkan sesuai middleware | Belum diuji |
| BB-WEB-006 | Web | Pemilik membuka halaman laporan | Login pemilik, buka `/laporan` | Halaman laporan tampil | Belum diuji |
| BB-WEB-007 | Web | Logout web | Klik logout atau POST `/logout` | Session hilang dan diarahkan ke login | Belum diuji |

## 8. Decision Table Login

| Kondisi | TC-1 | TC-2 | TC-3 | TC-4 |
| --- | --- | --- | --- | --- |
| Username terdaftar | Ya | Ya | Ya | Tidak |
| Password benar | Ya | Tidak | Ya | - |
| Role sesuai | Ya | Ya | Tidak | - |
| Akun aktif | Ya | Ya | Ya | - |
| Hasil | Login berhasil | Ditolak | Ditolak | Ditolak |

## 9. Decision Table Penyelesaian Transaksi

| Kondisi | TC-1 | TC-2 | TC-3 | TC-4 |
| --- | --- | --- | --- | --- |
| Transaksi ada | Ya | Ya | Ya | Tidak |
| Status dapat diproses | Ya | Ya | Ya | - |
| Stok mencukupi | Ya | Ya | Tidak | - |
| Jumlah bayar mencukupi | Ya | Tidak | Ya | - |
| Hasil | Transaksi selesai | Ditolak karena bayar kurang | Ditolak karena stok tidak cukup | Ditolak karena transaksi tidak ditemukan |

## 10. Format Pencatatan Hasil

Gunakan format berikut saat pengujian dilakukan.

| ID Test Case | Tanggal | Penguji | Actual result | Status | Catatan |
| --- | --- | --- | --- | --- | --- |
| BB-AUTH-001 |  |  |  | Pass/Fail |  |

## 11. Kriteria Kelulusan

Pengujian dinyatakan lulus jika:

1. Semua test case prioritas utama pada modul autentikasi, hak akses, produk, stok, transaksi, dan laporan berstatus Pass.
2. Tidak ada fitur yang menerima input tidak valid sebagai data valid.
3. Tidak ada role `kasir` yang dapat mengakses fitur khusus `pemilik`.
4. Proses transaksi selesai selalu mengurangi stok sesuai jumlah item.
5. Export PDF dan Excel menghasilkan file dengan format yang dapat dibuka.

## 12. Prioritas Pengujian

| Prioritas | Modul |
| --- | --- |
| Tinggi | Autentikasi, hak akses, transaksi, stok, produk |
| Sedang | Laporan, pembelian, pemasok, pengguna |
| Rendah | Filter, pagination, tampilan web pendukung |

## 13. Saran Eksekusi

1. Jalankan aplikasi dengan `php artisan serve`.
2. Jalankan migrasi dan seeder agar data awal tersedia.
3. Login melalui endpoint `/api/auth/login` untuk mendapatkan Bearer Token.
4. Uji endpoint API menggunakan Postman Collection di `docs/POS-Toko-Waris.postman_collection.json`.
5. Catat hasil aktual pada tabel pencatatan hasil.
6. Untuk regresi otomatis, jalankan `php artisan test`.
