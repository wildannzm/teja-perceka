<?php

namespace App\Livewire\Transaksi;

use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Riwayat & Rekap')]
class RiwayatRekap extends Component
{
    #[Url]
    public string $tab = 'pendapatan'; // 'pendapatan' or 'jurnal'

    public $unit_id = null;

    #[Url(as: 'periode')]
    public string $mode = 'harian';

    #[Url]
    public string $tanggal = '';

    #[Url]
    public string $minggu = '';

    #[Url]
    public string $bulan = '';

    #[Url]
    public string $semester = '1';

    #[Url]
    public string $semesterTahun = '';

    #[Url]
    public string $tahun = '';

    public string $sortField = 'tanggal';

    public string $sortDirection = 'asc';

    public string $sortOption = 'tanggal-asc';

    /** Journal tab view: 'summary' (aggregated) or 'detailed' (per voucher). */
    public string $viewMode = 'summary';

    public function updatedSortOption(): void
    {
        [$field, $direction] = array_pad(explode('-', $this->sortOption, 2), 2, 'asc');

        if (! in_array($field, ['tanggal', 'nomor_bukti'])) {
            $field = 'tanggal';
        }

        $this->sortField = $field;
        $this->sortDirection = $direction === 'desc' ? 'desc' : 'asc';
        $this->sortOption = $field.'-'.$this->sortDirection;
    }

    public function sortJurnalBy(string $field): void
    {
        if (! in_array($field, ['tanggal', 'nomor_bukti'])) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->sortOption = $this->sortField.'-'.$this->sortDirection;
    }

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        } elseif (empty($this->unit_id)) {
            $this->unit_id = 'bumdes';
        }

        // Default all modes to current period
        $now = Carbon::now();
        $this->tanggal = $now->format('Y-m-d');
        $this->minggu = $now->startOfWeek()->format('Y-m-d');
        $this->bulan = $now->format('Y-m');
        $this->semesterTahun = $now->format('Y');
        $this->semester = $now->month <= 6 ? '1' : '2';
        $this->tahun = $now->format('Y');

        if (! in_array($this->tab, ['pendapatan', 'jurnal'])) {
            $this->tab = 'pendapatan';
        }
    }

    private function isUnitMingguan(?UnitWisata $unit): bool
    {
        return $unit && $unit->frekuensi_input === 'mingguan';
    }

    #[Computed]
    public function units(): Collection
    {
        return UnitWisata::orderBy('nama')->get();
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return ($this->unit_id && is_numeric($this->unit_id)) ? UnitWisata::find($this->unit_id) : null;
    }

    #[Computed]
    public function isHarianDisabled(): bool
    {
        return false;
    }

    public function updatedMode(): void
    {
        // No overrides needed
    }

    public function updatingUnitId($value): void
    {
        $user = Auth::user();
        if ($user && $user->hasRole('kepala_unit') && $value !== $user->unit_wisata_id) {
            abort(403, 'Unauthorized');
        }
    }

    public function updatedUnitId(): void
    {
        // No overrides needed
    }

    public function updatedTab(): void
    {
        if (! in_array($this->tab, ['pendapatan', 'jurnal'])) {
            $this->tab = 'pendapatan';
        }
    }

    public function render()
    {
        // Security Fallback: Ensure kepala_unit cannot manipulate unit_id state via browser
        $user = Auth::user();
        if ($user && $user->hasRole('kepala_unit') && $this->unit_id !== $user->unit_wisata_id) {
            $this->unit_id = $user->unit_wisata_id;
        }

        return view('livewire.transaksi.riwayat-rekap');
    }
}
