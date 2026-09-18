<?php

namespace App\Livewire\UnitHead;

use App\Models\JournalEntry;
use App\Models\BusinessUnit;
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
class TransactionHistory extends Component
{
    #[Locked]
    public ?int $unitId = null;

    public ?BusinessUnit $unit = null;

    public string $mode = 'daily';

    public string $currentDate = '';

    public array $dates = [];

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->business_unit_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->business_unit_id;
        $this->unit = BusinessUnit::findOrFail($this->unitId);
        $isWeekly = $this->unit->input_frequency === 'weekly';

        $this->mode = $isWeekly ? 'weekly' : 'daily';

        $today = Carbon::today();
        $this->dates = [
            'daily' => $today->format('Y-m-d'),
            'weekly' => $today->copy()->startOfWeek()->format('Y-m-d'),
            'monthly' => $today->copy()->startOfMonth()->format('Y-m-d'),
            'yearly' => $today->copy()->startOfYear()->format('Y-m-d'),
        ];

        $this->currentDate = $this->dates[$this->mode];
    }

    public function updatedCurrentDate(): void
    {
        // Snap the chosen date to the correct period start if necessary
        $date = Carbon::parse($this->currentDate);
        if ($this->mode === 'weekly') {
            $this->currentDate = $date->startOfWeek()->format('Y-m-d');
        } elseif ($this->mode === 'monthly') {
            $this->currentDate = $date->startOfMonth()->format('Y-m-d');
        } elseif ($this->mode === 'yearly') {
            $this->currentDate = $date->startOfYear()->format('Y-m-d');
        }
        $this->dates[$this->mode] = $this->currentDate;
    }

    public function updatedMode(): void
    {
        $this->currentDate = $this->dates[$this->mode];
    }

    public function previousPeriod(): void
    {
        $date = Carbon::parse($this->currentDate);

        $this->currentDate = match ($this->mode) {
            'daily' => $date->subDay()->format('Y-m-d'),
            'weekly' => $date->subWeek()->startOfWeek()->format('Y-m-d'),
            'monthly' => $date->subMonth()->startOfMonth()->format('Y-m-d'),
            'yearly' => $date->subYear()->startOfYear()->format('Y-m-d'),
        };
        $this->dates[$this->mode] = $this->currentDate;
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->currentDate);

        $this->currentDate = match ($this->mode) {
            'daily' => $date->addDay()->format('Y-m-d'),
            'weekly' => $date->addWeek()->startOfWeek()->format('Y-m-d'),
            'monthly' => $date->addMonth()->startOfMonth()->format('Y-m-d'),
            'yearly' => $date->addYear()->startOfYear()->format('Y-m-d'),
        };
        $this->dates[$this->mode] = $this->currentDate;
    }

    #[Computed]
    public function dateRange(): array
    {
        $date = Carbon::parse($this->currentDate);

        return match ($this->mode) {
            'daily' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'yearly' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
        };
    }

    #[Computed]
    public function periodLabel(): string
    {
        [$start, $end] = $this->dateRange;

        return match ($this->mode) {
            'daily' => $start->translatedFormat('d F Y'),
            'weekly' => $start->month === $end->month
                ? $start->format('d').' - '.$end->translatedFormat('d F Y')
                : $start->translatedFormat('d M').' - '.$end->translatedFormat('d M Y'),
            'monthly' => $start->translatedFormat('F Y'),
            'yearly' => $start->format('Y'),
        };
    }

    #[Computed]
    public function transactions()
    {
        [$start, $end] = $this->dateRange;

        // Fetch General Journal data for all accounts
        return JournalEntry::with('account')
            ->where('business_unit_id', $this->unitId)
            ->whereBetween('transaction_date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderBy('transaction_date', 'asc')
            ->orderBy('voucher_number', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    #[Computed]
    public function totalDebit(): float
    {
        return $this->transactions->sum('debit');
    }

    #[Computed]
    public function totalCredit(): float
    {
        return $this->transactions->sum('credit');
    }

    public function exportPdf()
    {
        if (! class_exists(Pdf::class)) {
            $this->addError('pdf', 'Package PDF belum terinstall. Jalankan: composer require barryvdh/laravel-dompdf');

            return;
        }

        $transactions = $this->transactions;
        $period = $this->periodLabel;
        $unit = $this->unit;
        $totalDebit = $this->totalDebit;
        $totalCredit = $this->totalCredit;

        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $pdf = Pdf::loadView('pdf.transaction-history', compact(
            'transactions',
            'period',
            'unit',
            'totalDebit',
            'totalCredit'
        ))->setPaper('a4', 'landscape'); // Landscape fits ledger tables better

        $filename = 'JurnalUmum_'.str_replace(' ', '_', $unit->name).'_'.str_replace([' ', '-', '/'], '_', $period).'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        return view('livewire.unit-head.transaction-history');
    }
}
