<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['id_role' => 1, 'nama_role' => 'member'],
            ['id_role' => 2, 'nama_role' => 'panitia'],
            ['id_role' => 3, 'nama_role' => 'admin'],
            ['id_role' => 4, 'nama_role' => 'keuangan'],
        ];

        foreach ($roles as $role) {
            DB::table('role')->updateOrInsert(['id_role' => $role['id_role']], $role);
        }
    }
}
