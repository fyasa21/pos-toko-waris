<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    /**
     * Laporan penjualan berdasarkan rentang tanggal.
     */
    public function laporanPenjualan(string $dari, string $sampai): array
    {
        $dari    = Carbon::parse($dari)->startOfDay();
        $sampai  = Carbon::parse($sampai)->endOfDay();

        $transaksis = Transaksi::with(['user:user_id,nama_lengkap', 'details.produk:produk_id,nama_produk,kode_produk'])
            ->selesai()
            ->whereBetween('tanggal_transaksi', [$dari, $sampai])
            ->orderByDesc('tanggal_transaksi')
            ->get();

        $ringkasan = [
            'total_transaksi'      => $transaksis->count(),
            'total_pendapatan'     => $transaksis->sum('total_pembayaran'),
            'total_diskon'         => $transaksis->sum('total_diskon'),
            'total_pajak'          => $transaksis->sum('total_pajak'),
            'total_item_terjual'   => $transaksis->sum(fn($t) => $t->details->sum('jumlah')),
            'rata_rata_per_transaksi' => $transaksis->count() > 0
                ? round($transaksis->sum('total_pembayaran') / $transaksis->count(), 2) : 0,
        ];

        return [
            'ringkasan'   => $ringkasan,
            'transaksis'  => $transaksis,
            'periode'     => ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()],
        ];
    }

    /**
     * Laporan penjualan harian (agregat per hari).
     */
    public function laporanHarian(string $dari, string $sampai): array
    {
        $dari   = Carbon::parse($dari)->startOfDay();
        $sampai = Carbon::parse($sampai)->endOfDay();

        $harian = Transaksi::selesai()
            ->whereBetween('tanggal_transaksi', [$dari, $sampai])
            ->selectRaw('DATE(tanggal_transaksi) as tanggal,
                COUNT(*) as jumlah_transaksi,
                SUM(total_pembayaran) as total_pendapatan,
                SUM(total_diskon) as total_diskon,
                SUM(total_pajak) as total_pajak')
            ->groupBy(DB::raw('DATE(tanggal_transaksi)'))
            ->orderBy('tanggal')
            ->get();

        return [
            'harian'  => $harian,
            'periode' => ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()],
        ];
    }

    /**
     * Produk terlaris dalam periode tertentu.
     */
    public function produkTerlaris(string $dari, string $sampai, int $limit = 10): \Illuminate\Support\Collection
    {
        $dari   = Carbon::parse($dari)->startOfDay();
        $sampai = Carbon::parse($sampai)->endOfDay();

        return DetailTransaksi::join('transaksis', 'detail_transaksis.transaksi_id', '=', 'transaksis.transaksi_id')
            ->join('produks', 'detail_transaksis.produk_id', '=', 'produks.produk_id')
            ->where('transaksis.status', 'selesai')
            ->whereBetween('transaksis.tanggal_transaksi', [$dari, $sampai])
            ->select(
                'produks.produk_id',
                'produks.kode_produk',
                'produks.nama_produk',
                DB::raw('SUM(detail_transaksis.jumlah) as total_terjual'),
                DB::raw('SUM(detail_transaksis.subtotal) as total_pendapatan')
            )
            ->groupBy('produks.produk_id', 'produks.kode_produk', 'produks.nama_produk')
            ->orderByDesc('total_terjual')
            ->limit($limit)
            ->get();
    }

    /**
     * Laporan keuangan: pendapatan vs harga pokok penjualan (HPP).
     */
    public function laporanKeuangan(string $dari, string $sampai): array
    {
        $dari   = Carbon::parse($dari)->startOfDay();
        $sampai = Carbon::parse($sampai)->endOfDay();

        $rekapPenjualan = DB::table('detail_transaksis as dt')
            ->join('transaksis as t', 'dt.transaksi_id', '=', 't.transaksi_id')
            ->join('produks as p', 'dt.produk_id', '=', 'p.produk_id')
            ->where('t.status', 'selesai')
            ->whereBetween('t.tanggal_transaksi', [$dari, $sampai])
            ->selectRaw('
                SUM(dt.subtotal) as total_penjualan_bersih,
                SUM(dt.jumlah * p.harga_beli) as total_hpp,
                SUM(dt.jumlah * p.harga_jual) as total_penjualan_kotor,
                SUM(dt.diskon_item) as total_diskon_item
            ')
            ->first();

        $totalPajak   = Transaksi::selesai()->whereBetween('tanggal_transaksi', [$dari, $sampai])->sum('total_pajak');
        $totalDiskon  = Transaksi::selesai()->whereBetween('tanggal_transaksi', [$dari, $sampai])->sum('total_diskon');

        $labaBruto = ($rekapPenjualan->total_penjualan_bersih ?? 0)
                   - ($rekapPenjualan->total_hpp ?? 0);

        return [
            'periode'                    => ['dari' => $dari->toDateString(), 'sampai' => $sampai->toDateString()],
            'total_penjualan_kotor'      => (float) ($rekapPenjualan->total_penjualan_kotor ?? 0),
            'total_diskon'               => (float) $totalDiskon,
            'total_penjualan_bersih'     => (float) ($rekapPenjualan->total_penjualan_bersih ?? 0),
            'total_pajak'                => (float) $totalPajak,
            'total_hpp'                  => (float) ($rekapPenjualan->total_hpp ?? 0),
            'laba_bruto'                 => (float) $labaBruto,
            'margin_persen'              => ($rekapPenjualan->total_penjualan_bersih ?? 0) > 0
                ? round(($labaBruto / $rekapPenjualan->total_penjualan_bersih) * 100, 2) : 0,
        ];
    }

    /**
     * Ringkasan dashboard (hari ini).
     */
    public function ringkasanDashboard(): array
    {
        $hari  = now()->startOfDay();
        $akhir = now()->endOfDay();

        $transaksiHariIni = Transaksi::selesai()
            ->whereBetween('tanggal_transaksi', [$hari, $akhir]);

        $transaksiPending = Transaksi::pending()->count();

        return [
            'hari_ini' => [
                'jumlah_transaksi' => (clone $transaksiHariIni)->count(),
                'total_pendapatan' => (clone $transaksiHariIni)->sum('total_pembayaran'),
                'total_item'       => DetailTransaksi::join('transaksis', 'detail_transaksis.transaksi_id', '=', 'transaksis.transaksi_id')
                    ->where('transaksis.status', 'selesai')
                    ->whereBetween('transaksis.tanggal_transaksi', [$hari, $akhir])
                    ->sum('detail_transaksis.jumlah'),
            ],
            'bulan_ini' => [
                'jumlah_transaksi' => Transaksi::selesai()
                    ->whereBetween('tanggal_transaksi', [now()->startOfMonth(), $akhir])
                    ->count(),
                'total_pendapatan' => Transaksi::selesai()
                    ->whereBetween('tanggal_transaksi', [now()->startOfMonth(), $akhir])
                    ->sum('total_pembayaran'),
            ],
            'transaksi_pending' => $transaksiPending,
            'produk_aktif'      => \App\Models\Produk::active()->count(),
            'stok_minimal'      => \App\Models\Stok::lowStock()->count(),
        ];
    }
}
