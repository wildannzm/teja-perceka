<?php

namespace App\Livewire\Transaksi;

use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TabPendapatan extends Component
{
    #[Reactive]
    public $unitId = null;

    #[Reactive]
    public string $mode = 'harian';

    #[Reactive]
    public string $tanggal = '';

    #[Reactive]
    public string $minggu = '';

    #[Reactive]
    public string $bulan = '';

    #[Reactive]
    public string $semester = '1';

    #[Reactive]
    public string $semesterTahun = '';

    #[Reactive]
    public string $tahun = '';

    private function isUnitMingguan(?UnitWisata $unit): bool
    {
        return $unit && $unit->frekuensi_input === 'mingguan';
    }

    private function periodeRange(?UnitWisata $unit): ?array
    {
        $isTps = $this->isUnitMingguan($unit);

        switch ($this->mode) {
            case 'harian':
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));

                return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

            case 'mingguan':
                $weekStart = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd = $weekStart->copy()->endOfWeek();

                return [$weekStart, $weekEnd];

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')).'-01');

                return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];

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

    private function buildTransaksiHarianQuery(?UnitWisata $unit, ?array $range)
    {
        if ($range === null) {
            return TransaksiHarian::query()->whereRaw('1 = 0');
        }

        [$start, $end] = $range;
        $q = TransaksiHarian::query();

        if ($this->unitId && is_numeric($this->unitId)) {
            $q->where('unit_wisata_id', (int) $this->unitId);
        }

        $q->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);

        return $q;
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return ($this->unitId && is_numeric($this->unitId)) ? UnitWisata::find($this->unitId) : null;
    }

    #[Computed]
    public function isKepalaUnit(): bool
    {
        return Auth::user()->hasRole('kepala_unit');
    }

    #[Computed]
    public function periodeLabel(): string
    {
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
        $unit = ($this->unitId && is_numeric($this->unitId)) ? UnitWisata::find($this->unitId) : null;
        $range = $this->periodeRange($unit);
        $namaUnit = $unit ? $unit->nama : ($this->unitId === 'bumdes' ? 'BUMDes' : 'Semua Unit Usaha');

        if ($range === null) {
            return [
                'unit' => $namaUnit,
                'kategoriRows' => collect([]),
                'totalPendapatan' => 0,
                'totalPengeluaranUnit' => 0,
                'pendapatanBersih' => 0,
                'kosong' => true,
                'pesanKosong' => 'Mode Harian tidak tersedia untuk unit TPS karena data diinput per minggu. Silakan pilih mode Mingguan atau Bulanan.',
            ];
        }

        $transaksiQuery = $this->buildTransaksiHarianQuery($unit, $range);
        $totalPengeluaranUnit = (float) (clone $transaksiQuery)->sum('total_pengeluaran');
        $transaksiIds = $transaksiQuery->pluck('id');

        if ($transaksiIds->isEmpty()) {
            return [
                'unit' => $namaUnit,
                'kategoriRows' => collect([]),
                'totalPendapatan' => 0,
                'totalPengeluaranUnit' => 0,
                'pendapatanBersih' => 0,
                'kosong' => true,
                'pesanKosong' => 'Tidak ada data pemasukan pada periode ini.',
            ];
        }

        $aggrRows = TransaksiDetail::whereIn('transaksi_harian_id', $transaksiIds)
            ->select(
                'kategori_transaksi_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('MAX(harga_satuan) as harga_satuan'),
                DB::raw('SUM(subtotal) as total_subtotal'),
                DB::raw('MIN(transaksi_harian_id) as transaksi_harian_id')
            )
            ->groupBy('kategori_transaksi_id')
            ->with('kategoriTransaksi.unitWisata')
            ->get();

        $kategoriRows = $aggrRows->map(function ($row) {
            $kat = $row->kategoriTransaksi;

            return [
                'kategori_id' => $kat->id,
                'kategori' => $kat->nama,
                'unit_nama' => $kat->unitWisata?->nama ?? '-',
                'tipe' => $kat->tipe->value,
                'harga_satuan' => $kat->tipe->value === 'harga_x_qty' ? (float) $row->harga_satuan : null,
                'jumlah_qty' => $kat->tipe->value === 'harga_x_qty' ? (int) $row->total_qty : null,
                'subtotal' => (float) $row->total_subtotal,
                'transaksi_harian_id' => (int) $row->transaksi_harian_id,
            ];
        })->sortBy('kategori')->values();

        $totalPendapatan = $kategoriRows->sum('subtotal');
        $transaksiHarianId = $this->mode === 'harian' ? $kategoriRows->first()['transaksi_harian_id'] ?? null : null;

        return [
            'unit' => $namaUnit,
            'kategoriRows' => $kategoriRows,
            'totalPendapatan' => $totalPendapatan,
            'totalPengeluaranUnit' => $totalPengeluaranUnit,
            'pendapatanBersih' => $totalPendapatan - $totalPengeluaranUnit,
            'kosong' => $kategoriRows->isEmpty(),
            'pesanKosong' => 'Tidak ada data pemasukan pada periode ini.',
            'transaksiHarianId' => $transaksiHarianId,
        ];
    }

    public function render()
    {
        return view('livewire.transaksi.tab-pendapatan');
    }
}
