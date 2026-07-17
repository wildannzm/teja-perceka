<?php

namespace App\Livewire\Sekretaris;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $pemasukan = \App\Models\JournalDetail::whereHas('account', function($q) {
            $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
        })->sum('credit') - \App\Models\JournalDetail::whereHas('account', function($q) {
            $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
        })->sum('debit');

        $pengeluaran = \App\Models\JournalDetail::whereHas('account', function($q) {
            $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
        })->sum('debit') - \App\Models\JournalDetail::whereHas('account', function($q) {
            $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
        })->sum('credit');

        $saldo = \App\Models\JournalDetail::whereHas('account', function($q) {
            $q->whereIn('code', ['1-1100', '1-1200']);
        })->sum('debit') - \App\Models\JournalDetail::whereHas('account', function($q) {
            $q->whereIn('code', ['1-1100', '1-1200']);
        })->sum('credit');

        return view('livewire.sekretaris.dashboard', [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
        ])->layout('layouts.app', ['title' => 'Dashboard Sekretaris']);
    }
}
