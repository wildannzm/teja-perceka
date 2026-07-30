<?php

namespace Database\Seeders;

use App\Enums\TipeKategori;
use App\Models\JurnalUmum;
use App\Models\KategoriTransaksi;
use App\Models\KodeAkun;
use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiHarianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $date = Carbon::today();
        $akunKas = KodeAkun::where('kode', '1-1100')->first();

        if (!$akunKas) {
            return;
        }

        $units = UnitWisata::all();

        foreach ($units as $unit) {
            $user = User::where('unit_wisata_id', $unit->id)->first();
            if (! $user) {
                continue;
            }

            $kategoriList = KategoriTransaksi::where('unit_wisata_id', $unit->id)->get();
            if ($kategoriList->isEmpty()) {
                continue;
            }

            DB::beginTransaction();

            try {
                $totalPemasukan = 0;
                $details = [];
                $kreditGroup = [];

                foreach ($kategoriList as $kategori) {
                    $hargaSatuan = $kategori->hargaSaat($date);
                    $qty = null;
                    $subtotal = 0;

                    if ($kategori->tipe === TipeKategori::HargaXQty) {
                        $qty = rand(5, 50); // Random qty for demo
                        $subtotal = $hargaSatuan * $qty;
                    } elseif ($kategori->tipe === TipeKategori::Flat || $kategori->tipe === TipeKategori::Tahunan) {
                        $subtotal = $hargaSatuan; // Flat/Tahunan assume 1 time
                    } else {
                        // Bebas or others
                        $subtotal = rand(1, 10) * 10000;
                    }

                    if ($subtotal > 0) {
                        $totalPemasukan += $subtotal;
                        $details[] = [
                            'kategori_transaksi_id' => $kategori->id,
                            'qty' => $qty,
                            'harga_satuan' => $hargaSatuan,
                            'subtotal' => $subtotal,
                        ];

                        $akunId = $kategori->kode_akun_id;
                        if ($akunId) {
                            if (! isset($kreditGroup[$akunId])) {
                                $kreditGroup[$akunId] = 0;
                            }
                            $kreditGroup[$akunId] += $subtotal;
                        }
                    }
                }

                if ($totalPemasukan > 0) {
                    $transaksi = TransaksiHarian::create([
                        'unit_wisata_id' => $unit->id,
                        'user_id' => $user->id,
                        'tanggal' => $date,
                        'total_pemasukan' => $totalPemasukan,
                        'catatan' => 'Demo transaction '.$date->format('Y-m-d'),
                    ]);

                    foreach ($details as $detail) {
                        TransaksiDetail::create([
                            'transaksi_harian_id' => $transaksi->id,
                            'kategori_transaksi_id' => $detail['kategori_transaksi_id'],
                            'qty' => $detail['qty'],
                            'harga_satuan' => $detail['harga_satuan'],
                            'subtotal' => $detail['subtotal'],
                        ]);
                    }

                    // --- Jurnal Umum ---
                    $kodeUnit = strtoupper($unit->kode ?? 'XX');
                    $prefixNomor = 'D'.$kodeUnit;

                    $lastJurnal = JurnalUmum::where('nomor_bukti', 'like', $prefixNomor.'%')
                        ->whereMonth('tanggal', $date->month)
                        ->whereYear('tanggal', $date->year)
                        ->lockForUpdate()
                        ->orderBy('nomor_bukti', 'desc')
                        ->first();

                    $nextUrut = 1;
                    if ($lastJurnal) {
                        $lastUrut = (int) substr($lastJurnal->nomor_bukti, -3);
                        $nextUrut = $lastUrut + 1;
                    }

                    $nomorBukti = $prefixNomor.str_pad($nextUrut, 3, '0', STR_PAD_LEFT);
                    $keteranganJurnal = 'Pemasukan Harian - '.$unit->nama;

                    // Debet Kas
                    JurnalUmum::create([
                        'nomor_bukti' => $nomorBukti,
                        'tanggal' => $date,
                        'keterangan' => $keteranganJurnal,
                        'kode_akun_id' => $akunKas->id,
                        'debet' => $totalPemasukan,
                        'kredit' => 0,
                        'transaksi_harian_id' => $transaksi->id,
                        'unit_wisata_id' => $unit->id,
                    ]);

                    // Kredit Pendapatan
                    foreach ($kreditGroup as $akunId => $jumlahKredit) {
                        JurnalUmum::create([
                            'nomor_bukti' => $nomorBukti,
                            'tanggal' => $date,
                            'keterangan' => $keteranganJurnal,
                            'kode_akun_id' => $akunId,
                            'debet' => 0,
                            'kredit' => $jumlahKredit,
                            'transaksi_harian_id' => $transaksi->id,
                            'unit_wisata_id' => $unit->id,
                        ]);
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
    }
}
