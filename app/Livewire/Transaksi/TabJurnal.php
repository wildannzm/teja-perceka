<?php

namespace App\Livewire\Transaksi;

use App\Models\JurnalUmum;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class TabJurnal extends Component
{
    #[Reactive]
    public $unitId = null;

    #[Reactive]
    public string $mode = 'harian';

    #[Reactive]
    public string $tanggal = '';

    #[Reactive]
    public string $minggu = '';

    #[Reactive]
    public string $bulan = '';

    #[Reactive]
    public string $tahun = '';

    #[Computed]
    public function dateRange(): array
    {
        switch ($this->mode) {
            case 'harian':
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));
                return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

            case 'mingguan':
                $start = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                return [$start->copy()->startOfWeek(), $start->copy()->endOfWeek()];

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')) . '-01');
                return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];

            case 'tahunan':
                $year = (int)($this->tahun ?: Carbon::now()->format('Y'));
                return [Carbon::create($year, 1, 1)->startOfDay(), Carbon::create($year, 12, 31)->endOfDay()];
        }

        $today = Carbon::today();
        return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
    }

    #[Computed]
    public function periodeLabel(): string
    {
        [$start, $end] = $this->dateRange;

        return match ($this->mode) {
            'harian'   => $start->translatedFormat('d F Y'),
            'mingguan' => $start->month === $end->month
                ? $start->format('d') . ' - ' . $end->translatedFormat('d F Y')
                : $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y'),
            'bulanan'  => $start->translatedFormat('F Y'),
            'tahunan'  => $start->format('Y'),
            default    => '-',
        };
    }

    #[Computed]
    public function transactions()
    {
        [$start, $end] = $this->dateRange;

        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('tanggal', 'asc')
            ->orderBy('nomor_bukti', 'asc')
            ->orderBy('id', 'asc');

        if ($this->unitId) {
            $query->where('unit_wisata_id', $this->unitId);
        }

        return $query->get()->groupBy('nomor_bukti');
    }

    #[Computed]
    public function totalDebet(): float
    {
        return $this->transactions->flatten()->sum('debet');
    }

    #[Computed]
    public function totalKredit(): float
    {
        return $this->transactions->flatten()->sum('kredit');
    }

    #[Computed]
    public function canExportPdf(): bool
    {
        return in_array($this->mode, ['bulanan', 'tahunan']);
    }

    /**
     * Boleh hapus: sekretaris, bendahara, direktur_bumdes, kepala_unit (own unit only)
     * Tidak boleh hapus: kepala_desa, pengawas
     */
    #[Computed]
    public function canDelete(): bool
    {
        return Auth::user()->hasAnyRole(['sekretaris', 'bendahara', 'direktur_bumdes', 'kepala_unit']);
    }

    public function delete(int $firstIdInGroup): void
    {
        if (!$this->canDelete) {
            abort(403);
        }

        $jurnal = JurnalUmum::find($firstIdInGroup);
        if (!$jurnal) return;

        // Kepala Unit: only delete own unit's journals
        if (Auth::user()->hasRole('kepala_unit')) {
            if ($jurnal->unit_wisata_id !== Auth::user()->unit_wisata_id) {
                abort(403);
            }
        }

        DB::transaction(function () use ($jurnal) {
            JurnalUmum::where('nomor_bukti', $jurnal->nomor_bukti)->delete();
        });

        \Flux::toast(variant: 'success', text: 'Satu set jurnal (debet & kredit) berhasil dihapus.');
    }

    public function exportPdf()
    {
        if (!$this->canExportPdf) {
            \Flux::toast(variant: 'warning', text: 'Cetak PDF hanya tersedia untuk mode Bulanan dan Tahunan.');
            return;
        }

        if (!class_exists(Pdf::class)) {
            \Flux::toast(variant: 'danger', text: 'Package PDF belum terinstall.');
            return;
        }

        [$start, $end] = $this->dateRange;

        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('tanggal', 'asc')
            ->orderBy('nomor_bukti', 'asc')
            ->orderBy('id', 'asc');

        if ($this->unitId) {
            $query->where('unit_wisata_id', $this->unitId);
        }

        $transactions = $query->get();
        $totalDebet   = $transactions->sum('debet');
        $totalKredit  = $transactions->sum('kredit');
        $unit         = $this->unitId ? UnitWisata::find($this->unitId) : null;
        $periode      = $this->periodeLabel;

        $pdf = Pdf::loadView('pdf.riwayat-transaksi', compact(
            'transactions',
            'periode',
            'unit',
            'totalDebet',
            'totalKredit'
        ))->setPaper('a4', 'landscape');

        $unitName = $unit ? str_replace(' ', '_', $unit->nama) : 'Semua_Unit';
        $filename = 'JurnalUmum_' . $unitName . '_' . str_replace([' ', '-', '/'], '_', $periode) . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        return view('livewire.transaksi.tab-jurnal');
    }
}
