<?php

namespace App\Livewire\Transaksi;

use App\Models\KategoriTransaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
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
                if ($isTps) return null;
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));
                return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

            case 'mingguan':
                $weekStart = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd   = $weekStart->copy()->endOfWeek();
                return [$weekStart, $weekEnd];

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')) . '-01');
                return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];

            case 'tahunan':
                $year = (int)($this->tahun ?: Carbon::now()->format('Y'));
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

        if ($this->unitId) {
            $q->where('unit_wisata_id', $this->unitId);
        }

        if ($this->mode === 'mingguan' && $this->isUnitMingguan($unit) && $this->unitId) {
            $q->where('tanggal', $start->format('Y-m-d'))
              ->where('tanggal_akhir', $end->format('Y-m-d'));
        } else {
            $q->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        }

        return $q;
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return $this->unitId ? UnitWisata::find($this->unitId) : null;
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
                $weekEnd   = $weekStart->copy()->endOfWeek();
                return $weekStart->translatedFormat('d F Y') . ' – ' . $weekEnd->translatedFormat('d F Y');

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')) . '-01');
                return $date->translatedFormat('F Y');

            case 'tahunan':
                return 'Tahun ' . ($this->tahun ?: Carbon::now()->format('Y'));

            default:
                return '-';
        }
    }

    #[Computed]
    public function reportData(): array
    {
        $unit      = $this->unitId ? UnitWisata::find($this->unitId) : null;
        $range     = $this->periodeRange($unit);
        $namaUnit  = $unit ? $unit->nama : 'Semua Unit (Konsolidasi)';

        if ($range === null) {
            return [
                'unit'            => $namaUnit,
                'kategoriRows'    => collect([]),
                'totalPendapatan' => 0,
                'kosong'          => true,
                'pesanKosong'     => 'Mode Harian tidak tersedia untuk unit TPS karena data diinput per minggu. Silakan pilih mode Mingguan atau Bulanan.',
            ];
        }

        $transaksiIds = $this->buildTransaksiHarianQuery($unit, $range)->pluck('id');

        if ($transaksiIds->isEmpty()) {
            return [
                'unit'            => $namaUnit,
                'kategoriRows'    => collect([]),
                'totalPendapatan' => 0,
                'kosong'          => true,
                'pesanKosong'     => 'Tidak ada data pemasukan pada periode ini.',
            ];
        }

        $aggrRows = TransaksiDetail::whereIn('transaksi_harian_id', $transaksiIds)
            ->select(
                'kategori_transaksi_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('MAX(harga_satuan) as harga_satuan'),
                DB::raw('SUM(subtotal) as total_subtotal')
            )
            ->groupBy('kategori_transaksi_id')
            ->with('kategoriTransaksi.unitWisata')
            ->get();

        $kategoriRows = $aggrRows->map(function ($row) {
            $kat = $row->kategoriTransaksi;
            return [
                'kategori_id'  => $kat->id,
                'kategori'     => $kat->nama,
                'unit_nama'    => $kat->unitWisata?->nama ?? '-',
                'tipe'         => $kat->tipe->value,
                'harga_satuan' => $kat->tipe->value === 'harga_x_qty' ? (float) $row->harga_satuan : null,
                'jumlah_qty'   => $kat->tipe->value === 'harga_x_qty' ? (int) $row->total_qty : null,
                'subtotal'     => (float) $row->total_subtotal,
            ];
        })->sortBy('kategori')->values();

        $totalPendapatan = $kategoriRows->sum('subtotal');

        return [
            'unit'            => $namaUnit,
            'kategoriRows'    => $kategoriRows,
            'totalPendapatan' => $totalPendapatan,
            'kosong'          => $kategoriRows->isEmpty(),
            'pesanKosong'     => 'Tidak ada data pemasukan pada periode ini.',
        ];
    }

    public function render()
    {
        return view('livewire.transaksi.tab-pendapatan');
    }
}
