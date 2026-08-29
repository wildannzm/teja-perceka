<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ActivityLog;
use App\Models\UnitWisata;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Dashboard extends Component
{
    public function render()
    {
        $totalUsers = User::count();
        $totalUnits = UnitWisata::count();
        $totalRoles = Role::count();
        $todayActivities = ActivityLog::whereDate('created_at', today())->count();

        // Data Grafik Monitoring Aktivitas Pengguna (7 Hari Terakhir)
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
