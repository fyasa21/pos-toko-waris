<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Mengisi data akun awal seperti pemilik dan kasir.
     */
    public function run(): void
    {
        $users = [
            [
                'username'      => 'pemilik',
                'email'         => 'pemilik@tokowaris.local',
                'password_hash' => Hash::make('password123'),
                'role'          => 'pemilik',
                'nama_lengkap'  => 'Bapak Waris Santoso',
                'is_active'     => true,
            ],
            [
                'username'      => 'kasir1',
                'email'         => 'kasir1@tokowaris.local',
                'password_hash' => Hash::make('password123'),
                'role'          => 'kasir',
                'nama_lengkap'  => 'Siti Rahayu',
                'is_active'     => true,
            ],
            [
                'username'      => 'kasir2',
                'email'         => 'kasir2@tokowaris.local',
                'password_hash' => Hash::make('password123'),
                'role'          => 'kasir',
                'nama_lengkap'  => 'Ahmad Fauzi',
                'is_active'     => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['username' => $user['username']], $user);
        }

        $this->command->info('ðŸ‘¤ Users seeded: 1 pemilik, 2 kasir.');
    }
}
