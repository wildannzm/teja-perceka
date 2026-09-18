<?php

namespace App\Traits;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

trait DashboardChartData
{
    /**
     * Get chart data for monthly income and expenses for the current year.
     *
     * @return array{income: array<int, float>, expenses: array<int, float>}
     */
    protected function getChartData(?int $unitId = null): array
    {
        $currentYear = date('Y');

        $driver = DB::connection()->getDriverName();
        $monthSelect = $driver === 'sqlite' ? "CAST(strftime('%m', transaction_date) AS INTEGER)" : 'MONTH(transaction_date)';

        $incomeQuery = JournalEntry::select(
            DB::raw("$monthSelect as month"),
            DB::raw('SUM(credit) as total')
        )
            ->whereYear('transaction_date', $currentYear)
            ->whereHas('account', function ($q) {
                $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
            });

        if ($unitId) {
            $incomeQuery->where('business_unit_id', $unitId);
        }

        $monthlyIncome = $incomeQuery->groupBy('month')->pluck('total', 'month')->toArray();

        $expenseQuery = JournalEntry::select(
            DB::raw("$monthSelect as month"),
            DB::raw('SUM(debit) as total')
        )
            ->whereYear('transaction_date', $currentYear)
            ->whereHas('account', function ($q) {
                $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
            });

        if ($unitId) {
            $expenseQuery->where('business_unit_id', $unitId);
        }

        $monthlyExpenses = $expenseQuery->groupBy('month')->pluck('total', 'month')->toArray();

        $incomeChart = [];
        $expenseChart = [];

        for ($i = 1; $i <= 12; $i++) {
            $incomeChart[] = (float) ($monthlyIncome[$i] ?? 0);
            $expenseChart[] = (float) ($monthlyExpenses[$i] ?? 0);
        }

        return [
            'income' => $incomeChart,
            'expenses' => $expenseChart,
        ];
    }
}
