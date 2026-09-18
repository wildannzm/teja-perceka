<?php

namespace App\Livewire\Reports;

use App\Models\JournalEntry;
use App\Models\Account;
use App\Models\BusinessUnit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Buku Besar')]
class GeneralLedger extends Component
{
    /** null = konsolidasi semua unit */
    public ?int $unit_id = null;

    /** Format Y-m */
    public string $period = '';

    public ?int $account_id = null;

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->business_unit_id;
        }

        $this->period = Carbon::now()->format('Y-m');

        $firstAccount = Account::where('type', '!=', 'header')->orderBy('code')->first();
        if ($firstAccount) {
            $this->account_id = $firstAccount->id;
        }
    }

    private function getNormalBalanceType(string $type): string
    {
        $type = strtolower($type);
        if (in_array($type, ['aktiva', 'aset', 'beban', 'beban_lain', 'hpp'])) {
            return 'debit';
        }

        return 'credit';
    }

    private function periodRange(): array
    {
        $date = Carbon::parse($this->period ?: Carbon::now()->format('Y-m'));

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ];
    }

    #[Computed]
    public function units(): Collection
    {
        return BusinessUnit::orderBy('name')->get();
    }

    #[Computed]
    public function accounts(): Collection
    {
        return Account::where('type', '!=', 'header')->orderBy('code')->get();
    }

    #[Computed]
    public function isKepalaUnit(): bool
    {
        return Auth::user()->hasRole('kepala_unit');
    }

    #[Computed]
    public function canPrint(): bool
    {
        return Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes']);
    }

    #[Computed]
    public function selectedUnit(): ?BusinessUnit
    {
        return $this->unit_id ? BusinessUnit::find($this->unit_id) : null;
    }

    #[Computed]
    public function reportData(): array
    {
        $transactions = collect();
        $openingBalance = 0;
        $selectedAccount = null;
        $normalBalance = 'debit';
        $totalDebit = 0;
        $totalCredit = 0;

        if ($this->account_id && $this->period) {
            $selectedAccount = Account::find($this->account_id);

            if ($selectedAccount) {
                $normalBalance = $this->getNormalBalanceType($selectedAccount->type);
                [$startDate, $endDate] = $this->periodRange();

                // Calculate Saldo Awal (before start date)
                $openingQuery = JournalEntry::where('account_id', $this->account_id)
                    ->whereDate('transaction_date', '<', $startDate->format('Y-m-d'));

                if ($this->unit_id) {
                    $openingQuery->where('business_unit_id', $this->unit_id);
                }

                $openingDebit = (clone $openingQuery)->sum('debit');
                $openingCredit = (clone $openingQuery)->sum('credit');

                if ($normalBalance === 'debit') {
                    $openingBalance = $openingDebit - $openingCredit;
                } else {
                    $openingBalance = $openingCredit - $openingDebit;
                }

                // Get current transactions
                $currentQuery = JournalEntry::with('businessUnit')
                    ->where('account_id', $this->account_id)
                    ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orderBy('transaction_date', 'asc')
                    ->orderBy('id', 'asc');

                if ($this->unit_id) {
                    $currentQuery->where('business_unit_id', $this->unit_id);
                }

                $transactions = $currentQuery->get();
                $totalDebit = $transactions->sum('debit');
                $totalCredit = $transactions->sum('credit');
            }
        }

        return compact('transactions', 'openingBalance', 'selectedAccount', 'normalBalance', 'totalDebit', 'totalCredit');
    }

    public function exportPdf()
    {
        if (! $this->canPrint) {
            abort(403);
        }

        $data = $this->reportData;
        if (! $data['selectedAccount']) {
            abort(404, 'Kode Akun tidak ditemukan.');
        }

        $unit = $this->selectedUnit;
        $entityName = $unit ? strtoupper($unit->name) : 'BUMDESA TEJA PERCEKA';

        [$start, $end] = $this->periodRange();
        $printDate = strtoupper($end->translatedFormat('d F Y'));
        $signatureDate = $end->translatedFormat('F Y');
        $periodLabel = Carbon::parse($this->period ?: Carbon::now()->format('Y-m'))->translatedFormat('F Y');

        $signatory = Auth::user()->name;
        $position = match (true) {
            Auth::user()->hasRole('direktur_bumdes') => 'Direktur',
            Auth::user()->hasRole('sekretaris') => 'Sekretaris',
            Auth::user()->hasRole('bendahara') => 'Bendahara',
            Auth::user()->hasRole('kepala_unit') => 'Kepala Unit'.($unit ? ' '.$unit->name : ''),
            default => '',
        };

        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $pdf = Pdf::loadView('pdf.general-ledger', array_merge($data, compact('periodLabel', 'entityName', 'printDate', 'signatureDate', 'signatory', 'position')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->name) : 'Konsolidasi';
        $filename = 'BukuBesar_'.$unitSlug.'_'.$data['selectedAccount']->code.'_'.str_replace(' ', '_', $periodLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.reports.general-ledger');
    }
}
