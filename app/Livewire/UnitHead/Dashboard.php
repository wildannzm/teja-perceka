<?php

namespace App\Livewire\UnitHead;

use App\Models\DailyTransaction;
use App\Models\BusinessUnit;
use App\Traits\DashboardChartData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard Kepala Unit')]
class Dashboard extends Component
{
    use DashboardChartData;

    #[Locked]
    public ?int $unitId = null;

    public ?BusinessUnit $unit = null;

    public bool $isCurrentPeriodSubmitted = false;

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->business_unit_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->business_unit_id;
        $this->unit = BusinessUnit::findOrFail($this->unitId);

        $today = Carbon::today();
        $isWeekly = $this->unit->input_frequency === 'weekly';

        if ($isWeekly) {
            // For weekly units (TPS), show the alert only when there is no transaction
            // at all within the current week (any entry in the week is sufficient).
            $this->isCurrentPeriodSubmitted = DailyTransaction::where('business_unit_id', $this->unitId)
                ->whereBetween('transaction_date', [
                    $today->copy()->startOfWeek()->format('Y-m-d'),
                    $today->copy()->endOfWeek()->format('Y-m-d'),
                ])
                ->exists();
        } else {
            $this->isCurrentPeriodSubmitted = DailyTransaction::where('business_unit_id', $this->unitId)
                ->whereDate('transaction_date', $today->format('Y-m-d'))
                ->exists();
        }
    }

    public function getTodayIncomeProperty(): float
    {
        return DailyTransaction::where('business_unit_id', $this->unitId)
            ->whereDate('transaction_date', Carbon::today())
            ->sum('total_income');
    }

    public function getThisWeekIncomeProperty(): float
    {
        return DailyTransaction::where('business_unit_id', $this->unitId)
            ->whereBetween('transaction_date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->sum('total_income');
    }

    public function getThisMonthIncomeProperty(): float
    {
        return DailyTransaction::where('business_unit_id', $this->unitId)
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('total_income');
    }

    public function getThisYearIncomeProperty(): float
    {
        return DailyTransaction::where('business_unit_id', $this->unitId)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->sum('total_income');
    }

    public function getLatestTransactionsProperty()
    {
        return DailyTransaction::where('business_unit_id', $this->unitId)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();
    }

    public function getThisMonthTransactionCountProperty(): int
    {
        return DailyTransaction::where('business_unit_id', $this->unitId)
            ->whereMonth('transaction_date', Carbon::now()->month)
            ->whereYear('transaction_date', Carbon::now()->year)
            ->count();
    }

    public function render()
    {
        $chartData = $this->getChartData($this->unitId);

        return view('livewire.unit-head.dashboard', [
            'incomeChart' => $chartData['income'],
            'expenseChart' => $chartData['expenses'],
        ]);
    }
}
