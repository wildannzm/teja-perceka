<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'super_admin',
            'kepala_unit',
            'sekretaris',
            'bendahara',
            'direktur_bumdes',
            'kepala_desa',
            'pengawas',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

    }
}
