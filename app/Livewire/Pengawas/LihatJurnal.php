<?php

namespace App\Livewire\Pengawas;

use App\Models\JurnalUmum;
use App\Models\UnitWisata;
use App\Traits\ExportsJurnalPdf;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Lihat Jurnal Transaksi')]
class LihatJurnal extends Component
{
    use ExportsJurnalPdf;

    public $unit_id = '';

    public $month = '';

    public function mount()
    {
        $this->month = Carbon::now()->format('Y-m');
    }

    public function render()
    {
        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('nomor_bukti', 'desc')
            ->orderBy('id', 'asc');

        if ($this->unit_id) {
            $query->where('unit_wisata_id', $this->unit_id);
        }

        if ($this->month) {
            $date = Carbon::createFromFormat('Y-m', $this->month);
            $query->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year);
        }

        $journals = $query->get()->groupBy('nomor_bukti');
        $units = UnitWisata::all();

        return view('livewire.pengawas.lihat-jurnal', [
            'journals' => $journals,
            'units' => $units,
        ]);
    }
}
