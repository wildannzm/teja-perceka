<?php

namespace App\Livewire\Bendahara;

use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransactionList extends Component
{
    public function delete($id)
    {
        $jurnal = JurnalUmum::find($id);
        if ($jurnal) {
            DB::transaction(function () use ($jurnal) {
                JurnalUmum::where('nomor_bukti', $jurnal->nomor_bukti)->delete();
            });
            \Flux::toast(variant: 'success', text: 'Satu set jurnal (debet & kredit) berhasil dihapus.');
        }
    }

    public function render()
    {
        $journals = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('nomor_bukti', 'desc')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('nomor_bukti');

        return view('livewire.bendahara.transaction-list', [
            'journals' => $journals,
        ])->layout('layouts.app', ['title' => 'Kelola Jurnal']);
    }
}
