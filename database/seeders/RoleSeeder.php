<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles (including other roles so Kepala Desa can manage them)
        $roles = [
            'Kepala Desa',
            'Direktur BUMDes',
            'Sekretaris',
            'Bendahara',
            'Kepala Unit',
            'Pengawas',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create default admin user (Kepala Desa)
        $admin = User::firstOrCreate(
            ['email' => 'kepaladesa@bumdes.com'],
            [
                'name' => 'Kepala Desa',
                'password' => Hash::make('password123'),
            ]
        );

        if (!$admin->hasRole('Kepala Desa')) {
            $admin->assignRole('Kepala Desa');
        }

        // Create default Sekretaris user
        $sekretaris = User::firstOrCreate(
            ['email' => 'sekretaris@bumdes.com'],
            [
                'name' => 'Sekretaris',
                'password' => Hash::make('password123'),
            ]
        );

        if (!$sekretaris->hasRole('Sekretaris')) {
            $sekretaris->assignRole('Sekretaris');
        }
    }
}
