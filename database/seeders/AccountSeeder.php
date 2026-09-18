<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['code' => '1-1000', 'name' => 'AKTIVA LANCAR', 'type' => 'header', 'is_header' => true],
            ['code' => '1-1100', 'name' => 'Kas', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-1200', 'name' => 'Bank', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-1300', 'name' => 'Piutang Dagang', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-1400', 'name' => 'Piutang Karyawan', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-1500', 'name' => 'Persediaan Barang Dagang', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2000', 'name' => 'AKTIVA TIDAK LANCAR', 'type' => 'header', 'is_header' => true],
            ['code' => '1-2100', 'name' => 'Tanah', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2200', 'name' => 'Bangunan', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2300', 'name' => 'Kendaraan', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2400', 'name' => 'Peralatan & Mesin', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2500', 'name' => 'Akum. Penyusutan Bangunan', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2600', 'name' => 'Akum. Penyusutan Kendaraan', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '1-2700', 'name' => 'Akum. Penyusutan Peralatan & Mesin', 'type' => 'aktiva', 'is_header' => false],
            ['code' => '2-0000', 'name' => 'KEWAJIBAN', 'type' => 'header', 'is_header' => true],
            ['code' => '2-1000', 'name' => 'KEWAJIBAN JANGKA PENDEK', 'type' => 'header', 'is_header' => true],
            ['code' => '2-1100', 'name' => 'Hutang Dagang', 'type' => 'kewajiban', 'is_header' => false],
            ['code' => '2-1200', 'name' => 'Hutang Bank Jk. Pendek', 'type' => 'kewajiban', 'is_header' => false],
            ['code' => '2-1300', 'name' => 'Hutang Lancar Lainnya', 'type' => 'kewajiban', 'is_header' => false],
            ['code' => '2-2000', 'name' => 'KEWAJIBAN JANGKA PANJANG', 'type' => 'header', 'is_header' => true],
            ['code' => '2-2100', 'name' => 'Hutang Bank Jangka-Panjang', 'type' => 'kewajiban', 'is_header' => false],
            ['code' => '2-2200', 'name' => 'Hutang Jk. Panjang Lainnya', 'type' => 'kewajiban', 'is_header' => false],
            ['code' => '3-0000', 'name' => 'EKUITAS', 'type' => 'header', 'is_header' => true],
            ['code' => '3-1000', 'name' => 'Modal Disetor', 'type' => 'ekuitas', 'is_header' => false],
            ['code' => '3-2000', 'name' => 'Laba/Rugi Bulan Lalu', 'type' => 'ekuitas', 'is_header' => false],
            ['code' => '3-3000', 'name' => 'Laba Bersih Tahun Berjalan', 'type' => 'ekuitas', 'is_header' => false],
            ['code' => '4-0000', 'name' => 'PENDAPATAN USAHA', 'type' => 'header', 'is_header' => true],
            ['code' => '4-1000', 'name' => 'Penjualan Barang Dagang', 'type' => 'pendapatan', 'is_header' => false],
            ['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'type' => 'pendapatan', 'is_header' => false],
            ['code' => '4-3000', 'name' => 'Pendapatan sewa', 'type' => 'pendapatan', 'is_header' => false],
            ['code' => '4-4000', 'name' => 'Retur Penjualan', 'type' => 'pendapatan', 'is_header' => false],
            ['code' => '4-5000', 'name' => 'Pendapatan lainnya', 'type' => 'pendapatan', 'is_header' => false],
            ['code' => '5-0000', 'name' => 'HARGA POKOK PENJUALAN', 'type' => 'header', 'is_header' => true],
            ['code' => '5-1000', 'name' => 'Harga Pokok Penjualan', 'type' => 'hpp', 'is_header' => false],
            ['code' => '6-0000', 'name' => 'BIAYA USAHA', 'type' => 'header', 'is_header' => true],
            ['code' => '6-0001', 'name' => 'Biaya Promosi & Iklan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0002', 'name' => 'Gaji', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0003', 'name' => 'Tunjangan Makan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0004', 'name' => 'Biaya THR', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0005', 'name' => 'Biaya Insentif dan Bonus', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0006', 'name' => 'Biaya Perjalanan Dinas', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0007', 'name' => 'Biaya Transportasi, BBM, Toll & Parkir', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0008', 'name' => 'Biaya Listrik, Air dan Gas', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0009', 'name' => 'Biaya Telepon, Fax & Internet', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0011', 'name' => 'Biaya Materai', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0012', 'name' => 'Biaya ATK & Fotocopy', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0013', 'name' => 'Biaya Sewa', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0014', 'name' => 'Biaya Pos, Paket, Kurir', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0015', 'name' => 'Biaya Perlengkapan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0016', 'name' => 'Biaya Penyusutan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0017', 'name' => 'Biaya Rapat', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0018', 'name' => 'Biaya Asuransi', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0019', 'name' => 'Biaya Sewa Kendaraan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0020', 'name' => 'Biaya Donasi / Sumbangan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0021', 'name' => 'Biaya Pemeliharaan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0022', 'name' => 'Biaya Pembangunan', 'type' => 'beban', 'is_header' => false],
            ['code' => '6-0023', 'name' => 'Biaya Operasional Lainnya', 'type' => 'beban', 'is_header' => false],
            ['code' => '7-0000', 'name' => 'PENDAPATAN & BIAYA LAIN-LAIN', 'type' => 'header', 'is_header' => true],
            ['code' => '7-1000', 'name' => 'Pendapatan Luar Usaha/Bunga Bank', 'type' => 'pendapatan_lain', 'is_header' => false],
            ['code' => '7-2000', 'name' => 'Beban Luar Usaha/Pajak & Adm Bank', 'type' => 'beban_lain', 'is_header' => false],
        ];

        foreach ($accounts as $index => $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                array_merge($account, ['sort_order' => $index + 1])
            );
        }
    }
}
