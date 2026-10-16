<?php

namespace App\Livewire\BumdesDirector;

use App\Traits\DashboardChartData;
use Illuminate\Contracts\View\View;
use Livewire\Component;

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
        return view('livewire.bumdes-director.dashboard', $this->dashboardViewData($this->period))
            ->layout('layouts.app', ['title' => 'Dashboard']);
    }
}
