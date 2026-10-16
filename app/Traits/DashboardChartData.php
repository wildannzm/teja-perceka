<?php

namespace App\Traits;

use App\Models\JournalEntry;
use Illuminate\Support\Carbon;

trait DashboardChartData
{
    /**
     * Normalize a dashboard period filter, defaulting to monthly.
     */
    protected function normalizeDashboardPeriod(string $period): string
    {
        return in_array($period, ['mingguan', 'bulanan', 'semester', 'tahunan'], true) ? $period : 'bulanan';
    }

    /**
     * Resolve all view data for a dashboard period in one call.
     *
     * @return array{period: string, income: float, expenses: float, netIncome: float, chartLabels: array<int, string>, incomeChart: array<int, float>, expenseChart: array<int, float>, periodLabel: string, periodRange: string}
     */
    protected function dashboardViewData(string $period, ?int $unitId = null): array
    {
        $period = $this->normalizeDashboardPeriod($period);
        $range = $this->dashboardPeriodRange($period);
        $summary = $this->dashboardSummary($period, $unitId);
        $chart = $this->dashboardChart($period, $unitId);

        return [
            'period' => $period,
            'income' => $summary['income'],
            'expenses' => $summary['expenses'],
            'netIncome' => $summary['net'],
            'chartLabels' => $chart['labels'],
            'incomeChart' => $chart['income'],
            'expenseChart' => $chart['expenses'],
            'periodLabel' => $range['label'],
            'periodRange' => $range['range'],
        ];
    }

    /**
     * Push fresh chart buckets to the recap chart after the period changes.
     */
    protected function refreshDashboardChart(string $period, ?int $unitId = null): void
    {
        $chart = $this->dashboardChart($period, $unitId);

        $this->dispatch('recap-chart-update', labels: $chart['labels'], income: $chart['income'], expenses: $chart['expenses']);
    }

    /**
     * Resolve the date range for a dashboard period filter.
     *
     * @return array{start: Carbon, end: Carbon, label: string, range: string}
     */
    protected function dashboardPeriodRange(string $period): array
    {
        $period = $this->normalizeDashboardPeriod($period);
        $now = Carbon::now();

        $start = $now->copy()->startOfMonth();
        $end = $now->copy()->endOfMonth();
        $label = 'Bulan Ini';

        if ($period === 'mingguan') {
            $start = $now->copy()->startOfWeek();
            $end = $now->copy()->endOfWeek();
            $label = 'Minggu Ini';
        } elseif ($period === 'semester') {
            if ($now->month <= 6) {
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->month(6)->endOfMonth();
                $label = 'Semester 1';
            } else {
                $start = $now->copy()->month(7)->startOfMonth();
                $end = $now->copy()->endOfYear();
                $label = 'Semester 2';
            }
        } elseif ($period === 'tahunan') {
            $start = $now->copy()->startOfYear();
            $end = $now->copy()->endOfYear();
            $label = 'Tahun Ini';
        }

        return [
            'start' => $start,
            'end' => $end,
            'label' => $label,
            'range' => $this->dashboardRangeText($start, $end, $period),
        ];
    }

    /**
     * Summarize income, expenses, and net income for a period.
     *
     * @return array{income: float, expenses: float, net: float}
     */
    protected function dashboardSummary(string $period, ?int $unitId = null): array
    {
        $range = $this->dashboardPeriodRange($period);
        $between = [$range['start']->format('Y-m-d'), $range['end']->format('Y-m-d')];

        $income = JournalEntry::whereHas('account', function ($q) {
            $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
        })->when($unitId, fn ($q) => $q->where('business_unit_id', $unitId))
            ->whereBetween('transaction_date', $between)
            ->sum('credit');

        $expenses = JournalEntry::whereHas('account', function ($q) {
            $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
        })->when($unitId, fn ($q) => $q->where('business_unit_id', $unitId))
            ->whereBetween('transaction_date', $between)
            ->sum('debit');

        return [
            'income' => (float) $income,
            'expenses' => (float) $expenses,
            'net' => (float) $income - (float) $expenses,
        ];
    }

    /**
     * Build chart buckets for a period: daily for weekly/monthly, monthly for semester/yearly.
     *
     * @return array{labels: array<int, string>, income: array<int, float>, expenses: array<int, float>}
     */
    protected function dashboardChart(string $period, ?int $unitId = null): array
    {
        $period = $this->normalizeDashboardPeriod($period);
        $range = $this->dashboardPeriodRange($period);
        $start = $range['start']->copy()->startOfDay();
        $end = $range['end']->copy()->endOfDay();
        $between = [$start->format('Y-m-d'), $end->format('Y-m-d')];

        $keys = [];
        $labels = [];

        if ($period === 'mingguan' || $period === 'bulanan') {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m-d');
                $labels[] = $period === 'mingguan'
                    ? $this->dashboardDayLabel($cursor->dayOfWeek).' '.$cursor->day
                    : (string) $cursor->day;
                $cursor->addDay();
            }
            $keyFormat = 'Y-m-d';
        } else {
            $cursor = $start->copy()->startOfMonth();
            $last = $end->copy()->endOfMonth();
            while ($cursor->lte($last)) {
                $keys[] = $cursor->format('Y-m');
                $labels[] = $this->dashboardMonthLabel($cursor->month);
                $cursor->addMonth();
            }
            $keyFormat = 'Y-m';
        }

        $incomeTotals = $this->sumJournalByBucket($between, 'income', $unitId, $keyFormat);
        $expenseTotals = $this->sumJournalByBucket($between, 'expenses', $unitId, $keyFormat);

        $income = [];
        $expenses = [];

        foreach ($keys as $key) {
            $income[] = (float) ($incomeTotals[$key] ?? 0);
            $expenses[] = (float) ($expenseTotals[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
        ];
    }

    /**
     * @param  array<int, string>  $between
     * @return array<string, float>
     */
    private function sumJournalByBucket(array $between, string $type, ?int $unitId, string $keyFormat): array
    {
        $column = $type === 'income' ? 'credit' : 'debit';

        $rows = JournalEntry::whereHas('account', function ($q) use ($type) {
            if ($type === 'income') {
                $q->where('code', 'like', '4-%')->orWhere('code', 'like', '7-%');
            } else {
                $q->where('code', 'like', '5-%')->orWhere('code', 'like', '6-%');
            }
        })->when($unitId, fn ($q) => $q->where('business_unit_id', $unitId))
            ->whereBetween('transaction_date', $between)
            ->get(['transaction_date', $column]);

        $totals = [];

        foreach ($rows as $row) {
            $key = Carbon::parse($row->transaction_date)->format($keyFormat);
            $totals[$key] = ($totals[$key] ?? 0) + (float) $row->{$column};
        }

        return $totals;
    }

    private function dashboardRangeText(Carbon $start, Carbon $end, string $period): string
    {
        $day = fn (Carbon $d) => $d->day.' '.$this->dashboardMonthLabel($d->month);

        return match ($period) {
            'mingguan' => $day($start).' – '.$day($end).' '.$end->year,
            'bulanan' => $start->day.' – '.$end->day.' '.$this->dashboardMonthLabel($start->month).' '.$start->year,
            'semester' => $this->dashboardMonthLabel($start->month).' – '.$this->dashboardMonthLabel($end->month).' '.$end->year,
            default => (string) $start->year,
        };
    }

    private function dashboardMonthLabel(int $month): string
    {
        return [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'][$month] ?? '';
    }

    private function dashboardDayLabel(int $dayOfWeek): string
    {
        return [0 => 'Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][$dayOfWeek] ?? '';
    }
}
