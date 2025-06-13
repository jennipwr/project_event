<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PenggunaSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'id' => 'ADM-001',
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin123'),
                'role_id_role' => 3,
            ],
            [
                'id' => 'KGN-001',
                'name' => 'Keuangan User',
                'email' => 'keuangan@example.com',
                'password' => Hash::make('keuangan123'),
                'role_id_role' => 4,
            ],
            [
                'id' => 'PN-001',
                'name' => 'Panitia User',
                'email' => 'panitia@example.com',
                'password' => Hash::make('panitia123'),
                'role_id_role' => 2,
            ],
            [
                'id' => 'MB-001',
                'name' => 'Member User',
                'email' => 'member@example.com',
                'password' => Hash::make('member123'),
                'role_id_role' => 1,
            ],
        ];

        foreach ($users as $user) {
            DB::table('pengguna')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
