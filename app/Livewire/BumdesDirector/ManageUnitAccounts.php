<?php

namespace App\Livewire\BumdesDirector;

use App\Models\BusinessUnit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Akun Kepala Unit')]
class ManageUnitAccounts extends Component
{
    // Edit modal state
    public bool $showModal = false;

    public ?int $editingUserId = null;

    public string $editName = '';

    public string $editEmail = '';

    public ?int $editBusinessUnitId = null;

    public string $editPassword = '';

    public string $editPassword_confirmation = '';

    // Create modal state
    public bool $showCreateModal = false;

    public string $createName = '';

    public string $createEmail = '';

    public string $createPassword = '';

    public string $createPassword_confirmation = '';

    public ?int $createBusinessUnitId = null;

    // Delete modal state
    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

    public function mount(): void
    {
        if (! Auth::user()->hasRole('direktur_bumdes')) {
            abort(403, 'Akses ditolak.');
        }
    }

    /**
     * Unit heads manageable here: same boundary as the list.
     */
    private function manageableUsers()
    {
        return User::role('kepala_unit');
    }

    // ── CREATE ──────────────────────────────────────────────────────────────

    public function openCreate(): void
    {
        $this->reset(['createName', 'createEmail', 'createPassword', 'createPassword_confirmation', 'createBusinessUnitId']);
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->reset(['showCreateModal', 'createName', 'createEmail', 'createPassword', 'createPassword_confirmation', 'createBusinessUnitId']);
        $this->resetErrorBag();
    }

    public function storeUser(): void
    {
        try {
            $validated = $this->validate([
                'createName' => 'required|string|max:255',
                'createEmail' => 'required|email|max:255|unique:users,email',
                'createPassword' => 'required|string|min:8|confirmed',
                'createBusinessUnitId' => 'nullable|exists:business_units,id',
            ], [], [
                'createName' => 'nama',
                'createEmail' => 'email',
                'createPassword' => 'password',
                'createBusinessUnitId' => 'unit usaha',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        $user = User::create([
            'name' => $validated['createName'],
            'email' => $validated['createEmail'],
            'password' => $validated['createPassword'],
            'business_unit_id' => $validated['createBusinessUnitId'],
            'is_active' => true,
        ]);
        $user->assignRole('kepala_unit');

        $this->closeCreate();

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Akun Kepala Unit berhasil ditambahkan.');
    }

    // ── EDIT ────────────────────────────────────────────────────────────────

    public function openEdit(int $userId): void
    {
        $user = $this->manageableUsers()->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->editName = $user->name;
        $this->editEmail = $user->email;
        $this->editBusinessUnitId = $user->business_unit_id;
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->reset(['showModal', 'editingUserId', 'editName', 'editEmail', 'editBusinessUnitId', 'editPassword', 'editPassword_confirmation']);
        $this->resetErrorBag();
    }

    public function saveEdit(): void
    {
        try {
            $validated = $this->validate([
                'editName' => 'required|string|max:255',
                'editEmail' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
                'editBusinessUnitId' => 'nullable|exists:business_units,id',
                'editPassword' => 'nullable|string|min:8|confirmed',
            ], [], [
                'editName' => 'nama',
                'editEmail' => 'email',
                'editBusinessUnitId' => 'unit usaha',
                'editPassword' => 'password baru',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        $user = $this->manageableUsers()->findOrFail($this->editingUserId);

        $data = [
            'name' => $validated['editName'],
            'email' => $validated['editEmail'],
            'business_unit_id' => $validated['editBusinessUnitId'],
        ];

        if (! empty($validated['editPassword'])) {
            $data['password'] = $validated['editPassword'];
        }

        $user->update($data);

        $this->closeModal();

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Data akun berhasil diperbarui.');
    }

    // ── DELETE ──────────────────────────────────────────────────────────────

    public function confirmDelete(int $userId): void
    {
        $this->manageableUsers()->findOrFail($userId);
        $this->deleteId = $userId;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->deleteId) {
            abort(403);
        }

        $user = $this->manageableUsers()->findOrFail($this->deleteId);

        if ($user->id === Auth::id()) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal', text: 'Tidak dapat menghapus akun sendiri.');
            $this->reset(['showDeleteModal', 'deleteId']);

            return;
        }

        $user->delete();

        $this->reset(['showDeleteModal', 'deleteId']);

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Akun Kepala Unit berhasil dihapus.');
    }

    // ── RENDER ──────────────────────────────────────────────────────────────

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
