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
            [
                'kode' => '6-0002',
                'nama' => 'Gaji',
                'tipe' => 'beban',
            ],
            [
                'kode' => '6-0008',
                'nama' => 'Biaya Listrik, Air dan Gas',
                'tipe' => 'beban',
            ],
            [
                'kode' => '6-0015',
                'nama' => 'Biaya Perbaikan dan Pemeliharaan',
                'tipe' => 'beban',
            ],
            [
                'kode' => '6-0018',
                'nama' => 'Biaya Asuransi',
                'tipe' => 'beban',
            ],
            [
                'kode' => '6-0023',
                'nama' => 'Biaya Operasional Lainnya',
                'tipe' => 'beban',
            ],
        ];

        foreach ($accounts as $account) {
            KodeAkun::firstOrCreate(['kode' => $account['kode']], $account);
        }
    }
}
