<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ActivityLog;
use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        $totalUsers = User::count();
        $totalUnits = BusinessUnit::count();
        $totalRoles = Role::count();
        $todayActivities = ActivityLog::whereDate('created_at', today())->count();

        // User activity monitoring chart data (last 7 days)
        $chartDates = [];
        $chartActivityCounts = [];
        $chartImpersonateCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateString = $date->toDateString();

            $chartDates[] = $date->translatedFormat('d M');
            $chartActivityCounts[] = ActivityLog::whereDate('created_at', $dateString)->count();
            $chartImpersonateCounts[] = ActivityLog::whereDate('created_at', $dateString)
                ->where('activity_type', 'like', 'impersonate%')
                ->count();
        }

        return view('livewire.super-admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalUnits' => $totalUnits,
            'totalRoles' => $totalRoles,
            'todayActivities' => $todayActivities,
            'chartDates' => $chartDates,
            'chartActivityCounts' => $chartActivityCounts,
            'chartImpersonateCounts' => $chartImpersonateCounts,
        ])->layout('layouts.app', ['title' => 'Dashboard Super Admin']);
    }
}
