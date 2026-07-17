<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['code' => '1-1100', 'name' => 'Kas', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-1200', 'name' => 'Bank', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-1300', 'name' => 'Piutang Dagang', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-1400', 'name' => 'Piutang Karyawan', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-1500', 'name' => 'Persediaan Barang Dagang', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2100', 'name' => 'Tanah', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2200', 'name' => 'Bangunan', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2300', 'name' => 'Kendaraan', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2400', 'name' => 'Peralatan & Mesin', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2500', 'name' => 'Akum. Penyusutan Bangunan', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2600', 'name' => 'Akum. Penyusutan Kendaraan', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            ['code' => '1-2700', 'name' => 'Akum. Penyusutan Peralatan & Mesin', 'normal_balance' => 'DEBET', 'report_type' => 'NERACA'],
            
            ['code' => '2-1100', 'name' => 'Hutang Dagang', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],
            ['code' => '2-1200', 'name' => 'Hutang Bank Jk. Pendek', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],
            ['code' => '2-1300', 'name' => 'Hutang Lancar Lainnya', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],
            ['code' => '2-2100', 'name' => 'Hutang Bank Jangka-Panjang', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],
            ['code' => '2-2200', 'name' => 'Hutang Jk. Panjang Lainnya', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],

            ['code' => '3-1000', 'name' => 'Modal Disetor', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],
            ['code' => '3-2000', 'name' => 'Laba/Rugi Bulan Lalu', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],
            ['code' => '3-3000', 'name' => 'Laba Bersih Tahun Berjalan', 'normal_balance' => 'KREDIT', 'report_type' => 'NERACA'],

            ['code' => '4-1000', 'name' => 'Penjualan Barang Dagang', 'normal_balance' => 'KREDIT', 'report_type' => 'LABA RUGI'],
            ['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'normal_balance' => 'KREDIT', 'report_type' => 'LABA RUGI'],
            ['code' => '4-3000', 'name' => 'Pendapatan sewa', 'normal_balance' => 'KREDIT', 'report_type' => 'LABA RUGI'],
            ['code' => '4-4000', 'name' => 'Retur Penjualan', 'normal_balance' => 'KREDIT', 'report_type' => 'LABA RUGI'],
            ['code' => '4-5000', 'name' => 'Pendapatan lainnya', 'normal_balance' => 'KREDIT', 'report_type' => 'LABA RUGI'],

            ['code' => '5-1000', 'name' => 'Harga Pokok Penjualan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],

            ['code' => '6-0001', 'name' => 'Biaya Promosi & Iklan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0002', 'name' => 'Gaji', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0003', 'name' => 'Tunjangan Makan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0004', 'name' => 'Biaya THR', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0005', 'name' => 'Biaya Insentif dan Bonus', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0006', 'name' => 'Biaya Perjalanan Dinas', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0007', 'name' => 'Biaya Transportasi, BBM, Toll & Parkir', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0008', 'name' => 'Biaya Listrik, Air dan Gas', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0009', 'name' => 'Biaya Telepon, Fax & Internet', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0011', 'name' => 'Biaya Materai', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0012', 'name' => 'Biaya ATK & Fotocopy', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0013', 'name' => 'Biaya Sewa', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0014', 'name' => 'Biaya Pos, Paket, Kurir', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0015', 'name' => 'Biaya Perbaikan dan Pemeliharaan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0016', 'name' => 'Biaya Penyusutan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0017', 'name' => 'Biaya Rapat', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0018', 'name' => 'Biaya Asuransi', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0019', 'name' => 'Biaya Sewa Kendaraan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0020', 'name' => 'Biaya Donasi / Sumbangan', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0021', 'name' => 'Biaya Jasa Paking Sayuran', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0022', 'name' => 'Biaya Jasa Potong Ayam dan Paking Ayam', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            ['code' => '6-0023', 'name' => 'Biaya Operasional Lainnya', 'normal_balance' => 'DEBET', 'report_type' => 'LABA RUGI'],
            
            ['code' => '7-1000', 'name' => 'Pendapatan Luar Usaha/Bunga Bank', 'normal_balance' => 'KREDIT', 'report_type' => 'LABA RUGI'],
        ];

        foreach ($accounts as $acc) {
            Account::firstOrCreate(['code' => $acc['code']], $acc);
        }
    }
}
