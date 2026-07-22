<?php

namespace App\Livewire\Pengawas;

use App\Models\JurnalUmum;
use App\Models\UnitWisata;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Cetak Laporan Pengawas')]
class Report extends Component
{
    use \App\Traits\ExportsJurnalPdf;

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

        return view('livewire.pengawas.report', [
            'journals' => $journals,
            'units' => $units,
        ]);
    }
}

