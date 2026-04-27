<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\Seeder;

class ProdukSeeder extends Seeder
{
    /**
     * Mengisi data produk beserta stok awal agar sistem siap dipakai.
     */
    public function run(): void
    {
        $kategoris = Kategori::pluck('kategori_id', 'nama_kategori');

        $produkData = [
            // Makanan & Snack
            ['kode' => 'MKN-001', 'nama' => 'Indomie Goreng',         'kat' => 'Makanan & Snack',   'beli' => 2800,  'jual' => 3500,  'diskon' => 0,  'barcode' => '8996001234001', 'stok' => 150, 'min' => 20, 'exp' => now()->addMonths(8)->toDateString()],
            ['kode' => 'MKN-002', 'nama' => 'Indomie Soto',           'kat' => 'Makanan & Snack',   'beli' => 2800,  'jual' => 3500,  'diskon' => 0,  'barcode' => '8996001234002', 'stok' => 100, 'min' => 20, 'exp' => now()->addMonths(8)->toDateString()],
            ['kode' => 'MKN-003', 'nama' => 'Chitato Sapi Panggang',  'kat' => 'Makanan & Snack',   'beli' => 8000,  'jual' => 10000, 'diskon' => 0,  'barcode' => '8996001234003', 'stok' => 60,  'min' => 10, 'exp' => now()->addMonths(4)->toDateString()],
            ['kode' => 'MKN-004', 'nama' => 'Oreo Coklat',            'kat' => 'Makanan & Snack',   'beli' => 5000,  'jual' => 6500,  'diskon' => 0,  'barcode' => '8996001234004', 'stok' => 80,  'min' => 15, 'exp' => now()->addMonths(6)->toDateString()],
            ['kode' => 'MKN-005', 'nama' => 'Roti Tawar Sari Roti',   'kat' => 'Makanan & Snack',   'beli' => 13000, 'jual' => 16000, 'diskon' => 0,  'barcode' => '8996001234005', 'stok' => 25,  'min' => 5,  'exp' => now()->addDays(7)->toDateString()],
            ['kode' => 'MKN-006', 'nama' => 'Mie Sedaap Ayam Bawang','kat' => 'Makanan & Snack',   'beli' => 2600,  'jual' => 3200,  'diskon' => 0,  'barcode' => '8996001234006', 'stok' => 120, 'min' => 20, 'exp' => now()->addMonths(9)->toDateString()],

            // Minuman
            ['kode' => 'MNM-001', 'nama' => 'Aqua 600ml',             'kat' => 'Minuman',           'beli' => 2500,  'jual' => 3500,  'diskon' => 0,  'barcode' => '8996001235001', 'stok' => 200, 'min' => 48, 'exp' => now()->addYears(1)->toDateString()],
            ['kode' => 'MNM-002', 'nama' => 'Teh Botol Sosro 350ml', 'kat' => 'Minuman',           'beli' => 3500,  'jual' => 5000,  'diskon' => 0,  'barcode' => '8996001235002', 'stok' => 72,  'min' => 24, 'exp' => now()->addMonths(5)->toDateString()],
            ['kode' => 'MNM-003', 'nama' => 'Pocari Sweat 330ml',    'kat' => 'Minuman',           'beli' => 5500,  'jual' => 7500,  'diskon' => 0,  'barcode' => '8996001235003', 'stok' => 48,  'min' => 12, 'exp' => now()->addMonths(7)->toDateString()],
            ['kode' => 'MNM-004', 'nama' => 'Coca Cola 390ml',       'kat' => 'Minuman',           'beli' => 5000,  'jual' => 7000,  'diskon' => 0,  'barcode' => '8996001235004', 'stok' => 36,  'min' => 12, 'exp' => now()->addMonths(6)->toDateString()],
            ['kode' => 'MNM-005', 'nama' => 'Good Day Mocacinno',    'kat' => 'Minuman',           'beli' => 2000,  'jual' => 2500,  'diskon' => 0,  'barcode' => '8996001235005', 'stok' => 100, 'min' => 20, 'exp' => now()->addMonths(10)->toDateString()],

            // Rokok
            ['kode' => 'RKK-001', 'nama' => 'Gudang Garam Surya 16', 'kat' => 'Rokok',             'beli' => 20000, 'jual' => 23000, 'diskon' => 0,  'barcode' => '8996001236001', 'stok' => 80,  'min' => 10, 'exp' => null],
            ['kode' => 'RKK-002', 'nama' => 'Sampoerna A Mild 16',   'kat' => 'Rokok',             'beli' => 22000, 'jual' => 25000, 'diskon' => 0,  'barcode' => '8996001236002', 'stok' => 60,  'min' => 10, 'exp' => null],
            ['kode' => 'RKK-003', 'nama' => 'Djarum Super 12',       'kat' => 'Rokok',             'beli' => 18000, 'jual' => 21000, 'diskon' => 0,  'barcode' => '8996001236003', 'stok' => 50,  'min' => 10, 'exp' => null],

            // Kebersihan
            ['kode' => 'KBR-001', 'nama' => 'Sabun Lifebuoy 75gr',   'kat' => 'Kebersihan & Sabun','beli' => 4500,  'jual' => 6000,  'diskon' => 0,  'barcode' => '8996001237001', 'stok' => 40,  'min' => 10, 'exp' => now()->addYears(2)->toDateString()],
            ['kode' => 'KBR-002', 'nama' => 'Shampoo Sunsilk 170ml', 'kat' => 'Kebersihan & Sabun','beli' => 14000, 'jual' => 18000, 'diskon' => 0,  'barcode' => '8996001237002', 'stok' => 30,  'min' => 5,  'exp' => now()->addYears(2)->toDateString()],
            ['kode' => 'KBR-003', 'nama' => 'Rinso 800gr',           'kat' => 'Kebersihan & Sabun','beli' => 18000, 'jual' => 22000, 'diskon' => 0,  'barcode' => '8996001237003', 'stok' => 25,  'min' => 5,  'exp' => now()->addYears(3)->toDateString()],
            ['kode' => 'KBR-004', 'nama' => 'Pasta Gigi Pepsodent',  'kat' => 'Kebersihan & Sabun','beli' => 8500,  'jual' => 11000, 'diskon' => 0,  'barcode' => '8996001237004', 'stok' => 35,  'min' => 5,  'exp' => now()->addYears(2)->toDateString()],

            // Bumbu Dapur
            ['kode' => 'BMB-001', 'nama' => 'Garam Refina 250gr',    'kat' => 'Bumbu Dapur',       'beli' => 2500,  'jual' => 3500,  'diskon' => 0,  'barcode' => '8996001238001', 'stok' => 50,  'min' => 10, 'exp' => null],
            ['kode' => 'BMB-002', 'nama' => 'Minyak Goreng Bimoli 1L','kat'=> 'Bumbu Dapur',       'beli' => 17000, 'jual' => 20000, 'diskon' => 0,  'barcode' => '8996001238002', 'stok' => 30,  'min' => 5,  'exp' => now()->addMonths(18)->toDateString()],
            ['kode' => 'BMB-003', 'nama' => 'Kecap Manis ABC 275ml', 'kat' => 'Bumbu Dapur',       'beli' => 10000, 'jual' => 13000, 'diskon' => 0,  'barcode' => '8996001238003', 'stok' => 20,  'min' => 5,  'exp' => now()->addMonths(12)->toDateString()],
            ['kode' => 'BMB-004', 'nama' => 'Saos Sambal Indofood',  'kat' => 'Bumbu Dapur',       'beli' => 8000,  'jual' => 10500, 'diskon' => 0,  'barcode' => '8996001238004', 'stok' => 25,  'min' => 5,  'exp' => now()->addMonths(10)->toDateString()],

            // Susu & Dairy
            ['kode' => 'SUS-001', 'nama' => 'Susu Ultra Milk 200ml', 'kat' => 'Susu & Dairy',      'beli' => 4000,  'jual' => 5500,  'diskon' => 0,  'barcode' => '8996001239001', 'stok' => 60,  'min' => 24, 'exp' => now()->addMonths(3)->toDateString()],
            ['kode' => 'SUS-002', 'nama' => 'Indomilk Kaleng 185ml', 'kat' => 'Susu & Dairy',      'beli' => 9000,  'jual' => 12000, 'diskon' => 0,  'barcode' => '8996001239002', 'stok' => 40,  'min' => 10, 'exp' => now()->addYears(1)->toDateString()],
            ['kode' => 'SUS-003', 'nama' => 'Yakult 5pcs',           'kat' => 'Susu & Dairy',      'beli' => 12000, 'jual' => 16000, 'diskon' => 0,  'barcode' => '8996001239003', 'stok' => 3,   'min' => 5,  'exp' => now()->addDays(14)->toDateString()], // stok rendah

            // Obat & Suplemen
            ['kode' => 'OBT-001', 'nama' => 'Paracetamol 500mg 10s', 'kat' => 'Obat & Suplemen',   'beli' => 3500,  'jual' => 5000,  'diskon' => 0,  'barcode' => '8996001240001', 'stok' => 30,  'min' => 5,  'exp' => now()->addYears(2)->toDateString()],
            ['kode' => 'OBT-002', 'nama' => 'Antangin JRG 12 Kapsul','kat'=> 'Obat & Suplemen',   'beli' => 6000,  'jual' => 8000,  'diskon' => 0,  'barcode' => '8996001240002', 'stok' => 20,  'min' => 5,  'exp' => now()->addMonths(18)->toDateString()],
            ['kode' => 'OBT-003', 'nama' => 'Tolak Angin Cair 15ml', 'kat' => 'Obat & Suplemen',   'beli' => 5000,  'jual' => 7000,  'diskon' => 0,  'barcode' => '8996001240003', 'stok' => 4,   'min' => 5,  'exp' => now()->addDays(20)->toDateString()], // hampir kedaluwarsa & stok rendah

            // Alat Tulis
            ['kode' => 'ATK-001', 'nama' => 'Pulpen Pilot G-2',      'kat' => 'Alat Tulis',        'beli' => 8000,  'jual' => 12000, 'diskon' => 0,  'barcode' => '8996001241001', 'stok' => 20,  'min' => 5,  'exp' => null],
            ['kode' => 'ATK-002', 'nama' => 'Buku Tulis Sidu 38lbr', 'kat' => 'Alat Tulis',        'beli' => 3500,  'jual' => 5000,  'diskon' => 0,  'barcode' => '8996001241002', 'stok' => 50,  'min' => 10, 'exp' => null],
        ];

        foreach ($produkData as $p) {
            $kategoriId = $kategoris[$p['kat']] ?? null;

            $produk = Produk::firstOrCreate(
                ['kode_produk' => $p['kode']],
                [
                    'nama_produk'   => $p['nama'],
                    'kategori_id'   => $kategoriId,
                    'harga_beli'    => $p['beli'],
                    'harga_jual'    => $p['jual'],
                    'diskon_persen' => $p['diskon'],
                    'barcode'       => $p['barcode'],
                    'satuan'        => 'pcs',
                    'is_active'     => true,
                ]
            );

            Stok::updateOrCreate(
                ['produk_id' => $produk->produk_id],
                [
                    'jumlah_stok'         => $p['stok'],
                    'stok_minimal'        => $p['min'],
                    'tanggal_kedaluwarsa' => $p['exp'],
                    'lokasi_rak'          => 'Rak-' . strtoupper(substr($p['kat'], 0, 3)),
                ]
            );
        }

        $this->command->info('ðŸ“¦ Produk seeded: ' . count($produkData) . ' produk dengan stok.');
    }
}
