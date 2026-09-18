<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
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
        $sawahBengkok = BusinessUnit::where('code', 'SB')->first();
        $unitHead = User::firstOrCreate(
            ['email' => 'kepala.sawahbengkok@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Sawah Bengkok',
                'password' => $password,
                'business_unit_id' => $sawahBengkok ? $sawahBengkok->id : null,
            ]
        );
        $unitHead->assignRole('kepala_unit');

        // 2. Situ Ciranca unit head
        $situCiranca = BusinessUnit::where('code', 'SC')->first();
        $unitHeadSC = User::firstOrCreate(
            ['email' => 'kepala.situciranca@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Situ Ciranca',
                'password' => $password,
                'business_unit_id' => $situCiranca ? $situCiranca->id : null,
            ]
        );
        $unitHeadSC->assignRole('kepala_unit');

        // 3. Bukit Sampora unit head
        $bukitSampora = BusinessUnit::where('code', 'BS')->first();
        $unitHeadBS = User::firstOrCreate(
            ['email' => 'kepala.bukitsampora@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Bukit Sampora',
                'password' => $password,
                'business_unit_id' => $bukitSampora ? $bukitSampora->id : null,
            ]
        );
        $unitHeadBS->assignRole('kepala_unit');

        // 4. Buper Ciranca unit head
        $buperCiranca = BusinessUnit::where('code', 'BC')->first();
        $unitHeadBC = User::firstOrCreate(
            ['email' => 'kepala.buperciranca@tejaperceka.com'],
            [
                'name' => 'Kepala Unit Buper Ciranca',
                'password' => $password,
                'business_unit_id' => $buperCiranca ? $buperCiranca->id : null,
            ]
        );
        $unitHeadBC->assignRole('kepala_unit');

        // 5. TPS unit head
        $tps = BusinessUnit::where('code', 'TPS')->first();
        $unitHeadTPS = User::firstOrCreate(
            ['email' => 'kepala.tps@tejaperceka.com'],
            [
                'name' => 'Kepala Unit TPS',
                'password' => $password,
                'business_unit_id' => $tps ? $tps->id : null,
            ]
        );
        $unitHeadTPS->assignRole('kepala_unit');

        // 6. Secretary
        $secretary = User::firstOrCreate(
            ['email' => 'sekretaris@tejaperceka.com'],
            [
                'name' => 'Sekretaris BUMDes',
                'password' => $password,
            ]
        );
        $secretary->assignRole('sekretaris');

        // 7. Treasurer
        $treasurer = User::firstOrCreate(
            ['email' => 'bendahara@tejaperceka.com'],
            [
                'name' => 'Bendahara BUMDes',
                'password' => $password,
            ]
        );
        $treasurer->assignRole('bendahara');

        // 8. BUMDes director
        $director = User::firstOrCreate(
            ['email' => 'direktur@tejaperceka.com'],
            [
                'name' => 'Direktur BUMDes',
                'password' => $password,
            ]
        );
        $director->assignRole('direktur_bumdes');

        // 9. Village head
        $villageHead = User::firstOrCreate(
            ['email' => 'kepaladesa@tejaperceka.com'],
            [
                'name' => 'Kepala Desa Teja',
                'password' => $password,
            ]
        );
        $villageHead->assignRole('kepala_desa');

        // 10. Supervisor
        $supervisor = User::firstOrCreate(
            ['email' => 'pengawas@tejaperceka.com'],
            [
                'name' => 'Pengawas BUMDes',
                'password' => $password,
            ]
        );
        $supervisor->assignRole('pengawas');
    }
}
