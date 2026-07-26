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
            ['kode' => '1-1000', 'nama' => 'AKTIVA LANCAR', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '1-1100', 'nama' => 'Kas', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-1200', 'nama' => 'Bank', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-1300', 'nama' => 'Piutang Dagang', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-1400', 'nama' => 'Piutang Karyawan', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-1500', 'nama' => 'Persediaan Barang Dagang', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2000', 'nama' => 'AKTIVA TIDAK LANCAR', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '1-2100', 'nama' => 'Tanah', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2200', 'nama' => 'Bangunan', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2300', 'nama' => 'Kendaraan', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2400', 'nama' => 'Peralatan & Mesin', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2500', 'nama' => 'Akum. Penyusutan Bangunan', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2600', 'nama' => 'Akum. Penyusutan Kendaraan', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '1-2700', 'nama' => 'Akum. Penyusutan Peralatan & Mesin', 'tipe' => 'aktiva', 'is_header' => false],
            ['kode' => '2-0000', 'nama' => 'KEWAJIBAN', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '2-1000', 'nama' => 'KEWAJIBAN JANGKA PENDEK', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '2-1100', 'nama' => 'Hutang Dagang', 'tipe' => 'kewajiban', 'is_header' => false],
            ['kode' => '2-1200', 'nama' => 'Hutang Bank Jk. Pendek', 'tipe' => 'kewajiban', 'is_header' => false],
            ['kode' => '2-1300', 'nama' => 'Hutang Lancar Lainnya', 'tipe' => 'kewajiban', 'is_header' => false],
            ['kode' => '2-2000', 'nama' => 'KEWAJIBAN JANGKA PANJANG', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '2-2100', 'nama' => 'Hutang Bank Jangka-Panjang', 'tipe' => 'kewajiban', 'is_header' => false],
            ['kode' => '2-2200', 'nama' => 'Hutang Jk. Panjang Lainnya', 'tipe' => 'kewajiban', 'is_header' => false],
            ['kode' => '3-0000', 'nama' => 'EKUITAS', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '3-1000', 'nama' => 'Modal Disetor', 'tipe' => 'ekuitas', 'is_header' => false],
            ['kode' => '3-2000', 'nama' => 'Laba/Rugi Bulan Lalu', 'tipe' => 'ekuitas', 'is_header' => false],
            ['kode' => '3-3000', 'nama' => 'Laba Bersih Tahun Berjalan', 'tipe' => 'ekuitas', 'is_header' => false],
            ['kode' => '4-0000', 'nama' => 'PENDAPATAN USAHA', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '4-1000', 'nama' => 'Penjualan Barang Dagang', 'tipe' => 'pendapatan', 'is_header' => false],
            ['kode' => '4-2000', 'nama' => 'Pendapatan Jasa', 'tipe' => 'pendapatan', 'is_header' => false],
            ['kode' => '4-3000', 'nama' => 'Pendapatan sewa', 'tipe' => 'pendapatan', 'is_header' => false],
            ['kode' => '4-4000', 'nama' => 'Retur Penjualan', 'tipe' => 'pendapatan', 'is_header' => false],
            ['kode' => '4-5000', 'nama' => 'Pendapatan lainnya', 'tipe' => 'pendapatan', 'is_header' => false],
            ['kode' => '5-0000', 'nama' => 'HARGA POKOK PENJUALAN', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '5-1000', 'nama' => 'Harga Pokok Penjualan', 'tipe' => 'hpp', 'is_header' => false],
            ['kode' => '6-0000', 'nama' => 'BIAYA USAHA', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '6-0001', 'nama' => 'Biaya Promosi & Iklan', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0002', 'nama' => 'Gaji', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0003', 'nama' => 'Tunjangan Makan', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0004', 'nama' => 'Biaya THR', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0005', 'nama' => 'Biaya Insentif dan Bonus', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0006', 'nama' => 'Biaya Perjalanan Dinas', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0007', 'nama' => 'Biaya Transportasi, BBM, Toll & Parkir', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0008', 'nama' => 'Biaya Listrik, Air dan Gas', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0009', 'nama' => 'Biaya Telepon, Fax & Internet', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0011', 'nama' => 'Biaya Materai', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0012', 'nama' => 'Biaya ATK & Fotocopy', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0013', 'nama' => 'Biaya Sewa', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0014', 'nama' => 'Biaya Pos, Paket, Kurir', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0015', 'nama' => 'Biaya Perbaikan dan Pemeliharaan', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0016', 'nama' => 'Biaya Penyusutan', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0017', 'nama' => 'Biaya Rapat', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0018', 'nama' => 'Biaya Asuransi', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0019', 'nama' => 'Biaya Sewa Kendaraan', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0020', 'nama' => 'Biaya Donasi / Sumbangan', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0021', 'nama' => 'Biaya Jasa Paking Sayuran', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0022', 'nama' => 'Biaya Jasa Potong Ayam dan Paking Ayam', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '6-0023', 'nama' => 'Biaya Operasional Lainnya', 'tipe' => 'beban', 'is_header' => false],
            ['kode' => '7-0000', 'nama' => 'PENDAPATAN & BIAYA LAIN-LAIN', 'tipe' => 'header', 'is_header' => true],
            ['kode' => '7-1000', 'nama' => 'Pendapatan Luar Usaha/Bunga Bank', 'tipe' => 'pendapatan_lain', 'is_header' => false],
            ['kode' => '7-2000', 'nama' => 'Beban Luar Usaha/Pajak & Adm Bank', 'tipe' => 'beban_lain', 'is_header' => false],
        ];

        foreach ($accounts as $index => $account) {
            KodeAkun::updateOrCreate(
                ['kode' => $account['kode']],
                array_merge($account, ['urutan' => $index + 1])
            );
        }
    }
}
