<?php

namespace App\Livewire\KepalaDesa;

use Livewire\Component;
use App\Models\Journal;
use App\Models\Unit;

class Report extends Component
{
    public $unit_id = '';
    public $start_date = '';
    public $end_date = '';

    public function render()
    {
        $query = Journal::with(['unit', 'details.account'])->orderBy('date', 'desc');

        if ($this->unit_id) {
            $query->where('unit_id', $this->unit_id);
        }

        if ($this->start_date) {
            $query->whereDate('date', '>=', $this->start_date);
        }

        if ($this->end_date) {
            $query->whereDate('date', '<=', $this->end_date);
        }

        $journals = $query->get();
        $units = Unit::all();

        return view('livewire.kepala-desa.report', [
            'journals' => $journals,
            'units' => $units
        ])->layout('layouts.app', ['title' => 'Cetak Laporan Kepala Desa']);
    }
}
