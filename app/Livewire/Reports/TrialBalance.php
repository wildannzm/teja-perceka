<?php

namespace App\Livewire\Reports;

use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\JournalEntry;
use App\Support\BumdesCashBalance;
use App\Support\SafeDates;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Neraca Saldo')]
class TrialBalance extends Component
{
    /** null = consolidate all units */
    public mixed $unit_id = null;

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
        } elseif (empty($this->unit_id)) {
            $this->unit_id = 'bumdes';
        }

        $this->period = Carbon::now()->format('Y-m');
    }

    public function startEditing(): void
    {
        if (! Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes'])) {
            abort(403);
        }

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
        if (! Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes'])) {
            abort(403);
        }

        $unitId = $this->resolveAdjustmentUnitId();

        $data = $this->reportData;
        $allOriginalRows = collect();

        foreach (['currentAssets', 'fixedAssets', 'currentLiabilities', 'longTermLiabilities', 'equity'] as $group) {
            foreach ($data[$group] as $row) {
                $allOriginalRows->push($row);
            }
        }

        [$start, $end] = $this->periodRange();
        $batchTime = time();

        foreach ($this->editValues as $accountId => $newValue) {
            $newValue = (float) $newValue;
            $originalRow = $allOriginalRows->firstWhere('id', $accountId);

            if ($originalRow) {
                $difference = $newValue - $originalRow->balance;

                if ($difference != 0) {
                    if (! is_numeric($accountId) || ! Account::whereKey($accountId)->exists()) {
                        continue;
                    }

                    $account = Account::find($accountId);

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
                        'voucher_number' => 'ADJ-'.$batchTime.'-'.$accountId,
                        'transaction_date' => $end->format('Y-m-d'),
                        'description' => 'Penyesuaian Manual Neraca Saldo',
                        'account_id' => $accountId,
                        'debit' => $debit,
                        'credit' => $credit,
                        'business_unit_id' => $unitId,
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

    public function isBumdesScope(): bool
    {
        return ! Auth::user()?->hasRole('kepala_unit') && ($this->unit_id === 'bumdes' || empty($this->unit_id));
    }

    private function resolveAdjustmentUnitId(): ?int
    {
        if (Auth::user()?->hasRole('kepala_unit')) {
            return Auth::user()->business_unit_id;
        }

        return ($this->unit_id && is_numeric($this->unit_id)) ? (int) $this->unit_id : null;
    }

    private function periodRange(): array
    {
        $date = SafeDates::month($this->period);

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
        return ($this->unit_id && is_numeric($this->unit_id)) ? BusinessUnit::find($this->unit_id) : null;
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

            if ($this->isBumdesScope()) {
                $query->where(function ($q) {
                    $q->where('voucher_number', 'like', 'KBM%')
                        ->orWhere(function ($sub) {
                            $sub->where('voucher_number', 'like', 'DBM%')
                                ->whereNull('daily_transaction_id')
                                ->whereNull('business_unit_id');
                        });
                });
            } elseif ($this->unit_id && is_numeric($this->unit_id)) {
                $query->where('business_unit_id', (int) $this->unit_id);
            }

            $balancePerAccount = $query->select(
                'account_id',
                DB::raw('SUM(debit) as totalDebit'),
                DB::raw('SUM(credit) as totalCredit')
            )
                ->groupBy('account_id')
                ->get()
                ->keyBy('account_id');

            // BUMDes scope: add virtual amounts
            // - Saldo sebelumnya: debit cash 1-1100, credit equity 3-2000
            // - Net income per unit: debit cash 1-1100, credit revenue 4-2000
            if ($this->isBumdesScope()) {
                $openingBalance = BumdesCashBalance::getOpeningBalance($startDate);
                $netUnitIncome = BumdesCashBalance::getTotalNetUnitIncome($startDate, $endDate);

                // Cash account: opening balance + net unit income (debit)
                $cashAccount = Account::where('code', '1-1100')->first();
                if ($cashAccount && ($openingBalance + $netUnitIncome) > 0) {
                    $row = $balancePerAccount->get($cashAccount->id);
                    if ($row) {
                        $row->totalDebit += ($openingBalance + $netUnitIncome);
                    } else {
                        $balancePerAccount->put($cashAccount->id, (object) [
                            'account_id' => $cashAccount->id,
                            'totalDebit' => ($openingBalance + $netUnitIncome),
                            'totalCredit' => 0,
                        ]);
                    }
                }

                // Equity account (3-2000): opening balance (credit)
                if ($openingBalance > 0) {
                    $equityAccount = Account::where('code', '3-2000')->first();
                    if ($equityAccount) {
                        $row = $balancePerAccount->get($equityAccount->id);
                        if ($row) {
                            $row->totalCredit += $openingBalance;
                        } else {
                            $balancePerAccount->put($equityAccount->id, (object) [
                                'account_id' => $equityAccount->id,
                                'totalDebit' => 0,
                                'totalCredit' => $openingBalance,
                            ]);
                        }
                    }
                }

                // Revenue account (4-2000): net unit income only (credit)
                if ($netUnitIncome > 0) {
                    $revenueAccount = Account::where('code', '4-2000')->first();
                    if ($revenueAccount) {
                        $row = $balancePerAccount->get($revenueAccount->id);
                        if ($row) {
                            $row->totalCredit += $netUnitIncome;
                        } else {
                            $balancePerAccount->put($revenueAccount->id, (object) [
                                'account_id' => $revenueAccount->id,
                                'totalDebit' => 0,
                                'totalCredit' => $netUnitIncome,
                            ]);
                        }
                    }
                }
            }

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
        $periodLabel = SafeDates::month($this->period)->translatedFormat('F Y');

        $signatory = Auth::user()->name;
        $position = match (true) {
            Auth::user()->hasRole('direktur_bumdes') => 'Direktur',
            Auth::user()->hasRole('sekretaris') => 'Sekretaris',
            Auth::user()->hasRole('bendahara') => 'Bendahara',
            Auth::user()->hasRole('kepala_unit') => 'Kepala Unit'.($unit ? ' '.$unit->name : ''),
            default => '',
        };

        set_time_limit(120);

        $pdf = Pdf::loadView('pdf.trial-balance', array_merge($data, compact('periodLabel', 'entityName', 'printDate', 'signatureDate', 'signatory', 'position')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? Str::slug($unit->name, '_') : 'Konsolidasi';
        $filename = 'NeracaSaldo_'.$unitSlug.'_'.Str::slug($periodLabel, '_').'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function updatingUnitId($value): void
    {
        $user = Auth::user();
        if ($user && $user->hasRole('kepala_unit') && (int) $value !== (int) $user->business_unit_id) {
            abort(403, 'Unauthorized');
        }
    }

    public function render()
    {
        $user = Auth::user();
        if ($user && $user->hasRole('kepala_unit') && $this->unit_id !== $user->business_unit_id) {
            $this->unit_id = $user->business_unit_id;
        }

        return view('livewire.reports.trial-balance');
    }
}
