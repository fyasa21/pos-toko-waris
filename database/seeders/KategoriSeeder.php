<?php

namespace Database\Seeders;

use App\Models\Kategori;
use Illuminate\Database\Seeder;

class KategoriSeeder extends Seeder
{
    /**
     * Mengisi data kategori awal yang dipakai oleh produk toko.
     */
    public function run(): void
    {
        $kategoris = [
            ['nama_kategori' => 'Makanan & Snack',    'deskripsi' => 'Berbagai makanan ringan dan snack'],
            ['nama_kategori' => 'Minuman',             'deskripsi' => 'Air mineral, minuman kemasan, dll'],
            ['nama_kategori' => 'Rokok',               'deskripsi' => 'Rokok berbagai merek'],
            ['nama_kategori' => 'Kebersihan & Sabun',  'deskripsi' => 'Produk kebersihan diri dan rumah'],
            ['nama_kategori' => 'Bumbu Dapur',         'deskripsi' => 'Bumbu masak dan rempah'],
            ['nama_kategori' => 'Susu & Dairy',        'deskripsi' => 'Susu, keju, dan produk dairy'],
            ['nama_kategori' => 'Obat & Suplemen',     'deskripsi' => 'Obat bebas dan suplemen kesehatan'],
            ['nama_kategori' => 'Alat Tulis',          'deskripsi' => 'Perlengkapan alat tulis'],
        ];

        foreach ($kategoris as $k) {
            Kategori::firstOrCreate(['nama_kategori' => $k['nama_kategori']], $k);
        }

        $this->command->info('ðŸ·ï¸  Kategori seeded: ' . count($kategoris) . ' kategori.');
    }
}
