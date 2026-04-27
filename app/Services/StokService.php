<?php

namespace App\Services;

use App\Models\Stok;
use App\Models\StokMovement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class StokService
{
    /**
     * Kurangi stok saat terjadi penjualan.
     * Dipanggil dari TransaksiService.
     */
    public function kurangiStok(
        int $produkId,
        int $jumlah,
        string $referensi,
        ?int $userId = null
    ): void {
        $stok = Stok::where('produk_id', $produkId)->lockForUpdate()->firstOrFail();

        if ($stok->jumlah_stok < $jumlah) {
            throw new \RuntimeException(
                "Stok produk ID {$produkId} tidak mencukupi. Tersedia: {$stok->jumlah_stok}, diminta: {$jumlah}."
            );
        }

        $sebelum = $stok->jumlah_stok;
        $stok->jumlah_stok -= $jumlah;
        $stok->save();

        StokMovement::create([
            'produk_id'    => $produkId,
            'user_id'      => $userId,
            'tipe'         => 'keluar',
            'jumlah'       => $jumlah,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $stok->jumlah_stok,
            'referensi'    => $referensi,
            'keterangan'   => 'Stok keluar akibat penjualan',
        ]);

        if ($stok->isLowStock()) {
            Log::warning("LOW_STOCK: Produk ID {$produkId} stok {$stok->jumlah_stok} <= minimal {$stok->stok_minimal}");
        }
    }

    /**
     * Kembalikan stok saat transaksi dibatalkan.
     */
    public function kembalikanStok(
        int $produkId,
        int $jumlah,
        string $referensi,
        ?int $userId = null
    ): void {
        $stok = Stok::where('produk_id', $produkId)->lockForUpdate()->firstOrFail();

        $sebelum = $stok->jumlah_stok;
        $stok->jumlah_stok += $jumlah;
        $stok->save();

        StokMovement::create([
            'produk_id'    => $produkId,
            'user_id'      => $userId,
            'tipe'         => 'masuk',
            'jumlah'       => $jumlah,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $stok->jumlah_stok,
            'referensi'    => $referensi,
            'keterangan'   => 'Stok kembali akibat pembatalan transaksi',
        ]);
    }

    /**
     * Tambah stok saat pembelian dari pemasok diterima.
     */
    public function tambahStok(
        int $produkId,
        int $jumlah,
        string $referensi,
        ?int $userId = null,
        string $keterangan = 'Stok masuk dari pembelian pemasok'
    ): void {
        $stok = $this->getOrCreateLockedStok($produkId);
        $sebelum = $stok->jumlah_stok;
        $stok->jumlah_stok += $jumlah;
        $stok->save();

        StokMovement::create([
            'produk_id'    => $produkId,
            'user_id'      => $userId,
            'tipe'         => 'masuk',
            'jumlah'       => $jumlah,
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $stok->jumlah_stok,
            'referensi'    => $referensi,
            'keterangan'   => $keterangan,
        ]);
    }

    /**
     * Penyesuaian manual stok (stock opname).
     */
    public function penyesuaianStok(
        int $produkId,
        int $jumlahBaru,
        ?int $userId = null,
        string $keterangan = 'Penyesuaian stok (opname)'
    ): void {
        $stok = Stok::where('produk_id', $produkId)->lockForUpdate()->firstOrFail();
        $sebelum = $stok->jumlah_stok;
        $selisih = $jumlahBaru - $sebelum;

        $stok->jumlah_stok = $jumlahBaru;
        $stok->save();

        StokMovement::create([
            'produk_id'    => $produkId,
            'user_id'      => $userId,
            'tipe'         => 'penyesuaian',
            'jumlah'       => abs($selisih),
            'stok_sebelum' => $sebelum,
            'stok_sesudah' => $jumlahBaru,
            'referensi'    => null,
            'keterangan'   => $keterangan . " (selisih: {$selisih})",
        ]);
    }

    /**
     * Ambil daftar produk dengan stok minimum / hampir habis.
     */
    public function getNotifikasiStokMinimal(): \Illuminate\Database\Eloquent\Collection
    {
        return Stok::with('produk:produk_id,kode_produk,nama_produk,satuan')
            ->lowStock()
            ->get();
    }

    /**
     * Ambil daftar produk dengan tanggal kedaluwarsa mendekati / sudah lewat.
     */
    public function getNotifikasiKedaluwarsa(int $days = 30): array
    {
        $expiringSoon = Stok::with('produk:produk_id,kode_produk,nama_produk')
            ->expiringSoon($days)
            ->get();

        $expired = Stok::with('produk:produk_id,kode_produk,nama_produk')
            ->expired()
            ->get();

        return [
            'akan_kedaluwarsa' => $expiringSoon,
            'sudah_kedaluwarsa' => $expired,
        ];
    }

    /**
     * Mengambil data stok dengan penguncian transaksi agar update tetap konsisten.
     */
    private function getOrCreateLockedStok(int $produkId): Stok
    {
        $stok = Stok::where('produk_id', $produkId)->lockForUpdate()->first();

        if ($stok) {
            return $stok;
        }

        try {
            Stok::create([
                'produk_id' => $produkId,
                'jumlah_stok' => 0,
                'stok_minimal' => 5,
            ]);
        } catch (QueryException $e) {
            $errorInfo = $e->errorInfo ?? [];
            $sqlState = $errorInfo[0] ?? null;
            $driverCode = $errorInfo[1] ?? null;

            if (
                !in_array($sqlState, ['23000', '23505'], true)
                || !in_array($driverCode, [19, 1062], true)
            ) {
                throw $e;
            }
        }

        return Stok::where('produk_id', $produkId)->lockForUpdate()->firstOrFail();
    }
}
