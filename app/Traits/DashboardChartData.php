<?php

namespace App\Traits;

use App\Models\JurnalUmum;
use Illuminate\Support\Facades\DB;

trait DashboardChartData
{
    /**
     * Get chart data for monthly Pemasukan and Pengeluaran for the current year.
     *
     * @return array{pemasukan: array<int, float>, pengeluaran: array<int, float>}
     */
    protected function getChartData(?int $unitId = null): array
    {
        $currentYear = date('Y');

        $driver = DB::connection()->getDriverName();
        $monthSelect = $driver === 'sqlite' ? "CAST(strftime('%m', tanggal) AS INTEGER)" : 'MONTH(tanggal)';

        $pemasukanQuery = JurnalUmum::select(
            DB::raw("$monthSelect as month"),
            DB::raw('SUM(kredit) as total')
        )
            ->whereYear('tanggal', $currentYear)
            ->whereHas('kodeAkun', function ($q) {
                $q->where('kode', 'like', '4-%')->orWhere('kode', 'like', '7-%');
            });

        if ($unitId) {
            $pemasukanQuery->where('unit_wisata_id', $unitId);
        }

        $pemasukanPerBulan = $pemasukanQuery->groupBy('month')->pluck('total', 'month')->toArray();

        $pengeluaranQuery = JurnalUmum::select(
            DB::raw("$monthSelect as month"),
            DB::raw('SUM(debet) as total')
        )
            ->whereYear('tanggal', $currentYear)
            ->whereHas('kodeAkun', function ($q) {
                $q->where('kode', 'like', '5-%')->orWhere('kode', 'like', '6-%');
            });

        if ($unitId) {
            $pengeluaranQuery->where('unit_wisata_id', $unitId);
        }

        $pengeluaranPerBulan = $pengeluaranQuery->groupBy('month')->pluck('total', 'month')->toArray();

        $chartPemasukan = [];
        $chartPengeluaran = [];

        for ($i = 1; $i <= 12; $i++) {
            $chartPemasukan[] = (float) ($pemasukanPerBulan[$i] ?? 0);
            $chartPengeluaran[] = (float) ($pengeluaranPerBulan[$i] ?? 0);
        }

        return [
            'pemasukan' => $chartPemasukan,
            'pengeluaran' => $chartPengeluaran,
        ];
    }
}
