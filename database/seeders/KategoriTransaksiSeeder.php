<?php

namespace Database\Seeders;

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use App\Models\KategoriHargaRiwayat;
use App\Models\KategoriTransaksi;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
use Illuminate\Database\Seeder;

class KategoriTransaksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pendapatanAkun = KodeAkun::where('kode', '4-2000')->first();
        $kodeAkunId = $pendapatanAkun ? $pendapatanAkun->id : null;

        $categoriesByUnit = [
            'SB' => [
                [
                    'nama' => 'Tiket Dewasa',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Tiket Anak',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 5000,
                ],
                [
                    'nama' => 'Parkir Mobil',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 5000,
                ],
                [
                    'nama' => 'Parkir Motor',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 2000,
                ],
                [
                    'nama' => 'Sewa Ban',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Sewa Pelampung',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Kios (retribusi harian)',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 150000,
                ],
                [
                    'nama' => 'Kaki Lima (retribusi harian)',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 20000,
                ],
            ],
            'SC' => [
                [
                    'nama' => 'Tiket Dewasa',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Tiket Anak',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 5000,
                ],
                [
                    'nama' => 'Parkir Mobil',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 5000,
                ],
                [
                    'nama' => 'Parkir Motor',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 3000,
                ],
                [
                    'nama' => 'Sewa Ban',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Sewa Pelampung',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Sewa Bebek Goes',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 10000,
                ],
                [
                    'nama' => 'Sewa Kios 3x2',
                    'tipe' => TipeKategori::Tahunan,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 2000000,
                ],
                [
                    'nama' => 'Sewa Kios 1,5x1',
                    'tipe' => TipeKategori::Tahunan,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 1000000,
                ],
            ],
            'BS' => [
                [
                    'nama' => 'Tiket Masuk',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 15000,
                ],
            ],
            'BC' => [
                [
                    'nama' => 'Tiket Masuk',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 20000,
                ],
                [
                    'nama' => 'Sewa Kios Buper',
                    'tipe' => TipeKategori::Tahunan,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 1000000,
                ],
            ],
            'TPS' => [
                [
                    'nama' => 'Retribusi Sampah',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 15000,
                ],
                [
                    'nama' => 'Retribusi Kegiatan',
                    'tipe' => TipeKategori::HargaXQty,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 100000,
                ],
                [
                    'nama' => 'Retribusi Sampah Wisata',
                    'tipe' => TipeKategori::Bebas,
                    'jenis' => JenisTransaksi::Pemasukan,
                    'harga' => 0,
                ],
            ],
        ];

        foreach ($categoriesByUnit as $unitKode => $categories) {
            $unit = UnitWisata::where('kode', $unitKode)->first();

            if (! $unit) {
                continue;
            }

            foreach ($categories as $catData) {
                $category = KategoriTransaksi::firstOrCreate(
                    [
                        'unit_wisata_id' => $unit->id,
                        'nama' => $catData['nama'],
                    ],
                    [
                        'kode_akun_id' => $kodeAkunId,
                        'tipe' => $catData['tipe'],
                        'jenis' => $catData['jenis'],
                    ]
                );

                // Create initial price history if not already exists
                KategoriHargaRiwayat::firstOrCreate(
                    [
                        'kategori_transaksi_id' => $category->id,
                        'berlaku_dari' => '2026-01-01',
                    ],
                    [
                        'harga' => $catData['harga'],
                    ]
                );
            }
        }
    }
}
