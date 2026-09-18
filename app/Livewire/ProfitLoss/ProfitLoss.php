<?php

namespace App\Livewire\ProfitLoss;

use App\Models\JournalEntry;
use App\Models\Account;
use App\Models\BusinessUnit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Laba Rugi')]
class ProfitLoss extends Component
{
    public $unit_id = null;

    /** 'monthly' | 'semester' | 'yearly' */
    public string $mode = 'monthly';

    /** Format Y-m untuk bulanan, Y untuk tahunan */
    public string $period = '';

    public string $semester = '1';

    public string $semesterYear = '';

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->business_unit_id;
        }

        // Default periode ke bulan/tahun berjalan
        $now = Carbon::now();
        $this->period = $this->mode === 'monthly' ? $now->format('Y-m') : $now->format('Y');
        $this->semesterYear = $now->format('Y');
        $this->semester = $now->month <= 6 ? '1' : '2';
    }

    public function updatedMode(): void
    {
        $now = Carbon::now();
        if ($this->mode === 'monthly') {
            $this->period = $now->format('Y-m');
        } elseif ($this->mode === 'yearly') {
            $this->period = $now->format('Y');
        } elseif ($this->mode === 'semester') {
            $this->semesterYear = $now->format('Y');
            $this->semester = $now->month <= 6 ? '1' : '2';
        }
    }

    // ─── Inline Edit ─────────────────────────────────────────────────────

    public bool $isEditing = false;

    public array $editValues = [];

    public function startEditing(): void
    {
        if (! Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes'])) {
            abort(403);
        }

        $this->isEditing = true;

        // Populate editValues with current totals
        $data = $this->reportData;
        foreach (['revenueRows', 'cogsRows', 'expenseRows', 'otherRevenueRows', 'otherExpenseRows'] as $group) {
            foreach ($data[$group] as $row) {
                $this->editValues[$row->id] = $row->amount;
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

        $data = $this->reportData;
        $allOriginalRows = collect();
        foreach (['revenueRows', 'cogsRows', 'expenseRows', 'otherRevenueRows', 'otherExpenseRows'] as $group) {
            foreach ($data[$group] as $row) {
                // Attach the group name so we know the normalDirection
                $row->groupName = $group;
                $allOriginalRows->push($row);
            }
        }

        [$start, $end] = $this->periodRange();
        $batchTime = time();

        foreach ($this->editValues as $akunId => $newValue) {
            $newValue = (float) $newValue;
            $originalRow = $allOriginalRows->firstWhere('id', $akunId);

            if ($originalRow) {
                $difference = $newValue - $originalRow->amount;

                if ($difference != 0) {
                    // Determine normal balance based on group
                    $normalDirection = in_array($originalRow->groupName, ['revenueRows', 'otherRevenueRows']) ? 'credit' : 'debit';

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
                        'description' => 'Penyesuaian Manual Laba Rugi',
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

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function periodRange(): array
    {
        if ($this->mode === 'yearly') {
            $year = (int) ($this->period ?: Carbon::now()->format('Y'));

            return [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay(),
            ];
        }

        if ($this->mode === 'semester') {
            $year = (int) ($this->semesterYear ?: Carbon::now()->format('Y'));
            if ($this->semester === '1') {
                return [
                    Carbon::create($year, 1, 1)->startOfDay(),
                    Carbon::create($year, 6, 30)->endOfDay(),
                ];
            } else {
                return [
                    Carbon::create($year, 7, 1)->startOfDay(),
                    Carbon::create($year, 12, 31)->endOfDay(),
                ];
            }
        }

        $date = Carbon::parse($this->period ?: Carbon::now()->format('Y-m'));

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ];
    }

    private function periodLabel(): string
    {
        if ($this->mode === 'yearly') {
            return 'Tahun '.($this->period ?: Carbon::now()->format('Y'));
        }

        if ($this->mode === 'semester') {
            $year = $this->semesterYear ?: Carbon::now()->format('Y');

            return 'Semester '.$this->semester.' Tahun '.$year;
        }

        return Carbon::parse($this->period ?: Carbon::now()->format('Y-m'))->translatedFormat('F Y');
    }

    private function buildQuery()
    {
        [$start, $end] = $this->periodRange();
        $q = JournalEntry::query()->whereBetween('transaction_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        if ($this->unit_id) {
            $q->where('business_unit_id', $this->unit_id);
        }

        return $q;
    }

    // ─── Computed ────────────────────────────────────────────────────────

    #[Computed]
    public function units(): \Illuminate\Database\Eloquent\Collection
    {
        return BusinessUnit::orderBy('name')->get();
    }

    private function accountWithBalance(string $type, string $normalDirection): Collection
    {
        $base = $this->buildQuery();

        $accountList = Account::where('type', $type)
            ->orderBy('sort_order')
            ->get();

        $accountIds = $accountList->pluck('id');

        if ($accountIds->isEmpty()) {
            return collect();
        }

        $sums = (clone $base)
            ->whereIn('account_id', $accountIds)
            ->selectRaw('account_id, '.($normalDirection === 'credit'
                ? 'SUM(credit) - SUM(debit) as amount'
                : 'SUM(debit) - SUM(credit) as amount'))
            ->groupBy('account_id')
            ->pluck('amount', 'account_id');

        return $accountList->map(function ($account) use ($sums) {
            return (object) [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'amount' => $sums[$account->id] ?? 0,
            ];
        });
    }

    #[Computed]
    public function reportData(): array
    {
        $revenueRows = $this->accountWithBalance('pendapatan', 'credit');
        $totalRevenue = $revenueRows->sum('amount');

        $cogsRows = $this->accountWithBalance('hpp', 'debit');
        $totalHpp = $cogsRows->sum('amount');

        $grossProfit = $totalRevenue - $totalHpp;

        $expenseRows = $this->accountWithBalance('beban', 'debit');
        $totalExpenses = $expenseRows->sum('amount');

        $otherRevenueRows = $this->accountWithBalance('pendapatan_lain', 'credit');
        $totalOtherRevenue = $otherRevenueRows->sum('amount');

        $otherExpenseRows = $this->accountWithBalance('beban_lain', 'debit');
        $totalOtherExpenses = $otherExpenseRows->sum('amount');

        $netIncome = $grossProfit - $totalExpenses + $totalOtherRevenue - $totalOtherExpenses;

        return compact(
            'revenueRows', 'totalRevenue',
            'cogsRows', 'totalHpp', 'grossProfit',
            'expenseRows', 'totalExpenses',
            'otherRevenueRows', 'totalOtherRevenue',
            'otherExpenseRows', 'totalOtherExpenses',
            'netIncome'
        );
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

    // ─── Export PDF ──────────────────────────────────────────────────────

    public function exportPdf()
    {
        if (! $this->canPrint) {
            abort(403);
        }

        $data = $this->reportData;
        $periodLabel = $this->periodLabel();
        $unit = $this->selectedUnit;
        $entityName = $unit ? strtoupper($unit->name) : 'BUMDESA TEJA PERCEKA';

        [$start, $end] = $this->periodRange();
        $printDate = strtoupper($end->translatedFormat('d F Y'));
        $signatureDate = $end->translatedFormat('F Y');

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

        $pdf = Pdf::loadView('pdf.profit-loss', array_merge($data, compact('periodLabel', 'entityName', 'printDate', 'signatureDate', 'signatory', 'position')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->name) : 'Konsolidasi';
        $filename = 'LabaRugi_'.$unitSlug.'_'.str_replace(' ', '_', $periodLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        $unit = $this->selectedUnit;
        $entityName = $unit ? strtoupper($unit->name) : 'BUMDESA TEJA PERCEKA';

        [$start, $end] = $this->periodRange();
        $printDate = strtoupper($end->translatedFormat('d F Y'));
        $signatureDate = $end->translatedFormat('F Y');
        $periodLabel = $this->periodLabel();

        $signatory = Auth::user()->name;
        $position = match (true) {
            Auth::user()->hasRole('direktur_bumdes') => 'Direktur',
            Auth::user()->hasRole('sekretaris') => 'Sekretaris',
            Auth::user()->hasRole('bendahara') => 'Bendahara',
            Auth::user()->hasRole('kepala_unit') => 'Kepala Unit'.($unit ? ' '.$unit->name : ''),
            default => '',
        };

        return view('livewire.profit-loss.profit-loss', compact(
            'entityName', 'printDate', 'signatureDate', 'periodLabel', 'signatory', 'position'
        ));
    }
}
