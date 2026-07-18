<?php

namespace App\Livewire\Bendahara;

use App\Models\JurnalUmum;
use App\Models\UnitWisata;
use Livewire\Component;

class Report extends Component
{
    public $unit_id = '';

    public $start_date = '';

    public $end_date = '';

    public function render()
    {
        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('nomor_bukti', 'desc')
            ->orderBy('id', 'asc');

        if ($this->unit_id) {
            $query->where('unit_wisata_id', $this->unit_id);
        }

        if ($this->start_date) {
            $query->whereDate('tanggal', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->whereDate('tanggal', '<=', $this->end_date);
        }

        $journals = $query->get()->groupBy('nomor_bukti');
        $units = UnitWisata::all();

        return view('livewire.bendahara.report', [
            'journals' => $journals,
            'units' => $units,
        ])->layout('layouts.app', ['title' => 'Cetak Laporan Bendahara']);
    }
}
