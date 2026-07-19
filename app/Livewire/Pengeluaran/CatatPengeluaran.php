<?php

namespace App\Livewire\Pengeluaran;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Catat Pengeluaran')]
class CatatPengeluaran extends Component
{
    public string $jenisPengeluaran = 'per_unit'; // 'per_unit' | 'umum_bumdes'

    public ?int $unitWisataId = null;

    public string $tanggal = '';

    public string $keterangan = '';

    public ?int $kodeAkunId = null;

    public string $nominal = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['direktur_bumdes', 'sekretaris', 'bendahara'])) {
            abort(403, 'Akses ditolak.');
        }

        $this->tanggal = Carbon::today()->format('Y-m-d');
    }

    public function updatedJenisPengeluaran(): void
    {
        // Reset unit saat jenis berubah
        $this->unitWisataId = null;
    }

    public function submit(): void
    {
        $this->validate([
            'jenisPengeluaran' => 'required|in:per_unit,umum_bumdes',
            'unitWisataId' => $this->jenisPengeluaran === 'per_unit' ? 'required|exists:unit_wisata,id' : 'nullable',
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:500',
            'kodeAkunId' => 'required|exists:kode_akun,id',
            'nominal' => 'required|numeric|min:1',
        ], [
            'unitWisataId.required' => 'Pilih unit wisata terlebih dahulu.',
            'keterangan.required' => 'Keterangan wajib diisi.',
            'kodeAkunId.required' => 'Pilih jenis pengeluaran (akun biaya) terlebih dahulu.',
            'nominal.min' => 'Nominal harus lebih dari 0.',
        ]);

        $nominalValue = (float) $this->nominal;
        $date = Carbon::parse($this->tanggal);
        $unitId = $this->jenisPengeluaran === 'per_unit' ? $this->unitWisataId : null;

        DB::transaction(function () use ($nominalValue, $date, $unitId) {
            $akunKas = KodeAkun::where('kode', '1-1100')->firstOrFail();

            // Tentukan prefix berdasarkan jenis pengeluaran:
            // - "Per Unit Wisata" → K + kode unit (misal KSB, KSC, KBC)
            // - "Umum BUMDes"    → KBM (kode tetap untuk pengeluaran level BUMDes)
            $kodeUnit = 'BM'; // default untuk Umum BUMDes
            if ($unitId !== null) {
                $unitWisata = UnitWisata::find($unitId);
                $kodeUnit = strtoupper($unitWisata->kode ?? 'BM');
            }
            $prefix = 'K'.$kodeUnit;

            // Urutan dipisah per kombinasi prefix (K+kode) dan bulan+tahun berjalan
            // lockForUpdate() mencegah nomor bentrok saat input bersamaan
            $lastJurnal = JurnalUmum::where('nomor_bukti', 'like', $prefix.'%')
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

            $nomorBukti = $prefix.str_pad($nextUrut, 3, '0', STR_PAD_LEFT);

            // 1. Baris DEBET: ke akun biaya yang dipilih
            JurnalUmum::create([
                'nomor_bukti' => $nomorBukti,
                'tanggal' => $this->tanggal,
                'keterangan' => $this->keterangan,
                'kode_akun_id' => $this->kodeAkunId,
                'debet' => $nominalValue,
                'kredit' => 0,
                'transaksi_harian_id' => null,
                'unit_wisata_id' => $unitId,
            ]);

            // 2. Baris KREDIT: dari akun Kas (1-1100)
            JurnalUmum::create([
                'nomor_bukti' => $nomorBukti,
                'tanggal' => $this->tanggal,
                'keterangan' => $this->keterangan,
                'kode_akun_id' => $akunKas->id,
                'debet' => 0,
                'kredit' => $nominalValue,
                'transaksi_harian_id' => null,
                'unit_wisata_id' => $unitId,
            ]);
        });

        \Flux::toast(variant: 'success', text: 'Pengeluaran berhasil dicatat dengan nomor bukti.');

        // Reset form
        $this->reset(['keterangan', 'kodeAkunId', 'nominal', 'unitWisataId']);
        $this->tanggal = Carbon::today()->format('Y-m-d');
        $this->jenisPengeluaran = 'per_unit';
    }

    public function render()
    {
        $units = UnitWisata::orderBy('nama')->get();
        $akunBiaya = KodeAkun::where('tipe', 'beban')->orderBy('kode')->get();

        return view('livewire.pengeluaran.catat-pengeluaran', [
            'units' => $units,
            'akunBiaya' => $akunBiaya,
        ]);
    }
}
