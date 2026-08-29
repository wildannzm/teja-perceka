<?php

namespace App\Livewire\SuperAdmin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

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
        $users = User::with(['roles', 'unitWisata'])
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
            ->orderBy('id', 'asc')
            ->paginate(12);

        $roles = Role::pluck('name');

        return view('livewire.super-admin.user-manager', [
            'users' => $users,
            'roles' => $roles,
        ])->layout('layouts.app', ['title' => 'Manajemen User Super Admin']);
    }
}
