<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ActivityLog;
use App\Models\UnitWisata;
use App\Models\User;
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

        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::with('roles', 'unitWisata')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.super-admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalUnits' => $totalUnits,
            'totalRoles' => $totalRoles,
            'todayActivities' => $todayActivities,
            'recentActivities' => $recentActivities,
            'recentUsers' => $recentUsers,
        ])->layout('layouts.app', ['title' => 'Dashboard Super Admin']);
    }
}
