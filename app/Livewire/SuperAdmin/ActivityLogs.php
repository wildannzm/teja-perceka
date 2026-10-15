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

    public bool $showClearModal = false;

    public string $clearMode = 'filtered';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActivityType(): void
    {
        $this->resetPage();
    }

    private function baseQuery()
    {
        return ActivityLog::with('user')
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
            });
    }

    public function confirmClear(string $mode): void
    {
        $this->clearMode = $mode === 'all' ? 'all' : 'filtered';
        $this->showClearModal = true;
    }

    public function executeClear(): void
    {
        $deleted = $this->clearMode === 'all'
            ? ActivityLog::query()->delete()
            : $this->baseQuery()->delete();

        $this->reset(['showClearModal']);
        $this->clearMode = 'filtered';
        $this->resetPage();

        if ($deleted === 0) {
            $this->dispatch('swal-alert', icon: 'info', title: 'Tidak ada data', text: 'Tidak ada log yang dihapus.');

            return;
        }

        ActivityLog::log('logs_cleared', 'Super admin menghapus '.number_format($deleted).' data log aktivitas.', [
            'deleted_count' => $deleted,
            'mode' => $this->clearMode,
        ]);

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: number_format($deleted).' data log berhasil dihapus.');
    }

    public function render()
    {
        $logs = $this->baseQuery()
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
