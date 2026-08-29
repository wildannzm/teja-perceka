<?php

namespace App\Livewire\SuperAdmin;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogs extends Component
{
    use WithPagination;

    public string $search = '';

    public string $activityType = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActivityType(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = ActivityLog::with('user')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%'.$this->search.'%')
                        ->orWhere('ip_address', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', function ($uq) {
                            $uq->where('name', 'like', '%'.$this->search.'%')
                                ->orWhere('email', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->activityType !== '', function ($query) {
                $query->where('activity_type', $this->activityType);
            })
            ->latest()
            ->paginate(15);

        $activityTypes = ActivityLog::select('activity_type')
            ->distinct()
            ->pluck('activity_type');

        return view('livewire.super-admin.activity-logs', [
            'logs' => $logs,
            'activityTypes' => $activityTypes,
        ])->layout('layouts.app', ['title' => 'Log Aktivitas Sistem']);
    }
}
