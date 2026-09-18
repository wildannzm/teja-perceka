<?php

namespace Database\Seeders;

use App\Models\BusinessUnit;
use Illuminate\Database\Seeder;

class BusinessUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'name' => 'Bukit Sampora',
                'code' => 'BS',
                'input_frequency' => 'daily',
            ],
            [
                'name' => 'Buper Ciranca',
                'code' => 'BC',
                'input_frequency' => 'daily',
            ],
            [
                'name' => 'Sawah Bengkok',
                'code' => 'SB',
                'input_frequency' => 'daily',
            ],
            [
                'name' => 'Situ Ciranca',
                'code' => 'SC',
                'input_frequency' => 'daily',
            ],
            [
                'name' => 'TPS',
                'code' => 'TPS',
                'input_frequency' => 'weekly',
            ],
        ];

        foreach ($units as $unit) {
            BusinessUnit::firstOrCreate(['code' => $unit['code']], $unit);
        }
    }
}
