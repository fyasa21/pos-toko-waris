<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Menjalankan seluruh seeder utama untuk mengisi data awal aplikasi.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            KategoriSeeder::class,
            PemasokSeeder::class,
            ProdukSeeder::class,
            TransaksiSeeder::class,
        ]);

        $this->command->info('âœ… Seeder selesai! Database siap digunakan.');
        $this->command->table(
            ['Role', 'Username', 'Password'],
            [
                ['Pemilik', 'pemilik', 'password123'],
                ['Kasir 1', 'kasir1',  'password123'],
                ['Kasir 2', 'kasir2',  'password123'],
            ]
        );
    }
}
