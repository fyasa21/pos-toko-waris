<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\PersonalAccessToken;

// ============================================================
// PUBLIC: halaman dan endpoint yang tidak membutuhkan login
// ============================================================

// Menampilkan halaman login jika pengguna belum memiliki session aktif.
Route::get('/login', function () {
    if (session()->has('pos_token')) {
        return redirect()->route('dashboard');
    }

    return view('pages.login');
})->name('login');

// Menyimpan token API yang sudah tervalidasi ke dalam session web Laravel.
Route::post('/auth/session', function (Request $request) {
    $validated = $request->validate([
        'token' => 'required|string',
    ]);

    $accessToken = PersonalAccessToken::findToken($validated['token']);
    $user = $accessToken?->tokenable;

    if (!$accessToken || !$user || !data_get($user, 'is_active', false)) {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('error', 'Sesi login tidak valid. Silakan login kembali.');
    }

    $request->session()->regenerate();

    session([
        'pos_token' => $validated['token'],
        'user' => [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'role' => $user->role,
            'is_active' => $user->is_active,
        ],
    ]);

    return redirect()->route('dashboard');
})->name('auth.session');

// Menghapus token aktif dari session web lalu mengakhiri sesi pengguna.
Route::post('/logout', function () {
    $token = session('pos_token');

    if ($token && class_exists(PersonalAccessToken::class)) {
        $accessToken = PersonalAccessToken::findToken($token);

        if ($accessToken) {
            $accessToken->delete();
        }
    }

    session()->flush();

    return redirect()->route('login');
})->name('logout');

// ============================================================
// PROTECTED: halaman yang hanya dapat diakses setelah login
// ============================================================

// Kelompok route halaman web yang membutuhkan session login aktif.
Route::middleware('pos.auth')->group(function () {
    // Mengarahkan root aplikasi ke dashboard setelah autentikasi berhasil.
    Route::get('/', fn() => redirect()->route('dashboard'));

    // Menampilkan dashboard utama PoS.
    Route::get('/dashboard', fn() => view('pages.dashboard'))->name('dashboard');

    // Menampilkan halaman kasir untuk proses transaksi penjualan.
    Route::get('/kasir', fn() => view('pages.kasir'))->name('kasir');

    // Menampilkan halaman master data produk.
    Route::get('/produk', fn() => view('pages.produk'))->name('produk');

    // Menampilkan halaman daftar transaksi.
    Route::get('/transaksi', fn() => view('pages.transaksi'))->name('transaksi');

    // Menampilkan halaman cetak struk berdasarkan ID transaksi.
    Route::get('/kasir/struk/{id}', function ($id) {
        return view('pages.struk', ['id' => $id]);
    })->name('struk');

    // Kelompok route pemilik untuk halaman laporan dan proses ekspor file.
    Route::middleware('pos.pemilik')->group(function () {
        // Menampilkan halaman laporan yang hanya boleh dibuka oleh pemilik.
        Route::get('/laporan', fn() => view('pages.laporan'))->name('laporan');

        // Proxy export PDF agar browser bisa memakai token dari session saat mengunduh.
        Route::get('/laporan/export/pdf', function (Request $request) {
            $token = session('pos_token');
            $dari = $request->query('dari');
            $sampai = $request->query('sampai');
            $tipe = $request->query('tipe', 'penjualan');

            if (!$dari || !$sampai) {
                abort(400, 'Parameter dari dan sampai wajib diisi.');
            }

            $response = Http::withToken($token)->get(config('app.url') . '/api/laporan/export/pdf', [
                'dari' => $dari,
                'sampai' => $sampai,
                'tipe' => $tipe,
            ]);

            if ($response->failed()) {
                abort($response->status(), 'Gagal mengambil data dari API.');
            }

            $filename = "laporan_{$tipe}_{$dari}_sd_{$sampai}.pdf";

            return response($response->body(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        })->name('laporan.export.pdf');

        // Proxy export Excel agar file laporan tetap bisa diunduh dari halaman web.
        Route::get('/laporan/export/excel', function (Request $request) {
            $token = session('pos_token');
            $dari = $request->query('dari');
            $sampai = $request->query('sampai');

            if (!$dari || !$sampai) {
                abort(400, 'Parameter dari dan sampai wajib diisi.');
            }

            $response = Http::withToken($token)->get(config('app.url') . '/api/laporan/export/excel', [
                'dari' => $dari,
                'sampai' => $sampai,
            ]);

            if ($response->failed()) {
                abort($response->status(), 'Gagal mengambil data dari API.');
            }

            $filename = "laporan_penjualan_{$dari}_sd_{$sampai}.xlsx";

            return response($response->body(), 200)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        })->name('laporan.export.excel');
    });
});
