<?php

namespace App\Services;

use App\Models\DetailPembelianPemasok;
use App\Models\PembelianPemasok;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class PembelianService
{
    /**
     * Menginisialisasi service stok yang dibutuhkan saat proses pembelian.
     */
    public function __construct(private StokService $stokService) {}

    /**
     * Buat purchase order baru (status: pending).
     */
    public function buatPembelian(array $data, int $userId): PembelianPemasok
    {
        return DB::transaction(function () use ($data, $userId) {
            $totalHarga = 0;

            $pembelian = PembelianPemasok::create([
                'pemasok_id'       => $data['pemasok_id'],
                'user_id'          => $userId,
                'nomor_pembelian'  => $this->generateNomor(),
                'tanggal_pembelian'=> now(),
                'total_harga'      => 0,
                'status'           => 'pending',
                'catatan'          => $data['catatan'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $jumlah    = (int) $item['jumlah'];
                $hargaBeli = (float) $item['harga_beli'];
                $subtotal  = round($jumlah * $hargaBeli, 2);
                $totalHarga += $subtotal;

                DetailPembelianPemasok::create([
                    'pembelian_id' => $pembelian->pembelian_id,
                    'produk_id'    => $item['produk_id'],
                    'jumlah'       => $jumlah,
                    'harga_beli'   => $hargaBeli,
                    'subtotal'     => $subtotal,
                ]);
            }

            $pembelian->update(['total_harga' => $totalHarga]);

            return $pembelian->load('details.produk', 'pemasok');
        });
    }

    /**
     * Terima pembelian: update status selesai & tambah stok otomatis.
     */
    public function terimaPembelian(PembelianPemasok $pembelian): PembelianPemasok
    {
        if ($pembelian->status !== 'pending') {
            throw new \RuntimeException("Hanya pembelian berstatus 'pending' yang bisa diterima.");
        }

        return DB::transaction(function () use ($pembelian) {
            $pembelian->update(['status' => 'selesai']);

            foreach ($pembelian->details as $detail) {
                $this->stokService->tambahStok(
                    produkId: $detail->produk_id,
                    jumlah: $detail->jumlah,
                    referensi: $pembelian->nomor_pembelian,
                    userId: $pembelian->user_id,
                    keterangan: "Stok masuk dari PO {$pembelian->nomor_pembelian}"
                );
            }

            ActivityLog::record(
                $pembelian->user_id,
                'terima_pembelian',
                'pembelian',
                "Pembelian {$pembelian->nomor_pembelian} diterima, stok diperbarui."
            );

            return $pembelian->fresh('details.produk', 'pemasok');
        });
    }

    /**
     * Batalkan pembelian.
     */
    public function batalkanPembelian(PembelianPemasok $pembelian): PembelianPemasok
    {
        if ($pembelian->status === 'selesai') {
            throw new \RuntimeException('Pembelian yang sudah selesai tidak bisa dibatalkan.');
        }

        $pembelian->update(['status' => 'batal']);

        return $pembelian->fresh();
    }

    /**
     * Membuat nomor purchase order yang unik untuk transaksi pembelian.
     */
    private function generateNomor(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last   = PembelianPemasok::where('nomor_pembelian', 'like', $prefix . '%')
                      ->orderByDesc('nomor_pembelian')
                      ->value('nomor_pembelian');
        $counter = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }
}
