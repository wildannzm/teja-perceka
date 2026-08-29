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

        // 0. Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@tejaperceka.com'],
            [
                'name' => 'Administrator',
                'password' => $password,
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super_admin');

        // 1. Kepala Unit Sawah Bengkok
        $sawahBengkok = UnitWisata::where('kode', 'SB')->first();
        $kepalaUnit = User::firstOrCreate(
            ['email' => 'kepala.sawahbengkok@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Sawah Bengkok',
                'password' => $password,
                'unit_wisata_id' => $sawahBengkok ? $sawahBengkok->id : null,
            ]
        );
        $kepalaUnit->assignRole('kepala_unit');

        // 2. Kepala Unit Situ Ciranca
        $situCiranca = UnitWisata::where('kode', 'SC')->first();
        $kepalaUnitSC = User::firstOrCreate(
            ['email' => 'kepala.situciranca@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Situ Ciranca',
                'password' => $password,
                'unit_wisata_id' => $situCiranca ? $situCiranca->id : null,
            ]
        );
        $kepalaUnitSC->assignRole('kepala_unit');

        // 3. Kepala Unit Bukit Sampora
        $bukitSampora = UnitWisata::where('kode', 'BS')->first();
        $kepalaUnitBS = User::firstOrCreate(
            ['email' => 'kepala.bukitsampora@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Bukit Sampora',
                'password' => $password,
                'unit_wisata_id' => $bukitSampora ? $bukitSampora->id : null,
            ]
        );
        $kepalaUnitBS->assignRole('kepala_unit');

        // 4. Kepala Unit Buper Ciranca
        $buperCiranca = UnitWisata::where('kode', 'BC')->first();
        $kepalaUnitBC = User::firstOrCreate(
            ['email' => 'kepala.buperciranca@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Buper Ciranca',
                'password' => $password,
                'unit_wisata_id' => $buperCiranca ? $buperCiranca->id : null,
            ]
        );
        $kepalaUnitBC->assignRole('kepala_unit');

        // 5. Kepala Unit TPS
        $tps = UnitWisata::where('kode', 'TPS')->first();
        $kepalaUnitTPS = User::firstOrCreate(
            ['email' => 'kepala.tps@tejaperceka.com'],
            [
                'name' => 'Kepala Unit TPS',
                'password' => $password,
                'unit_wisata_id' => $tps ? $tps->id : null,
            ]
        );
        $kepalaUnitTPS->assignRole('kepala_unit');

        // 6. Sekretaris
        $sekretaris = User::firstOrCreate(
            ['email' => 'sekretaris@tejaperceka.com'],
            [
                'name' => 'Sekretaris BUMDes',
                'password' => $password,
            ]
        );
        $sekretaris->assignRole('sekretaris');

        // 7. Bendahara
        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@tejaperceka.com'],
            [
                'name' => 'Bendahara BUMDes',
                'password' => $password,
            ]
        );
        $bendahara->assignRole('bendahara');

        // 8. Direktur BUMDes
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@tejaperceka.com'],
            [
                'name' => 'Direktur BUMDes',
                'password' => $password,
            ]
        );
        $direktur->assignRole('direktur_bumdes');

        // 9. Kepala Desa
        $kepalaDesa = User::firstOrCreate(
            ['email' => 'kepaladesa@tejaperceka.com'],
            [
                'name' => 'Kepala Desa Teja',
                'password' => $password,
            ]
        );
        $kepalaDesa->assignRole('kepala_desa');

        // 10. Pengawas
        $pengawas = User::firstOrCreate(
            ['email' => 'pengawas@tejaperceka.com'],
            [
                'name' => 'Pengawas BUMDes',
                'password' => $password,
            ]
        );
        $pengawas->assignRole('pengawas');
    }
}
