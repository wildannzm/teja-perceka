<?php

namespace App\Livewire\KepalaUnit;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Catat Pengeluaran')]
class CatatPengeluaran extends Component
{
    #[Locked]
    public ?int $unitId = null;

    public ?UnitWisata $unit = null;

    public string $tanggal = '';

    // Array item pengeluaran
    // Format: [['kode_akun_id' => int, 'keterangan' => string, 'nominal' => float]]
    public array $items = [];

    public float $totalPengeluaran = 0;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unit   = UnitWisata::findOrFail($this->unitId);
        $this->tanggal = Carbon::today()->format('Y-m-d');

        // Mulai dengan satu baris kosong
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'kode_akun_id' => '',
            'keterangan'   => '',
            'nominal'      => '',
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function updatedItems(): void
    {
        $this->calculateTotal();
    }

    public function updatedTanggal(): void
    {
        // Tidak ada aksi khusus, tanggal bebas dipilih
    }

    private function calculateTotal(): void
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += (float) ($item['nominal'] ?: 0);
        }
        $this->totalPengeluaran = $total;
    }

    public function submit(): void
    {
        $this->validate([
            'tanggal'                    => 'required|date',
            'items'                      => 'required|array|min:1',
            'items.*.kode_akun_id'       => 'required|exists:kode_akun,id',
            'items.*.keterangan'         => 'required|string|max:500',
            'items.*.nominal'            => 'required|numeric|min:1',
        ], [
            'items.required'             => 'Minimal satu item pengeluaran harus diisi.',
            'items.*.kode_akun_id.required' => 'Pilih jenis biaya untuk setiap item.',
            'items.*.keterangan.required'   => 'Keterangan wajib diisi untuk setiap item.',
            'items.*.nominal.required'      => 'Nominal wajib diisi untuk setiap item.',
            'items.*.nominal.min'           => 'Nominal harus lebih dari 0.',
        ]);

        // Filter item yang valid (nominal > 0)
        $validItems = array_filter($this->items, fn($item) => (float) ($item['nominal'] ?? 0) > 0 && ! empty($item['kode_akun_id']));

        if (empty($validItems)) {
            $this->addError('items', 'Minimal satu item pengeluaran dengan nominal valid harus diisi.');
            return;
        }

        $date     = Carbon::parse($this->tanggal);
        $kodeUnit = strtoupper($this->unit->kode ?? 'XX');
        $prefix   = 'K' . $kodeUnit;

        DB::transaction(function () use ($date, $prefix, $validItems) {
            $akunKas = KodeAkun::where('kode', '1-1100')->firstOrFail();

            // Generate nomor bukti berurutan per bulan
            $lastJurnal = JurnalUmum::where('nomor_bukti', 'like', $prefix . '%')
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->lockForUpdate()
                ->orderBy('nomor_bukti', 'desc')
                ->first();

            $nextUrut = 1;
            if ($lastJurnal) {
                $nextUrut = ((int) substr($lastJurnal->nomor_bukti, -3)) + 1;
            }

            // Cek apakah ada transaksi_harian di tanggal ini untuk unit ini
            // (untuk mengaitkan pengeluaran ke transaksi_harian jika ada)
            $transaksiHarian = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->whereDate('tanggal', $this->tanggal)
                ->first();

            $totalPengeluaranBaru = (float) $this->totalPengeluaran;

            foreach ($validItems as $item) {
                $nominalItem = (float) $item['nominal'];
                $nomorBukti  = $prefix . str_pad($nextUrut, 3, '0', STR_PAD_LEFT);
                $keterangan  = $item['keterangan'];

                // Debet: akun biaya yang dipilih
                JurnalUmum::create([
                    'nomor_bukti'         => $nomorBukti,
                    'tanggal'             => $this->tanggal,
                    'keterangan'          => $keterangan,
                    'kode_akun_id'        => (int) $item['kode_akun_id'],
                    'debet'               => $nominalItem,
                    'kredit'              => 0,
                    'transaksi_harian_id' => $transaksiHarian?->id,
                    'unit_wisata_id'      => $this->unitId,
                ]);

                // Kredit: keluar dari Kas
                JurnalUmum::create([
                    'nomor_bukti'         => $nomorBukti,
                    'tanggal'             => $this->tanggal,
                    'keterangan'          => $keterangan,
                    'kode_akun_id'        => $akunKas->id,
                    'debet'               => 0,
                    'kredit'              => $nominalItem,
                    'transaksi_harian_id' => $transaksiHarian?->id,
                    'unit_wisata_id'      => $this->unitId,
                ]);

                $nextUrut++;
            }

            // Jika ada transaksi_harian, update total_pengeluaran
            if ($transaksiHarian) {
                $transaksiHarian->increment('total_pengeluaran', $totalPengeluaranBaru);
            }
        });

        \Flux::toast(variant: 'success', text: 'Pengeluaran unit berhasil dicatat!');

        // Reset form
        $this->reset(['items', 'totalPengeluaran']);
        $this->tanggal = Carbon::today()->format('Y-m-d');
        $this->addItem();
    }

    public function render()
    {
        $akunBiaya = KodeAkun::where('tipe', 'beban')->orderBy('kode')->get();

        return view('livewire.kepala-unit.catat-pengeluaran', [
            'akunBiaya' => $akunBiaya,
        ]);
    }
}
