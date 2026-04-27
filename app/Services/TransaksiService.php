<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransaksiService
{
    /**
     * Menginisialisasi service stok yang diperlukan saat transaksi diproses.
     */
    public function __construct(private StokService $stokService) {}

    /**
     * Buat transaksi baru (status: pending).
     * Menghitung diskon & pajak otomatis per item.
     */
    public function buatTransaksi(array $data, int $userId): Transaksi
    {
        return DB::transaction(function () use ($data, $userId) {
            $taxRate    = (float) config('pos.tax_rate', 0);
            $totalHarga = 0;
            $totalDiskon = 0;
            $items      = [];

            foreach ($data['items'] as $item) {
                /** @var Produk $produk */
                $produk = Produk::with('stok')
                    ->where('produk_id', $item['produk_id'])
                    ->where('is_active', true)
                    ->firstOrFail();

                $jumlah      = (int) $item['jumlah'];
                $hargaSatuan = (float) $produk->harga_jual;

                // Diskon: ambil dari request atau dari produk (persen)
                $diskonPersen = isset($item['diskon_persen'])
                    ? (float) $item['diskon_persen']
                    : (float) $produk->diskon_persen;

                $diskonItem = round(($diskonPersen / 100) * $hargaSatuan * $jumlah, 2);
                $subtotal   = round(($hargaSatuan * $jumlah) - $diskonItem, 2);

                $totalHarga  += $hargaSatuan * $jumlah;
                $totalDiskon += $diskonItem;

                $items[] = [
                    'produk_id'   => $produk->produk_id,
                    'jumlah'      => $jumlah,
                    'harga_satuan'=> $hargaSatuan,
                    'diskon_item' => $diskonItem,
                    'subtotal'    => $subtotal,
                ];
            }

            $subtotalSetelahDiskon = $totalHarga - $totalDiskon;
            $totalPajak   = round($subtotalSetelahDiskon * ($taxRate / 100), 2);
            $totalBayar   = round($subtotalSetelahDiskon + $totalPajak, 2);

            $transaksi = $this->createPendingTransaksi([
                'user_id'           => $userId,
                'total_harga'       => $totalHarga,
                'total_diskon'      => $totalDiskon,
                'total_pajak'       => $totalPajak,
                'total_pembayaran'  => $totalBayar,
                'jumlah_bayar'      => 0,
                'kembalian'         => 0,
                'metode_pembayaran' => $data['metode_pembayaran'] ?? 'cash',
                'status'            => 'pending',
                'catatan'           => $data['catatan'] ?? null,
                'device_id'         => $data['device_id'] ?? null,
                'is_synced'         => true,
            ]);

            foreach ($items as $item) {
                DetailTransaksi::create(array_merge($item, [
                    'transaksi_id' => $transaksi->transaksi_id,
                ]));
            }

            return $transaksi->load('details.produk');
        });
    }

    /**
     * Selesaikan transaksi: kurangi stok, catat pembayaran.
     */
    public function selesaikanTransaksi(Transaksi $transaksi, array $data): Transaksi
    {
        if ($transaksi->status !== 'pending') {
            throw new \RuntimeException("Transaksi hanya bisa diselesaikan jika statusnya 'pending'.");
        }

        return DB::transaction(function () use ($transaksi, $data) {
            $jumlahBayarSebelumnya = (float) $transaksi->jumlah_bayar;
            $jumlahBayar = (float) $data['jumlah_bayar'];
            $metode      = $data['metode_pembayaran'] ?? $transaksi->metode_pembayaran;

            if ($metode !== 'cash' && $jumlahBayar <= 0) {
                $jumlahBayar = max(0, (float) $transaksi->total_pembayaran - $jumlahBayarSebelumnya);
            }

            $totalPembayaranDiterima = round($jumlahBayarSebelumnya + $jumlahBayar, 2);

            if ($totalPembayaranDiterima < $transaksi->total_pembayaran) {
                throw new \RuntimeException('Jumlah bayar tidak mencukupi total pembayaran.');
            }

            $kembalian = $metode === 'cash'
                ? max(0, $totalPembayaranDiterima - $transaksi->total_pembayaran)
                : 0;

            if ($jumlahBayarSebelumnya <= 0) {
                foreach ($transaksi->details as $detail) {
                    $this->stokService->kurangiStok(
                        produkId: $detail->produk_id,
                        jumlah: $detail->jumlah,
                        referensi: $transaksi->nomor_transaksi,
                        userId: $transaksi->user_id
                    );
                }
            }

            $transaksi->update([
                'status'            => 'selesai',
                'jumlah_bayar'      => $totalPembayaranDiterima,
                'kembalian'         => $kembalian,
                'metode_pembayaran' => $metode,
                'tanggal_transaksi' => now(),
            ]);

            ActivityLog::record(
                $transaksi->user_id,
                'selesaikan_transaksi',
                'transaksi',
                "Transaksi {$transaksi->nomor_transaksi} selesai. Total: Rp " . number_format($transaksi->total_pembayaran),
                ['nomor_transaksi' => $transaksi->nomor_transaksi]
            );

            return $transaksi->fresh('details.produk', 'user');
        });
    }

    /**
     * Batalkan seluruh transaksi (sebelum pembayaran).
     */
    public function batalkanTransaksi(Transaksi $transaksi, ?string $alasan = null): Transaksi
    {
        if ($transaksi->status === 'batal') {
            throw new \RuntimeException('Transaksi sudah dibatalkan sebelumnya.');
        }

        if ($transaksi->status === 'selesai') {
            throw new \RuntimeException('Transaksi yang sudah selesai tidak bisa dibatalkan langsung. Gunakan fitur retur.');
        }

        return DB::transaction(function () use ($transaksi, $alasan) {
            $transaksi->update([
                'status'  => 'batal',
                'catatan' => $alasan ? "Dibatalkan: {$alasan}" : 'Transaksi dibatalkan',
            ]);

            ActivityLog::record(
                $transaksi->user_id,
                'batal_transaksi',
                'transaksi',
                "Transaksi {$transaksi->nomor_transaksi} dibatalkan.",
                ['alasan' => $alasan]
            );

            return $transaksi->fresh();
        });
    }

    /**
     * Tambah item ke transaksi yang masih bisa diedit dari halaman detail.
     */
    public function tambahItemTransaksi(Transaksi $transaksi, int $produkId, int $jumlah): Transaksi
    {
        $this->assertTransaksiBisaDiedit($transaksi);

        return DB::transaction(function () use ($transaksi, $produkId, $jumlah) {
            /** @var Produk $produk */
            $produk = Produk::with('stok')
                ->where('produk_id', $produkId)
                ->where('is_active', true)
                ->first();

            if (!$produk) {
                throw new \RuntimeException('Produk tidak ditemukan atau sudah tidak aktif.');
            }

            if ($transaksi->status === 'selesai') {
                $this->stokService->kurangiStok(
                    produkId: $produk->produk_id,
                    jumlah: $jumlah,
                    referensi: $transaksi->nomor_transaksi,
                    userId: $transaksi->user_id
                );
            }

            $detail = DetailTransaksi::where('transaksi_id', $transaksi->transaksi_id)
                ->where('produk_id', $produk->produk_id)
                ->first();

            if ($detail) {
                $jumlahBaru = (int) $detail->jumlah + $jumlah;
                $diskonPerUnit = $detail->jumlah > 0
                    ? ((float) $detail->diskon_item / (int) $detail->jumlah)
                    : round((((float) $produk->diskon_persen) / 100) * (float) $detail->harga_satuan, 2);

                $detail->update([
                    'jumlah' => $jumlahBaru,
                    'diskon_item' => round($diskonPerUnit * $jumlahBaru, 2),
                    'subtotal' => round(((float) $detail->harga_satuan * $jumlahBaru) - ($diskonPerUnit * $jumlahBaru), 2),
                ]);
            } else {
                $hargaSatuan = (float) $produk->harga_jual;
                $diskonPerUnit = round((((float) $produk->diskon_persen) / 100) * $hargaSatuan, 2);

                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->transaksi_id,
                    'produk_id' => $produk->produk_id,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $hargaSatuan,
                    'diskon_item' => round($diskonPerUnit * $jumlah, 2),
                    'subtotal' => round(($hargaSatuan * $jumlah) - ($diskonPerUnit * $jumlah), 2),
                ]);
            }

            $this->recalculateTotals($transaksi);

            ActivityLog::record(
                $transaksi->user_id,
                'edit_transaksi',
                'transaksi',
                "Item transaksi {$transaksi->nomor_transaksi} ditambahkan dari detail transaksi.",
                ['produk_id' => $produkId, 'jumlah' => $jumlah]
            );

            return $transaksi->fresh('details.produk', 'user');
        });
    }

    /**
     * Hapus item tertentu dari transaksi yang masih bisa diedit.
     */
    public function hapusItemTransaksi(Transaksi $transaksi, int $detailId): Transaksi
    {
        $this->assertTransaksiBisaDiedit($transaksi);

        return DB::transaction(function () use ($transaksi, $detailId) {
            $detail = DetailTransaksi::where('detail_id', $detailId)
                ->where('transaksi_id', $transaksi->transaksi_id)
                ->first();

            if (!$detail) {
                throw new \RuntimeException('Item transaksi tidak ditemukan.');
            }

            if ($transaksi->status === 'selesai') {
                $this->stokService->kembalikanStok(
                    produkId: $detail->produk_id,
                    jumlah: $detail->jumlah,
                    referensi: $transaksi->nomor_transaksi,
                    userId: $transaksi->user_id
                );
            }

            $detail->delete();

            $this->recalculateTotals($transaksi);

            ActivityLog::record(
                $transaksi->user_id,
                'edit_transaksi',
                'transaksi',
                "Item transaksi {$transaksi->nomor_transaksi} dihapus dari detail transaksi.",
                ['detail_id' => $detailId]
            );

            return $transaksi->fresh('details.produk', 'user');
        });
    }

    /**
     * Ubah jumlah item tertentu pada transaksi yang masih bisa diedit.
     */
    public function updateJumlahItemTransaksi(Transaksi $transaksi, int $detailId, int $jumlahBaru): Transaksi
    {
        $this->assertTransaksiBisaDiedit($transaksi);

        return DB::transaction(function () use ($transaksi, $detailId, $jumlahBaru) {
            $detail = DetailTransaksi::where('detail_id', $detailId)
                ->where('transaksi_id', $transaksi->transaksi_id)
                ->first();

            if (!$detail) {
                throw new \RuntimeException('Item transaksi tidak ditemukan.');
            }

            $diskonPerUnit = $detail->jumlah > 0
                ? ((float) $detail->diskon_item / (int) $detail->jumlah)
                : 0;

            if ($transaksi->status === 'selesai') {
                $selisihJumlah = $jumlahBaru - (int) $detail->jumlah;

                if ($selisihJumlah > 0) {
                    $this->stokService->kurangiStok(
                        produkId: $detail->produk_id,
                        jumlah: $selisihJumlah,
                        referensi: $transaksi->nomor_transaksi,
                        userId: $transaksi->user_id
                    );
                } elseif ($selisihJumlah < 0) {
                    $this->stokService->kembalikanStok(
                        produkId: $detail->produk_id,
                        jumlah: abs($selisihJumlah),
                        referensi: $transaksi->nomor_transaksi,
                        userId: $transaksi->user_id
                    );
                }
            }

            $detail->update([
                'jumlah'      => $jumlahBaru,
                'diskon_item' => round($diskonPerUnit * $jumlahBaru, 2),
                'subtotal'    => round(((float) $detail->harga_satuan * $jumlahBaru) - ($diskonPerUnit * $jumlahBaru), 2),
            ]);

            $this->recalculateTotals($transaksi);

            ActivityLog::record(
                $transaksi->user_id,
                'edit_transaksi',
                'transaksi',
                "Jumlah item transaksi {$transaksi->nomor_transaksi} diperbarui dari detail transaksi.",
                ['detail_id' => $detailId, 'jumlah_baru' => $jumlahBaru]
            );

            return $transaksi->fresh('details.produk', 'user');
        });
    }

    /**
     * Recalculate totals setelah item dihapus.
     */
    private function recalculateTotals(Transaksi $transaksi): void
    {
        $taxRate     = (float) config('pos.tax_rate', 0);
        $details     = $transaksi->details()->get();
        $totalHarga  = $details->sum(fn($d) => $d->harga_satuan * $d->jumlah);
        $totalDiskon = $details->sum('diskon_item');
        $subtotal    = $totalHarga - $totalDiskon;
        $totalPajak  = round($subtotal * ($taxRate / 100), 2);

        $totalPembayaran = round($subtotal + $totalPajak, 2);
        $updates = [
            'total_harga'      => $totalHarga,
            'total_diskon'     => $totalDiskon,
            'total_pajak'      => $totalPajak,
            'total_pembayaran' => $totalPembayaran,
        ];

        if (
            $transaksi->status === 'selesai'
            || ($transaksi->status === 'pending' && (float) $transaksi->jumlah_bayar > 0)
        ) {
            if ((float) $transaksi->jumlah_bayar >= $totalPembayaran) {
                $updates['status'] = 'selesai';
                $updates['kembalian'] = $transaksi->metode_pembayaran === 'cash'
                    ? round(max(0, (float) $transaksi->jumlah_bayar - $totalPembayaran), 2)
                    : 0;
            } else {
                $updates['status'] = 'pending';
                $updates['kembalian'] = 0;
            }
        }

        $transaksi->update($updates);
        $transaksi->refresh();
    }

    /**
     * Memastikan transaksi masih boleh dimodifikasi dari halaman detail.
     */
    private function assertTransaksiBisaDiedit(Transaksi $transaksi): void
    {
        if (!in_array($transaksi->status, ['pending', 'selesai'], true)) {
            throw new \RuntimeException('Item hanya bisa diubah pada transaksi berstatus pending atau selesai.');
        }
    }

    /**
     * Generate nomor transaksi unik: TRX-20250101-00001
     */
    private function createPendingTransaksi(array $attributes): Transaksi
    {
        $lockKey = 'transaksi-number:' . now()->format('Ymd');

        try {
            return Cache::lock($lockKey, 5)->block(3, function () use ($attributes) {
                return $this->createWithGeneratedNomor($attributes);
            });
        } catch (LockTimeoutException $e) {
            throw new \RuntimeException('Sistem sedang sibuk membuat nomor transaksi. Silakan coba beberapa saat lagi.', 0, $e);
        }
    }

    /**
     * Menyimpan transaksi baru menggunakan nomor transaksi yang baru dibuat.
     */
    private function createWithGeneratedNomor(array $attributes): Transaksi
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return Transaksi::create($attributes + [
                    'nomor_transaksi' => $this->generateNomorTransaksi(),
                ]);
            } catch (QueryException $e) {
                if (!$this->isDuplicateNomorTransaksiException($e) || $attempt === 4) {
                    throw $e;
                }
            }
        }

        throw new \RuntimeException('Gagal membuat nomor transaksi yang unik.');
    }

    /**
     * Memeriksa apakah exception yang muncul disebabkan duplikasi nomor transaksi.
     */
    private function isDuplicateNomorTransaksiException(QueryException $e): bool
    {
        $errorInfo = $e->errorInfo ?? [];
        $sqlState = $errorInfo[0] ?? null;
        $driverCode = $errorInfo[1] ?? null;
        $message = strtolower($e->getMessage());

        return in_array($sqlState, ['23000', '23505'], true)
            && ($driverCode === 1062 || str_contains($message, 'nomor_transaksi') || str_contains($message, 'unique'));
    }

    /**
     * Membuat nomor transaksi unik berdasarkan tanggal dan urutan harian.
     */
    private function generateNomorTransaksi(): string
    {
        $prefix  = 'TRX-' . now()->format('Ymd') . '-';
        $last    = Transaksi::where('nomor_transaksi', 'like', $prefix . '%')
                       ->orderByDesc('nomor_transaksi')
                       ->value('nomor_transaksi');
        $counter = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad($counter, 5, '0', STR_PAD_LEFT);
    }
}
