<?php

namespace Database\Seeders;

use App\Models\Pemasok;
use Illuminate\Database\Seeder;

class PemasokSeeder extends Seeder
{
    /**
     * Mengisi data pemasok awal yang digunakan pada modul pembelian.
     */
    public function run(): void
    {
        $pemasoks = [
            [
                'nama_pemasok'  => 'CV. Maju Bersama',
                'kontak_person' => 'Pak Hendra',
                'nomor_telepon' => '0812-3456-7890',
                'email'         => 'majubersama@email.com',
                'alamat'        => 'Jl. Industri No. 15',
                'kota'          => 'Tasikmalaya',
                'is_active'     => true,
            ],
            [
                'nama_pemasok'  => 'PT. Sumber Rejeki',
                'kontak_person' => 'Ibu Dewi',
                'nomor_telepon' => '0822-9876-5432',
                'email'         => 'sumberrejeki@email.com',
                'alamat'        => 'Jl. Raya Bandung KM 5',
                'kota'          => 'Bandung',
                'is_active'     => true,
            ],
            [
                'nama_pemasok'  => 'UD. Anugerah Jaya',
                'kontak_person' => 'Pak Sugiono',
                'nomor_telepon' => '0856-1122-3344',
                'email'         => 'anugerahjaya@email.com',
                'alamat'        => 'Jl. Pasar Baru No. 7',
                'kota'          => 'Garut',
                'is_active'     => true,
            ],
            [
                'nama_pemasok'  => 'CV. Karya Mandiri',
                'kontak_person' => 'Budi Santoso',
                'nomor_telepon' => '0878-5544-3322',
                'email'         => null,
                'alamat'        => 'Jl. Diponegoro No. 22',
                'kota'          => 'Tasikmalaya',
                'is_active'     => true,
            ],
        ];

        foreach ($pemasoks as $p) {
            Pemasok::firstOrCreate(['nama_pemasok' => $p['nama_pemasok']], $p);
        }

        $this->command->info('ðŸšš Pemasok seeded: ' . count($pemasoks) . ' pemasok.');
    }
}
