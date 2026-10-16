<?php

namespace App\Livewire\Supervisor;

use App\Traits\DashboardChartData;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use DashboardChartData;

    public string $period = 'bulanan';

    public function updatedPeriod(): void
    {
        $this->period = $this->normalizeDashboardPeriod($this->period);
        $this->refreshDashboardChart($this->period);
    }

    public function render(): View
    {
        return view('livewire.supervisor.dashboard', $this->dashboardViewData($this->period))
            ->layout('layouts.app', ['title' => 'Dashboard Pengawas']);
    }
}
