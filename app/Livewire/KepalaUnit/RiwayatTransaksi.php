<?php

namespace App\Livewire\KepalaUnit;

use App\Models\JurnalUmum;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Riwayat & Rekap Transaksi')]
class RiwayatTransaksi extends Component
{
    #[Locked]
    public ?int $unitId = null;

    public ?UnitWisata $unit = null;

    public bool $isMingguanOnly = false;

    public string $mode = 'harian';

    public string $currentDate = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unit = UnitWisata::findOrFail($this->unitId);
        $this->isMingguanOnly = $this->unit->frekuensi_input === 'mingguan';

        $this->mode = $this->isMingguanOnly ? 'mingguan' : 'harian';

        $today = Carbon::today();
        $this->currentDate = $this->mode === 'mingguan'
            ? $today->copy()->startOfWeek()->format('Y-m-d')
            : $today->format('Y-m-d');
    }

    public function updatedCurrentDate(): void
    {
        // Snap the chosen date to the correct period start if necessary
        $date = Carbon::parse($this->currentDate);
        if ($this->mode === 'mingguan') {
            $this->currentDate = $date->startOfWeek()->format('Y-m-d');
        } elseif ($this->mode === 'bulanan') {
            $this->currentDate = $date->startOfMonth()->format('Y-m-d');
        } elseif ($this->mode === 'tahunan') {
            $this->currentDate = $date->startOfYear()->format('Y-m-d');
        }
    }

    public function updatedMode(): void
    {
        $this->updatedCurrentDate();
    }

    public function previousPeriod(): void
    {
        $date = Carbon::parse($this->currentDate);

        $this->currentDate = match ($this->mode) {
            'harian' => $date->subDay()->format('Y-m-d'),
            'mingguan' => $date->subWeek()->startOfWeek()->format('Y-m-d'),
            'bulanan' => $date->subMonth()->startOfMonth()->format('Y-m-d'),
            'tahunan' => $date->subYear()->startOfYear()->format('Y-m-d'),
        };
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->currentDate);

        $this->currentDate = match ($this->mode) {
            'harian' => $date->addDay()->format('Y-m-d'),
            'mingguan' => $date->addWeek()->startOfWeek()->format('Y-m-d'),
            'bulanan' => $date->addMonth()->startOfMonth()->format('Y-m-d'),
            'tahunan' => $date->addYear()->startOfYear()->format('Y-m-d'),
        };
    }

    #[Computed]
    public function dateRange(): array
    {
        $date = Carbon::parse($this->currentDate);

        return match ($this->mode) {
            'harian' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'mingguan' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'bulanan' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'tahunan' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
        };
    }

    #[Computed]
    public function periodeLabel(): string
    {
        [$start, $end] = $this->dateRange;

        return match ($this->mode) {
            'harian' => $start->translatedFormat('d F Y'),
            'mingguan' => $start->month === $end->month
                ? $start->format('d').' - '.$end->translatedFormat('d F Y')
                : $start->translatedFormat('d M').' - '.$end->translatedFormat('d M Y'),
            'bulanan' => $start->translatedFormat('F Y'),
            'tahunan' => $start->format('Y'),
        };
    }

    #[Computed]
    public function transactions()
    {
        [$start, $end] = $this->dateRange;

        // Ambil data dari Jurnal Umum untuk semua akun
        return JurnalUmum::with('kodeAkun')
            ->where('unit_wisata_id', $this->unitId)
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('tanggal', 'asc')
            ->orderBy('nomor_bukti', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    #[Computed]
    public function totalDebet(): float
    {
        return $this->transactions->sum('debet');
    }

    #[Computed]
    public function totalKredit(): float
    {
        return $this->transactions->sum('kredit');
    }

    public function exportPdf()
    {
        if (! class_exists(Pdf::class)) {
            $this->addError('pdf', 'Package PDF belum terinstall. Jalankan: composer require barryvdh/laravel-dompdf');

            return;
        }

        $transactions = $this->transactions;
        $periode = $this->periodeLabel;
        $unit = $this->unit;
        $totalDebet = $this->totalDebet;
        $totalKredit = $this->totalKredit;

        $pdf = Pdf::loadView('pdf.riwayat-transaksi', compact(
            'transactions',
            'periode',
            'unit',
            'totalDebet',
            'totalKredit'
        ))->setPaper('a4', 'landscape'); // Landscape lebih cocok untuk tabel ledger

        $filename = 'JurnalUmum_'.str_replace(' ', '_', $unit->nama).'_'.str_replace([' ', '-', '/'], '_', $periode).'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        return view('livewire.kepala-unit.riwayat-transaksi');
    }
}
