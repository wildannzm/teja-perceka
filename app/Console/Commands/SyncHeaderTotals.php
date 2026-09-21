<?php

namespace App\Console\Commands;

use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\TransactionItem;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('voucher:sync-header-totals {--dry-run : Show planned changes without applying them} {--unit= : Only sync this business_unit_id} {--month= : Only sync this month (YYYY-MM)}')]
#[Description('Reset stale daily header expense totals back to the journal ledger (Laba Rugi source)')]
class SyncHeaderTotals extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = DailyTransaction::query()->orderBy('transaction_date')->orderBy('id')
            ->when($this->option('unit'), fn ($q) => $q->where('business_unit_id', (int) $this->option('unit')))
            ->when($this->option('month'), fn ($q) => $q->where('transaction_date', 'like', $this->option('month').'%'));

        $expenseFixes = [];
        $incomeWarnings = [];

        foreach ($query->get(['id', 'business_unit_id', 'transaction_date', 'total_income', 'total_expense']) as $daily) {
            $journalExpense = (float) JournalEntry::where('daily_transaction_id', $daily->id)
                ->where('voucher_number', 'like', 'K%')
                ->sum('debit');

            if (abs((float) $daily->total_expense - $journalExpense) > 0.001) {
                $expenseFixes[] = [
                    'daily' => $daily->id,
                    'date' => substr((string) $daily->transaction_date, 0, 10),
                    'old' => (float) $daily->total_expense,
                    'new' => $journalExpense,
                    'model' => $daily,
                ];
            }

            // Income is reported only: entry items are its source of truth.
            $itemsIncome = (float) TransactionItem::where('daily_transaction_id', $daily->id)->sum('subtotal');
            $journalIncome = (float) JournalEntry::where('daily_transaction_id', $daily->id)
                ->where('voucher_number', 'like', 'D%')
                ->sum('debit');

            if (abs((float) $daily->total_income - $itemsIncome) > 0.001
                || abs((float) $daily->total_income - $journalIncome) > 0.001) {
                $incomeWarnings[] = [
                    $daily->id,
                    substr((string) $daily->transaction_date, 0, 10),
                    (float) $daily->total_income,
                    $itemsIncome,
                    $journalIncome,
                ];
            }
        }

        if ($expenseFixes === [] && $incomeWarnings === []) {
            $this->info('All header totals already match the ledger.');

            return self::SUCCESS;
        }

        if ($expenseFixes !== []) {
            $this->line('<comment>Expense headers out of sync (will reset to journal ledger):</comment>');
            $this->table(['daily', 'date', 'old', 'new'], array_map(
                fn ($fix) => [$fix['daily'], $fix['date'], $fix['old'], $fix['new']],
                $expenseFixes
            ));

            if (! $this->option('dry-run')) {
                foreach ($expenseFixes as $fix) {
                    $fix['model']->update(['total_expense' => $fix['new']]);
                }
                $this->info(count($expenseFixes).' header(s) synced.');
            }
        }

        if ($incomeWarnings !== []) {
            $this->line('<comment>Income mismatches (report only, not changed):</comment>');
            $this->table(
                ['daily', 'date', 'header', 'items', 'journals'],
                $incomeWarnings
            );
        }

        return self::SUCCESS;
    }
}
