<?php

namespace App\Livewire\KepalaDesa;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public $search = '';

    public $editingUserId = null;

    public $name = '';

    public $email = '';

    public function editUser($userId)
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function cancelEdit()
    {
        $this->reset(['editingUserId', 'name', 'email']);
    }

    public function saveUser()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
        ]);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            // Note: Password and Roles are NOT managed here based on user instruction.
        }

        $this->reset(['editingUserId', 'name', 'email']);
    }

    public function render()
    {
        $users = User::with(['roles', 'unitWisata'])
            ->whereHas('roles', function ($query) {
                $query->whereNotIn('name', ['kepala_desa', 'pengawas', 'super_admin']);
            })
            ->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->paginate(10);

        return view('livewire.kepala-desa.user-manager', [
            'users' => $users,
        ])->layout('layouts.app', ['title' => 'Kelola Akun']);
    }
}
