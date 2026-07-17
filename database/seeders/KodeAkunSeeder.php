<?php

namespace Database\Seeders;

use App\Models\KodeAkun;
use Illuminate\Database\Seeder;

class KodeAkunSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'kode' => '1-1100',
                'nama' => 'Kas',
                'tipe' => 'aktiva',
            ],
            [
                'kode' => '4-2000',
                'nama' => 'Pendapatan Jasa',
                'tipe' => 'pendapatan',
            ],
            [
                'kode' => '5-1000',
                'nama' => 'Biaya Operasional',
                'tipe' => 'beban',
            ],
        ];

        foreach ($accounts as $account) {
            KodeAkun::firstOrCreate(['kode' => $account['kode']], $account);
        }
    }
}
