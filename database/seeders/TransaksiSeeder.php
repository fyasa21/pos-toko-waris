<?php

namespace Database\Seeders;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\StokMovement;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransaksiSeeder extends Seeder
{
    /**
     * Mengisi data transaksi contoh untuk kebutuhan demo dan pengujian.
     */
    public function run(): void
    {
        $kasirIds = User::where('role', 'kasir')->pluck('user_id')->toArray();
        $produks = Produk::with('stok')->active()->get();
        $taxRate = (float) config('pos.tax_rate', 0);

        $counter = 1;

        // Buat 30 hari transaksi historis.
        for ($day = 29; $day >= 0; $day--) {
            $tanggal = now()->subDays($day);
            $jumlahTrx = rand(5, 15);

            for ($t = 0; $t < $jumlahTrx; $t++) {
                $userId = $kasirIds[array_rand($kasirIds)];
                $jam = $tanggal->copy()->setTime(rand(8, 20), rand(0, 59), 0);
                $status = rand(1, 10) <= 9 ? 'selesai' : 'batal';

                $itemCount = rand(1, 5);
                $pilihProduk = $produks->random(min($itemCount, $produks->count()));

                $totalHarga = 0;
                $totalDiskon = 0;
                $items = [];

                foreach ($pilihProduk as $produk) {
                    $jumlah = rand(1, 5);
                    $hargaSatuan = (float) $produk->harga_jual;
                    $diskonPersen = (float) $produk->diskon_persen;
                    $diskonItem = round(($diskonPersen / 100) * $hargaSatuan * $jumlah, 2);
                    $subtotal = round(($hargaSatuan * $jumlah) - $diskonItem, 2);

                    $totalHarga += $hargaSatuan * $jumlah;
                    $totalDiskon += $diskonItem;
                    $items[] = compact('produk', 'jumlah', 'hargaSatuan', 'diskonItem', 'subtotal');
                }

                $subtotalBersih = $totalHarga - $totalDiskon;
                $totalPajak = round($subtotalBersih * ($taxRate / 100), 2);
                $totalBayar = round($subtotalBersih + $totalPajak, 2);

                $metode = rand(0, 1) ? 'cash' : 'cashless';
                $bayar = $metode === 'cash'
                    ? $totalBayar + (rand(0, 3) * 1000)
                    : $totalBayar;

                $nomorTrx = 'TRX-' . $jam->format('Ymd') . '-' . str_pad($counter++, 5, '0', STR_PAD_LEFT);

                $transaksi = Transaksi::create([
                    'user_id' => $userId,
                    'nomor_transaksi' => $nomorTrx,
                    'tanggal_transaksi' => $jam,
                    'total_harga' => $totalHarga,
                    'total_diskon' => $totalDiskon,
                    'total_pajak' => $totalPajak,
                    'total_pembayaran' => $totalBayar,
                    'jumlah_bayar' => $status === 'selesai' ? $bayar : 0,
                    'kembalian' => $status === 'selesai' ? max(0, $bayar - $totalBayar) : 0,
                    'metode_pembayaran' => $metode,
                    'status' => $status,
                    'device_id' => 'KASIR-PC-01',
                    'is_synced' => true,
                ]);

                foreach ($items as $item) {
                    DetailTransaksi::create([
                        'transaksi_id' => $transaksi->transaksi_id,
                        'produk_id' => $item['produk']->produk_id,
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $item['hargaSatuan'],
                        'diskon_item' => $item['diskonItem'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    if ($status === 'selesai') {
                        $stok = Stok::where('produk_id', $item['produk']->produk_id)->first();
                        if ($stok) {
                            $sebelum = $stok->jumlah_stok;

                            StokMovement::create([
                                'produk_id' => $item['produk']->produk_id,
                                'user_id' => $userId,
                                'tipe' => 'keluar',
                                'jumlah' => $item['jumlah'],
                                'stok_sebelum' => $sebelum + $item['jumlah'],
                                'stok_sesudah' => $sebelum,
                                'referensi' => $nomorTrx,
                                'keterangan' => 'Stok keluar akibat penjualan (seeded)',
                                'created_at' => $jam,
                            ]);
                        }
                    }
                }
            }
        }

        $totalSeeded = Transaksi::count();
        $this->command->info("Transaksi seeded: {$totalSeeded} transaksi historis.");
    }
}
