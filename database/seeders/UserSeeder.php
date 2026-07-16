<?php

namespace Database\Seeders;

use App\Models\UnitWisata;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Kepala Unit Sawah Bengkok
        $sawahBengkok = UnitWisata::where('kode', 'SB')->first();
        $kepalaUnit = User::firstOrCreate(
            ['email' => 'kepala.sawahbengkok@tejaperceka.test'],
            [
                'name' => 'Kepala Unit Sawah Bengkok',
                'password' => $password,
                'unit_wisata_id' => $sawahBengkok ? $sawahBengkok->id : null,
            ]
        );
        $kepalaUnit->assignRole('kepala_unit');

        // 2. Sekretaris
        $sekretaris = User::firstOrCreate(
            ['email' => 'sekretaris@tejaperceka.test'],
            [
                'name' => 'Sekretaris BUMDes',
                'password' => $password,
            ]
        );
        $sekretaris->assignRole('sekretaris');

        // 3. Bendahara
        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@tejaperceka.test'],
            [
                'name' => 'Bendahara BUMDes',
                'password' => $password,
            ]
        );
        $bendahara->assignRole('bendahara');

        // 4. Direktur BUMDes
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@tejaperceka.test'],
            [
                'name' => 'Direktur BUMDes',
                'password' => $password,
            ]
        );
        $direktur->assignRole('direktur_bumdes');

        // 5. Kepala Desa
        $kepalaDesa = User::firstOrCreate(
            ['email' => 'kepaladesa@tejaperceka.test'],
            [
                'name' => 'Kepala Desa Teja',
                'password' => $password,
            ]
        );
        $kepalaDesa->assignRole('kepala_desa');

        // 6. Pengawas
        $pengawas = User::firstOrCreate(
            ['email' => 'pengawas@tejaperceka.test'],
            [
                'name' => 'Pengawas BUMDes',
                'password' => $password,
            ]
        );
        $pengawas->assignRole('pengawas');
    }
}
