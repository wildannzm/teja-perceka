<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            'BUMDes Pusat',
            'TPS',
            'Situ Ciranca',
            'Buper Ciranca',
            'Bukit Sampora',
            'Sawah Bengkok',
        ];

        foreach ($units as $unit) {
            \App\Models\Unit::firstOrCreate(['name' => $unit]);
        }
    }
}
