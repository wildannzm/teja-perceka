<?php

namespace App\Traits;

use App\Models\JurnalUmum;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;

trait ExportsJurnalPdf
{
    public function exportPdf()
    {
        if (! class_exists(Pdf::class)) {
            $this->addError('pdf', 'Package PDF belum terinstall.');
            return;
        }

        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->orderBy('tanggal', 'asc')
            ->orderBy('nomor_bukti', 'asc')
            ->orderBy('id', 'asc');

        if (property_exists($this, 'unit_id') && $this->unit_id) {
            $query->where('unit_wisata_id', $this->unit_id);
        }

        if (property_exists($this, 'start_date') && $this->start_date) {
            $query->whereDate('tanggal', '>=', $this->start_date);
        }

        if (property_exists($this, 'end_date') && $this->end_date) {
            $query->whereDate('tanggal', '<=', $this->end_date);
        }

        if (property_exists($this, 'month') && $this->month) {
            $query->whereMonth('tanggal', substr($this->month, 5, 2))
                  ->whereYear('tanggal', substr($this->month, 0, 4));
        }

        $transactions = $query->get();
        $totalDebet = $transactions->sum('debet');
        $totalKredit = $transactions->sum('kredit');
        $unit = (property_exists($this, 'unit_id') && $this->unit_id) ? UnitWisata::find($this->unit_id) : null;
        
        $periode = 'Laporan Jurnal Umum';
        if (property_exists($this, 'start_date') && $this->start_date && property_exists($this, 'end_date') && $this->end_date) {
            $periode = $this->start_date . ' s/d ' . $this->end_date;
        } elseif (property_exists($this, 'month') && $this->month) {
            $periode = 'Bulan: ' . $this->month;
        }

        $pdf = Pdf::loadView('pdf.riwayat-transaksi', compact(
            'transactions',
            'periode',
            'unit',
            'totalDebet',
            'totalKredit'
        ))->setPaper('a4', 'landscape');

        $unitName = $unit ? str_replace(' ', '_', $unit->nama) : 'Semua_Unit';
        $filename = 'JurnalUmum_' . $unitName . '_' . date('Ymd_His') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }
}
