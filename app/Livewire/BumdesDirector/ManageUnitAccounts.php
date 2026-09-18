<?php

namespace App\Livewire\BumdesDirector;

use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Akun Kepala Unit')]
class ManageUnitAccounts extends Component
{
    // Edit state
    public ?int $editingUserId = null;

    public string $editName = '';

    public ?int $editBusinessUnitId = null;

    public string $editPassword = '';

    // Reset password state
    public ?int $resetPasswordUserId = null;

    public ?string $generatedPassword = null;

    public function mount(): void
    {
        if (! Auth::user()->hasRole('direktur_bumdes')) {
            abort(403, 'Akses ditolak.');
        }
    }

    // ── EDIT USER ─────────────────────────────────────────────────────────────

    public function startEdit(int $userId): void
    {
        $user = User::role('kepala_unit')->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editBusinessUnitId = $user->business_unit_id;
        $this->editPassword = '';
        $this->generatedPassword = null;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingUserId', 'editName', 'editBusinessUnitId', 'editPassword']);
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editBusinessUnitId' => 'nullable|exists:business_units,id',
            'editPassword' => 'nullable|string|min:8',
        ]);

        $user = User::role('kepala_unit')->findOrFail($this->editingUserId);

        $data = [
            'name' => $this->editName,
            'business_unit_id' => $this->editBusinessUnitId,
        ];

        if (! empty($this->editPassword)) {
            $data['password'] = $this->editPassword;
        }

        $user->update($data);

        \Flux::toast(variant: 'success', text: 'Data akun berhasil diperbarui.');
        $this->cancelEdit();
    }

    // ── RESET PASSWORD ─────────────────────────────────────────────────────────

    public function startResetPassword(int $userId): void
    {
        $this->resetPasswordUserId = $userId;
        $this->generatedPassword = null;
        $this->cancelEdit();
    }

    public function generatePassword(): void
    {
        $user = User::role('kepala_unit')->findOrFail($this->resetPasswordUserId);

        $newPassword = Str::random(10);
        $user->update(['password' => $newPassword]);

        $this->generatedPassword = $newPassword;
    }

    public function cancelResetPassword(): void
    {
        $this->reset(['resetPasswordUserId', 'generatedPassword']);
    }

    // ── TOGGLE STATUS ─────────────────────────────────────────────────────────

    public function toggleStatus(int $userId): void
    {
        $user = User::role('kepala_unit')->findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        \Flux::toast(variant: 'success', text: "Akun {$user->name} berhasil {$status}.");
    }

    // ── RENDER ────────────────────────────────────────────────────────────────

    public function render()
    {
        $users = User::role('kepala_unit')
            ->with('businessUnit')
            ->orderBy('name')
            ->get();

        $units = BusinessUnit::orderBy('name')->get();

        return view('livewire.bumdes-director.manage-unit-accounts', [
            'users' => $users,
            'units' => $units,
        ]);
    }
}
