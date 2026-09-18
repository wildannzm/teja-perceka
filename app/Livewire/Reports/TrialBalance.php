<?php

namespace App\Livewire\Reports;

use App\Models\JournalEntry;
use App\Models\Account;
use App\Models\BusinessUnit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Neraca Saldo')]
class TrialBalance extends Component
{
    /** null = konsolidasi semua unit */
    public ?int $unit_id = null;

    /** Format Y-m */
    public string $period = '';

    public bool $isEditing = false;

    public array $editValues = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->business_unit_id;
        }

        $this->period = Carbon::now()->format('Y-m');
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
        $this->editValues = [];
        $data = $this->reportData;
        foreach (['currentAssets', 'fixedAssets', 'currentLiabilities', 'longTermLiabilities', 'equity'] as $key) {
            foreach ($data[$key] as $row) {
                $this->editValues[$row->id] = $row->balance;
            }
        }
    }

    public function cancelEditing(): void
    {
        $this->isEditing = false;
        $this->editValues = [];
    }

    public function saveAdjustments(): void
    {
        $data = $this->reportData;
        $allOriginalRows = collect();

        foreach (['currentAssets', 'fixedAssets', 'currentLiabilities', 'longTermLiabilities', 'equity'] as $group) {
            foreach ($data[$group] as $row) {
                $allOriginalRows->push($row);
            }
        }

        [$start, $end] = $this->periodRange();
        $batchTime = time();

        foreach ($this->editValues as $akunId => $newValue) {
            $newValue = (float) $newValue;
            $originalRow = $allOriginalRows->firstWhere('id', $akunId);

            if ($originalRow) {
                $difference = $newValue - $originalRow->balance;

                if ($difference != 0) {
                    $account = Account::find($akunId);
                    if (! $account) {
                        continue;
                    }

                    $normalDirection = $this->getNormalBalanceType($account->type);

                    $debit = 0;
                    $credit = 0;

                    if ($normalDirection === 'credit') {
                        if ($difference > 0) {
                            $credit = abs($difference);
                        } else {
                            $debit = abs($difference);
                        }
                    } else { // arah normal debet
                        if ($difference > 0) {
                            $debit = abs($difference);
                        } else {
                            $credit = abs($difference);
                        }
                    }

                    JournalEntry::create([
                        'voucher_number' => 'ADJ-'.$batchTime.'-'.$akunId,
                        'transaction_date' => $end->format('Y-m-d'),
                        'description' => 'Penyesuaian Manual Neraca Saldo',
                        'account_id' => $akunId,
                        'debit' => $debit,
                        'credit' => $credit,
                        'business_unit_id' => $this->unit_id,
                    ]);
                }
            }
        }

        $this->isEditing = false;
        $this->editValues = [];
        unset($this->reportData);
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
        $accounts = Account::where('type', '!=', 'header')->orderBy('code')->get();

        $currentAssets = collect();
        $fixedAssets = collect();
        $currentLiabilities = collect();
        $longTermLiabilities = collect();
        $equity = collect();

        $totalCurrentAssets = 0;
        $totalFixedAssets = 0;
        $totalCurrentLiabilities = 0;
        $totalLongTermLiabilities = 0;
        $totalEquity = 0;

        $totalRevenue = 0;
        $totalExpenses = 0;

        if ($this->period) {
            [$startDate, $endDate] = $this->periodRange();

            $query = JournalEntry::whereDate('transaction_date', '<=', $endDate->format('Y-m-d'));

            if ($this->unit_id) {
                $query->where('business_unit_id', $this->unit_id);
            }

            $balancePerAccount = $query->select(
                'account_id',
                DB::raw('SUM(debit) as totalDebit'),
                DB::raw('SUM(credit) as totalCredit')
            )
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id');

            foreach ($accounts as $account) {
                $balance = $balancePerAccount->get($account->id);
                $debitTotal = $balance ? $balance->totalDebit : 0;
                $creditTotal = $balance ? $balance->totalCredit : 0;

                $normalBalance = $this->getNormalBalanceType($account->type);
                $closingBalance = 0;

                if ($normalBalance === 'debit') {
                    $closingBalance = $debitTotal - $creditTotal;
                } else {
                    $closingBalance = $creditTotal - $debitTotal;
                }

                $prefix = substr($account->code, 0, 3);
                $firstDigit = substr($account->code, 0, 1);

                // Calculate Net Income (Laba Bersih) dynamically from nominal accounts
                if ($firstDigit === '4' || $firstDigit === '7') {
                    $totalRevenue += $closingBalance; // Normal balance is kredit, so saldoAkhir is Kredit-Debit
                } elseif ($firstDigit === '5' || $firstDigit === '6') {
                    $totalExpenses += $closingBalance; // Normal balance is debit, so saldoAkhir is Debit-Kredit
                }

                if ($closingBalance == 0 && $firstDigit !== '3' && $firstDigit !== '1' && $firstDigit !== '2') {
                    continue; // Skip zero balances unless we want to show them? Actually, let's include them if they are in the balance sheet structure but we can filter zero balance out in view or keep them as '-' like in excel.
                    // The Excel shows some '-' so we keep them, or we just keep all balance sheet accounts (1, 2, 3)
                }

                $item = (object) [
                    'id' => $account->id,
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $closingBalance,
                ];

                if ($prefix === '1-1') {
                    $currentAssets->push($item);
                    $totalCurrentAssets += $closingBalance;
                } elseif ($prefix === '1-2') {
                    $fixedAssets->push($item);
                    $totalFixedAssets += $closingBalance;
                } elseif ($prefix === '2-1') {
                    $currentLiabilities->push($item);
                    $totalCurrentLiabilities += $closingBalance;
                } elseif ($prefix === '2-2') {
                    $longTermLiabilities->push($item);
                    $totalLongTermLiabilities += $closingBalance;
                } elseif ($firstDigit === '3') {
                    if ($account->code === '3-3000') {
                        // Skip injecting Laba Bersih here, we'll do it manually after the loop
                    } else {
                        $equity->push($item);
                        $totalEquity += $closingBalance;
                    }
                }
            }

            // Inject Laba Bersih
            $netIncome = $totalRevenue - $totalExpenses;

            $netIncomeAccount = Account::where('code', '3-3000')->first();
            if ($netIncomeAccount) {
                $equity->push((object) [
                    'id' => $netIncomeAccount->id,
                    'code' => $netIncomeAccount->code,
                    'name' => 'LABA BERSIH', // Override name to match excel
                    'balance' => $netIncome,
                ]);
                $totalEquity += $netIncome;
            }
        }

        $totalAssets = $totalCurrentAssets + $totalFixedAssets;
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;
        $totalLiabilitiesEquity = $totalLiabilities + $totalEquity;

        return compact(
            'currentAssets', 'fixedAssets', 'totalCurrentAssets', 'totalFixedAssets', 'totalAssets',
            'currentLiabilities', 'longTermLiabilities', 'totalCurrentLiabilities', 'totalLongTermLiabilities', 'totalLiabilities',
            'equity', 'totalEquity', 'totalLiabilitiesEquity', 'netIncome'
        );
    }

    public function exportPdf()
    {
        if (! $this->canPrint) {
            abort(403);
        }

        $data = $this->reportData;
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

        $pdf = Pdf::loadView('pdf.trial-balance', array_merge($data, compact('periodLabel', 'entityName', 'printDate', 'signatureDate', 'signatory', 'position')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->name) : 'Konsolidasi';
        $filename = 'NeracaSaldo_'.$unitSlug.'_'.str_replace(' ', '_', $periodLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.reports.trial-balance');
    }
}
