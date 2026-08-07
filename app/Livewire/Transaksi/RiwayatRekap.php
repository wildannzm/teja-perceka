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
    public string $tab = 'pendapatan'; // pendapatan, jurnal

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
    public string $tahun = '';

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        // Default all modes to current period
        $now = Carbon::now();
        $this->tanggal = $now->format('Y-m-d');
        $this->minggu  = $now->startOfWeek()->format('Y-m-d');
        $this->bulan   = Carbon::now()->format('Y-m');
        $this->tahun   = Carbon::now()->format('Y');

        if (!in_array($this->tab, ['pendapatan', 'jurnal'])) {
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
        return $this->unit_id ? UnitWisata::find($this->unit_id) : null;
    }

    #[Computed]
    public function isHarianDisabled(): bool
    {
        return $this->isUnitMingguan($this->selectedUnit);
    }

    public function updatedMode(): void
    {
        $unit = $this->selectedUnit;
        if ($this->mode === 'harian' && $this->isUnitMingguan($unit)) {
            $this->mode = 'mingguan';
        }
    }

    public function updatedUnitId(): void
    {
        $unit = UnitWisata::find($this->unit_id);
        if ($this->mode === 'harian' && $this->isUnitMingguan($unit)) {
            $this->mode = 'mingguan';
        }
    }

    public function updatedTab(): void
    {
        if (!in_array($this->tab, ['pendapatan', 'jurnal'])) {
            $this->tab = 'pendapatan';
        }
    }

    public function render()
    {
        return view('livewire.transaksi.riwayat-rekap');
    }
}
