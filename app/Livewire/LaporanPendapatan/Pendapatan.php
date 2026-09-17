<?php

namespace App\Livewire\LaporanPendapatan;

use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pendapatan')]
class Pendapatan extends Component
{
    public $unit_id = null;

    /** 'harian' | 'mingguan' | 'bulanan' | 'semester' | 'tahunan' */
    public string $mode = 'harian';

    /** Daily mode: specific date, format Y-m-d */
    public string $tanggal = '';

    /** Weekly mode: Monday of the selected ISO week, format Y-m-d */
    public string $minggu = '';

    /** Monthly mode: format Y-m */
    public string $bulan = '';

    /** Semester mode */
    public string $semester = '1';

    public string $semesterTahun = '';

    /** Annual mode: format Y */
    public string $tahun = '';

    public function mount(): void
    {
        $user = Auth::user();

        // Unit head: lock to assigned unit
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        // Default all modes to current period
        $now = Carbon::now();
        $this->tanggal = $now->format('Y-m-d');
        $this->minggu = $now->startOfWeek()->format('Y-m-d');
        $this->bulan = $now->format('Y-m');
        $this->semesterTahun = $now->format('Y');
        $this->semester = $now->month <= 6 ? '1' : '2';
        $this->tahun = $now->format('Y');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Check if currently selected unit is TPS (weekly input frequency).
     */
    private function isUnitMingguan(?UnitWisata $unit): bool
    {
        return $unit && $unit->frekuensi_input === 'mingguan';
    }

    /**
     * Return [start, end] Carbon range based on mode and active period values.
     * Returns null for weekly TPS units under daily mode (not applicable).
     */
    private function periodeRange(?UnitWisata $unit): ?array
    {
        $isTps = $this->isUnitMingguan($unit);

        switch ($this->mode) {
            case 'harian':
                // Daily mode does not apply to weekly units (TPS)
                if ($isTps) {
                    return null;
                }
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));

                return [$date->startOfDay(), $date->copy()->endOfDay()];

            case 'mingguan':
                // For TPS: match exact single record with tanggal = startOfWeek (ISO)
                // For daily units: WHERE tanggal BETWEEN startOfWeek AND endOfWeek
                $weekStart = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd = $weekStart->copy()->endOfWeek();

                return [$weekStart, $weekEnd];

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')).'-01');

                return [$date->startOfMonth(), $date->copy()->endOfMonth()];

            case 'semester':
                $year = (int) ($this->semesterTahun ?: Carbon::now()->format('Y'));
                if ($this->semester === '1') {
                    return [
                        Carbon::create($year, 1, 1)->startOfDay(),
                        Carbon::create($year, 6, 30)->endOfDay(),
                    ];
                } else {
                    return [
                        Carbon::create($year, 7, 1)->startOfDay(),
                        Carbon::create($year, 12, 31)->endOfDay(),
                    ];
                }

            case 'tahunan':
                $year = (int) ($this->tahun ?: Carbon::now()->format('Y'));

                return [
                    Carbon::create($year, 1, 1)->startOfDay(),
                    Carbon::create($year, 12, 31)->endOfDay(),
                ];

            default:
                return null;
        }
    }

    /**
     * Build TransaksiHarian query based on mode, range, and unit.
     * TPS (weekly): query based on exact date (tanggal = start of week).
     * Daily units: query based on date range.
     */
    private function buildTransaksiHarianQuery(?UnitWisata $unit, ?array $range)
    {
        if ($range === null) {
            // No relevant data (e.g. daily mode for weekly units)
            return TransaksiHarian::query()->whereRaw('1 = 0');
        }

        [$start, $end] = $range;
        $q = TransaksiHarian::query();

        // Unit filter
        if ($this->unit_id) {
            $q->where('unit_wisata_id', $this->unit_id);
        }

        // TPS (weekly): find exact record whose period matches current week
        // Column tanggal = week start, tanggal_akhir = week end
        if ($this->mode === 'mingguan' && $this->isUnitMingguan($unit) && $this->unit_id) {
            $q->where('tanggal', $start->format('Y-m-d'))
                ->where('tanggal_akhir', $end->format('Y-m-d'));
        } else {
            // Daily unit: WHERE tanggal BETWEEN start AND end
            // For consolidated view, include all units including TPS by date
            $q->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        }

        return $q;
    }

    // ─── Computed ────────────────────────────────────────────────────────

    #[Computed]
    public function units(): Collection
    {
        return UnitWisata::orderBy('nama')->get();
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return $this->unit_id ? UnitWisata::find($this->unit_id) : null;
    }

    /**
     * Is daily mode active while selected unit is weekly?
     * Used to hide/disable daily mode picker in weekly views.
     */
    #[Computed]
    public function isHarianDisabled(): bool
    {
        // Daily mode is disabled/hidden when selected unit is weekly (TPS)
        return $this->isUnitMingguan($this->selectedUnit);
    }

    #[Computed]
    public function availableTpsWeeks(): Collection
    {
        $unit = $this->selectedUnit;
        if (! $this->isUnitMingguan($unit)) {
            return collect();
        }

        return TransaksiHarian::where('unit_wisata_id', $this->unit_id)
            ->select('tanggal', 'tanggal_akhir')
            ->distinct()
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    #[Computed]
    public function isKepalaUnit(): bool
    {
        return Auth::user()->hasRole('kepala_unit');
    }

    #[Computed]
    public function periodeLabel(): string
    {
        $unit = $this->selectedUnit;

        switch ($this->mode) {
            case 'harian':
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));

                return $date->translatedFormat('d F Y');

            case 'mingguan':
                $weekStart = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd = $weekStart->copy()->endOfWeek();

                return $weekStart->translatedFormat('d F Y').' – '.$weekEnd->translatedFormat('d F Y');

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')).'-01');

                return $date->translatedFormat('F Y');

            case 'semester':
                $year = $this->semesterTahun ?: Carbon::now()->format('Y');

                return 'Semester '.$this->semester.' Tahun '.$year;

            case 'tahunan':
                return 'Tahun '.($this->tahun ?: Carbon::now()->format('Y'));

            default:
                return '-';
        }
    }

    #[Computed]
    public function reportData(): array
    {
        // Fresh lookup – do not rely on cached $this->selectedUnit
        $unit = $this->unit_id ? UnitWisata::find($this->unit_id) : null;
        $range = $this->periodeRange($unit);

        $namaUnit = $unit ? $unit->nama : 'Semua Unit (Konsolidasi)';

        // Return empty if daily mode is selected for weekly unit (TPS)
        if ($range === null) {
            return [
                'unit' => $namaUnit,
                'kategoriRows' => collect([]),
                'totalPendapatan' => 0,
                'kosong' => true,
                'pesanKosong' => 'Mode Harian tidak tersedia untuk unit TPS karena data diinput per minggu. Silakan pilih mode Mingguan atau Bulanan.',
            ];
        }

        // Fetch matching TransaksiHarian IDs
        $transaksiIds = $this->buildTransaksiHarianQuery($unit, $range)->pluck('id');

        if ($transaksiIds->isEmpty()) {
            return [
                'unit' => $namaUnit,
                'kategoriRows' => collect([]),
                'totalPendapatan' => 0,
                'kosong' => true,
                'pesanKosong' => 'Tidak ada data pemasukan pada periode ini.',
            ];
        }

        // Aggregate details per category — single query
        $aggrRows = TransaksiDetail::whereIn('transaksi_harian_id', $transaksiIds)
            ->select(
                'kategori_transaksi_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('MAX(harga_satuan) as harga_satuan'),   // unit price is constant over the period (MAX is enough)
                DB::raw('SUM(subtotal) as total_subtotal')
            )
            ->groupBy('kategori_transaksi_id')
            ->with('kategoriTransaksi.unitWisata')
            ->get();

        // Shape kategoriRows into the expected format
        $kategoriRows = $aggrRows->map(function ($row) {
            $kat = $row->kategoriTransaksi;

            return [
                'kategori_id' => $kat->id,
                'kategori' => $kat->nama,
                'unit_nama' => $kat->unitWisata?->nama ?? '-',
                'tipe' => $kat->tipe->value,  // harga_x_qty | flat | bebas | tahunan
                'harga_satuan' => $kat->tipe->value === 'harga_x_qty' ? (float) $row->harga_satuan : null,
                'jumlah_qty' => $kat->tipe->value === 'harga_x_qty' ? (int) $row->total_qty : null,
                'subtotal' => (float) $row->total_subtotal,
            ];
        })->sortBy('kategori')->values();

        $totalPendapatan = $kategoriRows->sum('subtotal');

        return [
            'unit' => $namaUnit,
            'kategoriRows' => $kategoriRows,
            'totalPendapatan' => $totalPendapatan,
            'kosong' => $kategoriRows->isEmpty(),
            'pesanKosong' => 'Tidak ada data pemasukan pada periode ini.',
        ];
    }

    public function updatedMode(): void
    {
        // When the daily mode is picked for a weekly unit, switch to weekly automatically
        $unit = $this->selectedUnit;
        if ($this->mode === 'harian' && $this->isUnitMingguan($unit)) {
            $this->mode = 'mingguan';
        }
    }

    public function updatedUnitId(): void
    {
        // When switching to TPS while still in daily mode → move to weekly automatically
        $unit = UnitWisata::find($this->unit_id);
        if ($this->mode === 'harian' && $this->isUnitMingguan($unit)) {
            $this->mode = 'mingguan';
        }
    }

    public function render()
    {
        return view('livewire.laporan-pendapatan.pendapatan');
    }
}
