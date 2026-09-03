<?php

namespace App\Livewire\Pengeluaran;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
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

    public function submit(): void
    {
        $this->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:500',
            'kodeAkunId' => 'required|exists:kode_akun,id',
            'nominal' => 'required|numeric|min:1',
        ], [
            'keterangan.required' => 'Keterangan wajib diisi.',
            'kodeAkunId.required' => 'Pilih jenis pengeluaran (akun biaya) terlebih dahulu.',
            'nominal.min' => 'Nominal harus lebih dari 0.',
        ]);

        $nominalValue = (float) $this->nominal;
        $date = Carbon::parse($this->tanggal);
        $unitId = null; // Selalu null karena pengeluaran umum BUMDes

        DB::transaction(function () use ($nominalValue, $date, $unitId) {
            $akunKas = KodeAkun::where('kode', '1-1100')->firstOrFail();

            // "Umum BUMDes" -> KBM (kode tetap untuk pengeluaran level BUMDes)
            $prefix = 'KBM';

            $existingNumbers = JurnalUmum::where('nomor_bukti', 'like', $prefix.'%')
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->lockForUpdate()
                ->pluck('nomor_bukti')
                ->map(fn($nomor) => (int) substr($nomor, -3))
                ->unique()
                ->toArray();

            $nextUrut = 1;
            while (in_array($nextUrut, $existingNumbers)) {
                $nextUrut++;
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
        $this->reset(['keterangan', 'kodeAkunId', 'nominal']);
        $this->tanggal = Carbon::today()->format('Y-m-d');
    }

    public function render()
    {
        $akunBiaya = KodeAkun::where('tipe', 'beban')->orderBy('kode')->get();

        return view('livewire.pengeluaran.catat-pengeluaran', [
            'akunBiaya' => $akunBiaya,
        ]);
    }
}
