<?php

namespace App\Livewire\UnitHead;

use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Traits\DashboardChartData;
use Illuminate\Contracts\View\View;
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

    public string $period = 'bulanan';

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

    public function updatedPeriod(): void
    {
        $this->period = $this->normalizeDashboardPeriod($this->period);
        $this->refreshDashboardChart($this->period, $this->unitId);
    }

    public function render(): View
    {
        return view('livewire.unit-head.dashboard', $this->dashboardViewData($this->period, $this->unitId));
    }
}
