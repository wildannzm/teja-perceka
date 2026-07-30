<?php

namespace App\Livewire\Bendahara;

use App\Models\JurnalUmum;
use App\Traits\DashboardChartData;
use Livewire\Component;

class Dashboard extends Component
{
    use DashboardChartData;

    public function render()
    {
        // Akun pendapatan: prefix 4- (menggunakan kolom kredit)
        $pemasukan = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->where('kode', 'like', '4-%')->orWhere('kode', 'like', '7-%');
        })->sum('kredit');

        // Akun beban/biaya: prefix 5- dan 6- (menggunakan kolom debet)
        $pengeluaran = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->where('kode', 'like', '5-%')->orWhere('kode', 'like', '6-%');
        })->sum('debet');

        // Saldo Kas: debet - kredit pada akun Kas (1-1100) dan Bank (1-1200)
        $kasDebet = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->whereIn('kode', ['1-1100', '1-1200']);
        })->sum('debet');

        $kasKredit = JurnalUmum::whereHas('kodeAkun', function ($q) {
            $q->whereIn('kode', ['1-1100', '1-1200']);
        })->sum('kredit');

        $saldo = $kasDebet - $kasKredit;
        $chartData = $this->getChartData();

        return view('livewire.bendahara.dashboard', [
            'pemasukan' => $pemasukan,
            'pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
            'chartPemasukan' => $chartData['pemasukan'],
            'chartPengeluaran' => $chartData['pengeluaran'],
        ])->layout('layouts.app', ['title' => 'Dashboard Bendahara']);
    }
}
