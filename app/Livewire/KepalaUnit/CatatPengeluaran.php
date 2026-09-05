<?php

namespace App\Livewire\KepalaUnit;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
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

    public array $items = [];

    public float $totalPengeluaran = 0;

    public string $filterMode = 'harian';

    public string $filterDate = '';

    public string $filterBulan = '';

    public string $filterSemester = '1';

    public string $filterSemesterTahun = '';

    public string $filterTahun = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?string $editingNomorBukti = null;

    public ?string $deletingNomorBukti = null;

    public string $editTanggal = '';

    public string $editKodeAkunId = '';

    public string $editKeterangan = '';

    public $editNominal = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unit = UnitWisata::findOrFail($this->unitId);
        $this->tanggal = Carbon::today()->format('Y-m-d');

        $today = Carbon::today();
        $this->filterMode = 'harian';
        $this->filterDate = $today->format('Y-m-d');
        $this->filterBulan = $today->format('Y-m');
        $this->filterSemester = $today->month <= 6 ? '1' : '2';
        $this->filterSemesterTahun = $today->format('Y');
        $this->filterTahun = $today->format('Y');

        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'kode_akun_id' => '',
            'keterangan' => '',
            'nominal' => '',
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

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['items', 'totalPengeluaran']);
        $this->tanggal = Carbon::today()->format('Y-m-d');
        $this->addItem();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingNomorBukti', 'editKodeAkunId', 'editKeterangan', 'editNominal', 'editTanggal']);
        $this->resetErrorBag();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingNomorBukti = null;
    }

    public function resetFilter(): void
    {
        $today = Carbon::today();
        $this->filterMode = 'harian';
        $this->filterDate = $today->format('Y-m-d');
        $this->filterBulan = $today->format('Y-m');
        $this->filterSemester = $today->month <= 6 ? '1' : '2';
        $this->filterSemesterTahun = $today->format('Y');
        $this->filterTahun = $today->format('Y');
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
            'tanggal' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.kode_akun_id' => 'required|exists:kode_akun,id',
            'items.*.keterangan' => 'required|string|max:500',
            'items.*.nominal' => 'required|numeric|min:1|max:9999999999999',
        ], [
            'items.required' => 'Minimal satu item pengeluaran harus diisi.',
            'items.*.kode_akun_id.required' => 'Pilih jenis biaya untuk setiap item.',
            'items.*.keterangan.required' => 'Keterangan wajib diisi untuk setiap item.',
            'items.*.nominal.required' => 'Nominal wajib diisi untuk setiap item.',
            'items.*.nominal.min' => 'Nominal harus lebih dari 0.',
            'items.*.nominal.max' => 'Nominal terlalu besar.',
        ]);

        $validItems = array_filter($this->items, fn ($item) => (float) ($item['nominal'] ?? 0) > 0 && ! empty($item['kode_akun_id']));

        if (empty($validItems)) {
            $this->addError('items', 'Minimal satu item pengeluaran dengan nominal valid harus diisi.');

            return;
        }

        $date = Carbon::parse($this->tanggal);
        $kodeUnit = strtoupper($this->unit->kode ?? 'XX');
        $prefix = 'K'.$kodeUnit;

        DB::transaction(function () use ($date, $prefix, $validItems) {
            $akunKas = KodeAkun::where('kode', '1-1100')->firstOrFail();

            $existingNumbers = JurnalUmum::where('nomor_bukti', 'like', $prefix.'%')
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->lockForUpdate()
                ->pluck('nomor_bukti')
                ->map(fn ($nomor) => (int) substr($nomor, -3))
                ->unique()
                ->toArray();

            $nextUrut = 1;

            $transaksiHarian = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->whereDate('tanggal', $this->tanggal)
                ->first();

            $totalPengeluaranBaru = (float) $this->totalPengeluaran;

            foreach ($validItems as $item) {
                while (in_array($nextUrut, $existingNumbers)) {
                    $nextUrut++;
                }
                $existingNumbers[] = $nextUrut;

                $nominalItem = (float) $item['nominal'];
                $nomorBukti = $prefix.str_pad($nextUrut, 3, '0', STR_PAD_LEFT);
                $keterangan = $item['keterangan'];

                JurnalUmum::create([
                    'nomor_bukti' => $nomorBukti,
                    'tanggal' => $this->tanggal,
                    'keterangan' => $keterangan,
                    'kode_akun_id' => (int) $item['kode_akun_id'],
                    'debet' => $nominalItem,
                    'kredit' => 0,
                    'transaksi_harian_id' => $transaksiHarian?->id,
                    'unit_wisata_id' => $this->unitId,
                ]);

                JurnalUmum::create([
                    'nomor_bukti' => $nomorBukti,
                    'tanggal' => $this->tanggal,
                    'keterangan' => $keterangan,
                    'kode_akun_id' => $akunKas->id,
                    'debet' => 0,
                    'kredit' => $nominalItem,
                    'transaksi_harian_id' => $transaksiHarian?->id,
                    'unit_wisata_id' => $this->unitId,
                ]);

                $nextUrut++;
            }

            if ($transaksiHarian) {
                $transaksiHarian->increment('total_pengeluaran', $totalPengeluaranBaru);
            }
        });

        \Flux::toast(variant: 'success', text: 'Pengeluaran unit berhasil dicatat!');

        $this->reset(['items', 'totalPengeluaran']);
        $this->tanggal = Carbon::today()->format('Y-m-d');
        $this->addItem();
        $this->showCreateModal = false;
    }

    public function editRiwayat($nomorBukti): void
    {
        $jurnalDebet = JurnalUmum::where('nomor_bukti', $nomorBukti)
            ->where('unit_wisata_id', $this->unitId)
            ->where('debet', '>', 0)
            ->first();

        if (! $jurnalDebet) {
            return;
        }

        $this->editingNomorBukti = $nomorBukti;
        $this->editTanggal = $jurnalDebet->tanggal
            ? Carbon::parse($jurnalDebet->tanggal)->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');
        $this->editKodeAkunId = (string) $jurnalDebet->kode_akun_id;
        $this->editKeterangan = $jurnalDebet->keterangan;
        $this->editNominal = $jurnalDebet->debet;
        $this->showEditModal = true;
    }

    public function updateRiwayat(): void
    {
        $validated = $this->validate([
            'editTanggal' => 'required|date',
            'editKodeAkunId' => 'required|exists:kode_akun,id',
            'editKeterangan' => 'required|string|max:500',
            'editNominal' => 'required|numeric|min:1|max:9999999999999',
        ]);

        $jurnals = JurnalUmum::where('nomor_bukti', $this->editingNomorBukti)
            ->where('unit_wisata_id', $this->unitId)
            ->get();

        $oldTotal = $jurnals->sum('debet');
        $transaksiHarianId = $jurnals->first()?->transaksi_harian_id;

        DB::transaction(function () use ($jurnals, $oldTotal, $transaksiHarianId, $validated) {
            $akunKas = KodeAkun::where('kode', '1-1100')->firstOrFail();

            foreach ($jurnals as $j) {
                $isDebet = $j->debet > 0;
                $j->update([
                    'tanggal' => $validated['editTanggal'],
                    'keterangan' => $validated['editKeterangan'],
                    'kode_akun_id' => $isDebet ? (int) $validated['editKodeAkunId'] : $akunKas->id,
                    'debet' => $isDebet ? $validated['editNominal'] : 0,
                    'kredit' => $isDebet ? 0 : $validated['editNominal'],
                ]);
            }

            if ($transaksiHarianId) {
                $th = TransaksiHarian::find($transaksiHarianId);
                if ($th) {
                    $th->increment('total_pengeluaran', (float) $validated['editNominal'] - $oldTotal);
                }
            }
        });

        $this->showEditModal = false;
        $this->reset(['editingNomorBukti', 'editKodeAkunId', 'editKeterangan', 'editNominal', 'editTanggal']);

        \Flux::toast(variant: 'success', text: 'Data pengeluaran berhasil diperbarui.');
    }

    public function confirmDelete($nomorBukti): void
    {
        $this->deletingNomorBukti = $nomorBukti;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->deletingNomorBukti) {
            return;
        }

        $this->deleteRiwayat($this->deletingNomorBukti, false);

        $this->showDeleteModal = false;
        $this->deletingNomorBukti = null;

        \Flux::toast(variant: 'success', text: 'Data pengeluaran berhasil dihapus.');
    }

    public function deleteRiwayat($nomorBukti, $showToast = true): void
    {
        $jurnals = JurnalUmum::where('nomor_bukti', $nomorBukti)->where('unit_wisata_id', $this->unitId)->get();
        if ($jurnals->isEmpty()) {
            return;
        }

        $totalDebet = $jurnals->sum('debet');
        $transaksiHarianId = $jurnals->first()->transaksi_harian_id;

        DB::transaction(function () use ($jurnals, $totalDebet, $transaksiHarianId) {
            foreach ($jurnals as $j) {
                $j->delete();
            }

            if ($transaksiHarianId) {
                $th = TransaksiHarian::find($transaksiHarianId);
                if ($th) {
                    $th->decrement('total_pengeluaran', $totalDebet);
                }
            }
        });

        if ($showToast) {
            \Flux::toast(variant: 'success', text: 'Data pengeluaran berhasil dihapus.');
        }
    }

    public function updatedFilterMode(): void
    {
        $today = Carbon::today();
        match ($this->filterMode) {
            'harian' => $this->filterDate = $this->filterDate ?: $today->format('Y-m-d'),
            'bulanan' => $this->filterBulan = $this->filterBulan ?: $today->format('Y-m'),
            'semester' => [
                $this->filterSemester = $this->filterSemester ?: '1',
                $this->filterSemesterTahun = $this->filterSemesterTahun ?: $today->format('Y'),
            ],
            'tahunan' => $this->filterTahun = $this->filterTahun ?: $today->format('Y'),
            default => $this->filterDate = $this->filterDate ?: $today->format('Y-m-d'),
        };
    }

    #[Computed]
    public function riwayat()
    {
        $query = JurnalUmum::with('kodeAkun')
            ->where('unit_wisata_id', $this->unitId)
            ->where('kredit', 0)
            ->where('nomor_bukti', 'like', 'K%');

        match ($this->filterMode) {
            'harian' => $query->whereDate('tanggal', $this->filterDate ?: Carbon::today()->format('Y-m-d')),
            'bulanan' => $query->whereBetween('tanggal', [
                Carbon::parse(($this->filterBulan ?: date('Y-m')).'-01')->startOfMonth()->format('Y-m-d'),
                Carbon::parse(($this->filterBulan ?: date('Y-m')).'-01')->endOfMonth()->format('Y-m-d'),
            ]),
            'semester' => $query->whereBetween('tanggal', [
                Carbon::create((int) ($this->filterSemesterTahun ?: date('Y')), $this->filterSemester === '1' ? 1 : 7, 1)->startOfMonth()->format('Y-m-d'),
                Carbon::create((int) ($this->filterSemesterTahun ?: date('Y')), $this->filterSemester === '1' ? 6 : 12, 1)->endOfMonth()->format('Y-m-d'),
            ]),
            'tahunan' => $query->whereBetween('tanggal', [
                Carbon::create((int) ($this->filterTahun ?: date('Y')), 1, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create((int) ($this->filterTahun ?: date('Y')), 12, 31)->endOfDay()->format('Y-m-d'),
            ]),
            default => null,
        };

        $query->orderByRaw('LENGTH(nomor_bukti) ASC')
            ->orderByRaw('nomor_bukti ASC');

        return $query->limit(50)->get();
    }

    #[Computed]
    public function riwayatTotal(): float
    {
        return $this->riwayat->sum('debet');
    }

    public function render()
    {
        $akunBiaya = KodeAkun::where('tipe', 'beban')->orderBy('kode')->get();

        return view('livewire.kepala-unit.catat-pengeluaran', [
            'akunBiaya' => $akunBiaya,
            'riwayat' => $this->riwayat,
            'riwayatTotal' => $this->riwayatTotal,
        ]);
    }
}
