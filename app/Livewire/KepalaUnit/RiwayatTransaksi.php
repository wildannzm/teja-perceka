<?php

namespace App\Livewire\KepalaUnit;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
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

    #[Locked]
    public ?int $akunKasId = null;

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

        $akunKas = KodeAkun::where('kode', '1-1100')->first();
        if ($akunKas) {
            $this->akunKasId = $akunKas->id;
        }

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

        if (! $this->akunKasId) {
            return collect([]);
        }

        // Ambil data dari Jurnal Umum khusus untuk akun Kas
        $jurnals = JurnalUmum::with('kodeAkun')
            ->where('unit_wisata_id', $this->unitId)
            ->where('kode_akun_id', $this->akunKasId)
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('tanggal', 'asc') // Urut dari terlama untuk logika running balance
            ->orderBy('id', 'asc')
            ->get();

        $saldo = 0;

        return $jurnals->map(function ($jurnal) use (&$saldo) {
            $saldo += $jurnal->debet;
            $saldo -= $jurnal->kredit;

            $jurnal->saldo_berjalan = $saldo;

            return $jurnal;
        });
    }

    #[Computed]
    public function grandTotal(): float
    {
        $trxs = $this->transactions;
        if ($trxs->isEmpty()) {
            return 0;
        }

        return (float) $trxs->last()->saldo_berjalan;
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
        $grandTotal = $this->grandTotal;

        $pdf = Pdf::loadView('pdf.riwayat-transaksi', compact(
            'transactions',
            'periode',
            'unit',
            'grandTotal',
        ))->setPaper('a4', 'landscape'); // Landscape lebih cocok untuk tabel ledger

        $filename = 'BukuKas_'.str_replace(' ', '_', $unit->nama).'_'.str_replace([' ', '-', '/'], '_', $periode).'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        return view('livewire.kepala-unit.riwayat-transaksi');
    }
}
