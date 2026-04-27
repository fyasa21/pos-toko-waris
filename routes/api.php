<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\PemasokController;
use App\Http\Controllers\Api\PembelianController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\StokController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - POS Toko Waris
|--------------------------------------------------------------------------
|
| Prefix  : /api
| Auth    : Laravel Sanctum (Bearer Token)
| Role    : kasir | pemilik
|
*/

// Endpoint login untuk mendapatkan token akses API.
Route::post('/auth/login', [AuthController::class, 'login']);

// Kelompok route yang hanya bisa diakses setelah bearer token tervalidasi.
Route::middleware(['auth:sanctum', 'log.api'])->group(function () {
    // Route pengelolaan profil dan sesi pengguna yang sedang login.
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/change-password', [AuthController::class, 'changePassword']);
    });

    // Route baca produk yang dapat diakses kasir maupun pemilik.
    Route::get('/produk/barcode/{barcode}', [ProdukController::class, 'findByBarcode']);
    Route::get('/produk', [ProdukController::class, 'index']);
    Route::get('/produk/{id}', [ProdukController::class, 'show']);

    // Route baca kategori untuk kebutuhan filter dan master produk.
    Route::get('/kategori', [KategoriController::class, 'index']);

    // Route baca stok dan notifikasi yang dibutuhkan seluruh role.
    Route::get('/stok', [StokController::class, 'index']);
    Route::get('/stok/notifikasi', [StokController::class, 'notifikasi']);
    Route::get('/stok/{produkId}/riwayat', [StokController::class, 'riwayat']);

    // Route transaksi yang dapat dipakai oleh kasir maupun pemilik toko.
    Route::middleware('role:kasir,pemilik')->prefix('transaksi')->group(function () {
        Route::get('/', [TransaksiController::class, 'index']);
        Route::post('/', [TransaksiController::class, 'store']);
        Route::get('/{id}', [TransaksiController::class, 'show']);
        Route::post('/{id}/item', [TransaksiController::class, 'tambahItem']);
        Route::patch('/{id}/item/{detailId}', [TransaksiController::class, 'updateItem']);
        Route::post('/{id}/selesaikan', [TransaksiController::class, 'selesaikan']);
        Route::post('/{id}/batalkan', [TransaksiController::class, 'batalkan']);
        Route::delete('/{id}/item/{detailId}', [TransaksiController::class, 'hapusItem']);
        Route::get('/{id}/struk', [TransaksiController::class, 'struk']);
    });

    // Route khusus pemilik untuk konfigurasi master data dan pelaporan toko.
    Route::middleware('role:pemilik')->group(function () {
        // CRUD produk hanya boleh dilakukan oleh pemilik.
        Route::post('/produk', [ProdukController::class, 'store']);
        Route::put('/produk/{id}', [ProdukController::class, 'update']);
        Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);

        // CRUD kategori hanya boleh dilakukan oleh pemilik.
        Route::post('/kategori', [KategoriController::class, 'store']);
        Route::put('/kategori/{id}', [KategoriController::class, 'update']);
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy']);

        // Manajemen stok manual dan hasil opname.
        Route::put('/stok/{produkId}', [StokController::class, 'update']);
        Route::post('/stok/{produkId}/tambah', [StokController::class, 'tambah']);
        Route::post('/stok/{produkId}/penyesuaian', [StokController::class, 'penyesuaian']);

        // Route pemasok dan riwayat pembeliannya.
        Route::apiResource('pemasok', PemasokController::class)->parameters(['pemasok' => 'id']);
        Route::get('/pemasok/{id}/riwayat-pembelian', [PemasokController::class, 'riwayatPembelian']);

        // Route purchase order ke pemasok.
        Route::get('/pembelian', [PembelianController::class, 'index']);
        Route::post('/pembelian', [PembelianController::class, 'store']);
        Route::get('/pembelian/{id}', [PembelianController::class, 'show']);
        Route::post('/pembelian/{id}/terima', [PembelianController::class, 'terima']);
        Route::post('/pembelian/{id}/batalkan', [PembelianController::class, 'batalkan']);

        // Route manajemen akun pengguna sistem.
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);

        // Kelompok route laporan untuk dashboard, rekap, dan ekspor data.
        Route::prefix('laporan')->group(function () {
            Route::get('/dashboard', [LaporanController::class, 'dashboard']);
            Route::get('/penjualan', [LaporanController::class, 'penjualan']);
            Route::get('/harian', [LaporanController::class, 'harian']);
            Route::get('/mingguan', [LaporanController::class, 'mingguan']);
            Route::get('/bulanan', [LaporanController::class, 'bulanan']);
            Route::get('/keuangan', [LaporanController::class, 'keuangan']);
            Route::get('/produk-terlaris', [LaporanController::class, 'produkTerlaris']);
            Route::get('/export/pdf', [LaporanController::class, 'exportPdf']);
            Route::get('/export/excel', [LaporanController::class, 'exportExcel']);
        });
    });
});

// Endpoint sederhana untuk mengecek apakah API berjalan dengan baik.
Route::get('/ping', fn() => response()->json(['status' => 'ok', 'timestamp' => now()]));
