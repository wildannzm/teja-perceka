<?php

namespace Database\Seeders;

use App\Models\UnitWisata;
use Illuminate\Database\Seeder;

class UnitWisataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'nama' => 'Bukit Sampora',
                'kode' => 'BS',
                'frekuensi_input' => 'harian',
            ],
            [
                'nama' => 'Buper Ciranca',
                'kode' => 'BC',
                'frekuensi_input' => 'harian',
            ],
            [
                'nama' => 'Sawah Bengkok',
                'kode' => 'SB',
                'frekuensi_input' => 'harian',
            ],
            [
                'nama' => 'Situ Ciranca',
                'kode' => 'SC',
                'frekuensi_input' => 'harian',
            ],
            [
                'nama' => 'TPS',
                'kode' => 'TPS',
                'frekuensi_input' => 'mingguan',
            ],
        ];

        foreach ($units as $unit) {
            UnitWisata::firstOrCreate(['kode' => $unit['kode']], $unit);
        }
    }
}
