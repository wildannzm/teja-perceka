<?php

namespace App\Livewire\KepalaUnit;

use App\Models\JurnalUmum;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use App\Traits\DashboardChartData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Kepala Unit')]
class Dashboard extends Component
{
    use DashboardChartData;

    #[Locked]
    public ?int $unitId = null;

    public ?UnitWisata $unit = null;

    public bool $isPeriodeBerjalanSudahDiisi = false;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unit = UnitWisata::findOrFail($this->unitId);

        $today = Carbon::today();
        $isMingguan = $this->unit->frekuensi_input === 'mingguan';
        
        if ($isMingguan) {
            $this->isPeriodeBerjalanSudahDiisi = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->where('tanggal', $today->copy()->startOfWeek()->format('Y-m-d'))
                ->where('tanggal_akhir', $today->copy()->endOfWeek()->format('Y-m-d'))
                ->exists();
        } else {
            $this->isPeriodeBerjalanSudahDiisi = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->whereDate('tanggal', $today->format('Y-m-d'))
                ->exists();
        }
    }

    public function getPemasukanHariIniProperty(): float
    {
        return TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->whereDate('tanggal', Carbon::today())
            ->sum('total_pemasukan');
    }

    public function getPemasukanMingguIniProperty(): float
    {
        return TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->whereBetween('tanggal', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->sum('total_pemasukan');
    }

    public function getPemasukanBulanIniProperty(): float
    {
        return TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total_pemasukan');
    }

    public function getPemasukanTahunIniProperty(): float
    {
        return TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('total_pemasukan');
    }

    public function getTransaksiTerakhirProperty()
    {
        return TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();
    }

    public function getTotalTransaksiBulanIniProperty(): int
    {
        return TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->count();
    }

    public function render()
    {
        $chartData = $this->getChartData($this->unitId);

        return view('livewire.kepala-unit.dashboard', [
            'chartPemasukan' => $chartData['pemasukan'],
            'chartPengeluaran' => $chartData['pengeluaran'],
        ]);
    }
}
