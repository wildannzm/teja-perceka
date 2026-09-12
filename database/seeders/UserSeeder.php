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

        // 1. Sawah Bengkok unit head
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

        // 2. Situ Ciranca unit head
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

        // 3. Bukit Sampora unit head
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

        // 4. Buper Ciranca unit head
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

        // 5. TPS unit head
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

        // 6. Secretary
        $sekretaris = User::firstOrCreate(
            ['email' => 'sekretaris@tejaperceka.com'],
            [
                'name' => 'Sekretaris BUMDes',
                'password' => $password,
            ]
        );
        $sekretaris->assignRole('sekretaris');

        // 7. Treasurer
        $bendahara = User::firstOrCreate(
            ['email' => 'bendahara@tejaperceka.com'],
            [
                'name' => 'Bendahara BUMDes',
                'password' => $password,
            ]
        );
        $bendahara->assignRole('bendahara');

        // 8. BUMDes director
        $direktur = User::firstOrCreate(
            ['email' => 'direktur@tejaperceka.com'],
            [
                'name' => 'Direktur BUMDes',
                'password' => $password,
            ]
        );
        $direktur->assignRole('direktur_bumdes');

        // 9. Village head
        $kepalaDesa = User::firstOrCreate(
            ['email' => 'kepaladesa@tejaperceka.com'],
            [
                'name' => 'Kepala Desa Teja',
                'password' => $password,
            ]
        );
        $kepalaDesa->assignRole('kepala_desa');

        // 10. Supervisor
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
