<?php

namespace App\Livewire\Sekretaris;

use App\Models\JurnalUmum;
use App\Traits\DashboardChartData;
use Livewire\Component;

class Dashboard extends Component
{
    use DashboardChartData;

    public function render()
    {
        // Income accounts: 4- and 7- prefixes (credit column)
        $pemasukan = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->where('kode', 'like', '4-%')->orWhere('kode', 'like', '7-%');
        })->sum('kredit');

        // Expense accounts: 5- and 6- prefixes (debit column)
        $pengeluaran = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->where('kode', 'like', '5-%')->orWhere('kode', 'like', '6-%');
        })->sum('debet');

        // Cash balance: debit - credit on Cash (1-1100) and Bank (1-1200)
        $kasDebet = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->whereIn('kode', ['1-1100', '1-1200']);
        })->sum('debet');

        $kasKredit = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->whereIn('kode', ['1-1100', '1-1200']);
        })->sum('kredit');

        $saldo = $kasDebet - $kasKredit;
        $chartData = $this->getChartData();

        return view('livewire.sekretaris.dashboard', [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
            'chartPemasukan' => $chartData['pemasukan'],
            'chartPengeluaran' => $chartData['pengeluaran'],
        ])->layout('layouts.app', ['title' => 'Dashboard Sekretaris']);
    }
}
