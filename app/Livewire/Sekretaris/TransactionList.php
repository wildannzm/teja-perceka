<?php

namespace App\Livewire\Sekretaris;

use Livewire\Component;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;

class TransactionList extends Component
{
    public function delete($id)
    {
        $journal = Journal::find($id);
        if ($journal) {
            DB::transaction(function () use ($journal) {
                // Cascading delete will handle journal_details
                $journal->delete();
            });
            \Flux::toast(variant: 'success', text: 'Jurnal berhasil dihapus.');
        }
    }

    public function render()
    {
        $journals = Journal::with(['unit', 'details.account'])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('livewire.sekretaris.transaction-list', [
            'journals' => $journals,
        ])->layout('layouts.app', ['title' => 'Kelola Jurnal']);
    }
}
