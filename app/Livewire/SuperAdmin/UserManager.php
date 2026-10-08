<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use App\Support\RoleLabels;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $selectedRole = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedRole(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        // ponytail: CASE sort in SQL, single source in RoleLabels
        [$roleOrder, $bindings] = RoleLabels::caseSql(User::class);

        $users = User::with(['roles', 'businessUnit'])
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->selectedRole !== '', function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->selectedRole);
                });
            })
            ->orderByRaw($roleOrder, $bindings)
            ->orderBy('name', 'asc')
            ->paginate(12);

        $roles = RoleLabels::existingOrdered();

        return view('livewire.super-admin.user-manager', [
            'users' => $users,
            'roles' => $roles,
        ])->layout('layouts.app', ['title' => 'Manajemen User Super Admin']);
    }
}
