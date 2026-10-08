<?php

namespace App\Livewire\VillageHead;

use App\Models\BusinessUnit;
use App\Models\User;
use App\Support\RoleLabels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class UserManager extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public $editingUserId = null;

    public $name = '';

    public $email = '';

    public string $editRole = '';

    public $editBusinessUnitId = null;

    public string $editPassword = '';

    public string $editPassword_confirmation = '';

    public bool $showCreateModal = false;

    public string $createName = '';

    public string $createEmail = '';

    public string $createPassword = '';

    public string $createPassword_confirmation = '';

    public string $createRole = '';

    public $createBusinessUnitId = null;

    public ?int $deleteId = null;

    public bool $showDeleteModal = false;

    /**
     * Users a village head may manage: same boundary as the list —
     * privileged roles are never editable here, even by guessed ID.
     */
    private function manageableUsers()
    {
        return User::whereHas('roles', function ($query) {
            $query->whereNotIn('name', ['kepala_desa', 'pengawas', 'super_admin']);
        });
    }

    public function editUser($userId)
    {
        $user = $this->manageableUsers()->with('roles')->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->editRole = $user->roles->first()?->name ?? '';
        $this->editBusinessUnitId = $user->business_unit_id;
        $this->editPassword = '';
        $this->editPassword_confirmation = '';
        $this->resetErrorBag();
    }

    public function cancelEdit()
    {
        $this->reset(['editingUserId', 'name', 'email', 'editRole', 'editBusinessUnitId', 'editPassword', 'editPassword_confirmation']);
    }

    public function saveUser()
    {
        try {
            $validated = $this->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,'.$this->editingUserId,
                'editRole' => ['required', Rule::in(['direktur_bumdes', 'sekretaris', 'bendahara', 'kepala_unit'])],
                'editBusinessUnitId' => 'nullable|exists:business_units,id',
                'editPassword' => 'nullable|string|min:8|confirmed',
            ], [], [
                'editRole' => 'hak akses',
                'editBusinessUnitId' => 'unit usaha',
                'editPassword' => 'password baru',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        if ($validated['editRole'] === 'kepala_unit' && empty($validated['editBusinessUnitId'])) {
            $this->addError('editBusinessUnitId', 'Unit usaha wajib dipilih untuk Kepala Unit.');
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: 'Unit usaha wajib dipilih untuk Kepala Unit.');

            return;
        }

        if ($this->editingUserId) {
            $user = $this->manageableUsers()->findOrFail($this->editingUserId);
            $data = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'business_unit_id' => $validated['editRole'] === 'kepala_unit' ? $validated['editBusinessUnitId'] : null,
            ];

            if (! empty($validated['editPassword'])) {
                $data['password'] = $validated['editPassword'];
            }

            $user->update($data);
            $user->syncRoles([$validated['editRole']]);
        }

        $this->reset(['editingUserId', 'name', 'email', 'editRole', 'editBusinessUnitId', 'editPassword', 'editPassword_confirmation']);

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Data pengguna berhasil diperbarui.');
    }

    public function openCreate(): void
    {
        $this->reset(['createName', 'createEmail', 'createPassword', 'createPassword_confirmation', 'createRole', 'createBusinessUnitId']);
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->reset(['showCreateModal', 'createName', 'createEmail', 'createPassword', 'createPassword_confirmation', 'createRole', 'createBusinessUnitId']);
        $this->resetErrorBag();
    }

    public function storeUser(): void
    {
        try {
            $validated = $this->validate([
                'createName' => 'required|string|max:255',
                'createEmail' => 'required|email|max:255|unique:users,email',
                'createPassword' => 'required|string|min:8|confirmed',
                'createRole' => ['required', Rule::in(['direktur_bumdes', 'sekretaris', 'bendahara', 'kepala_unit'])],
                'createBusinessUnitId' => 'nullable|exists:business_units,id',
            ], [], [
                'createName' => 'nama',
                'createEmail' => 'email',
                'createPassword' => 'password',
                'createRole' => 'hak akses',
                'createBusinessUnitId' => 'unit usaha',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: (string) $e->validator->errors()->first());

            throw $e;
        }

        if ($validated['createRole'] === 'kepala_unit' && empty($validated['createBusinessUnitId'])) {
            $this->addError('createBusinessUnitId', 'Unit usaha wajib dipilih untuk Kepala Unit.');
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: 'Unit usaha wajib dipilih untuk Kepala Unit.');

            return;
        }

        $user = User::create([
            'name' => $validated['createName'],
            'email' => $validated['createEmail'],
            'password' => $validated['createPassword'],
            'business_unit_id' => $validated['createRole'] === 'kepala_unit' ? $validated['createBusinessUnitId'] : null,
            'is_active' => true,
        ]);
        $user->assignRole($validated['createRole']);

        $this->closeCreate();
        $this->resetPage();

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Pengguna baru berhasil ditambahkan.');
    }

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
        $this->resetPage();

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Pengguna berhasil dihapus.');
    }

    public function render()
    {
        [$roleOrder, $bindings] = RoleLabels::caseSql(User::class, ['direktur_bumdes', 'sekretaris', 'bendahara', 'kepala_unit']);

        $users = $this->manageableUsers()
            ->with(['roles', 'businessUnit'])
            ->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            })
            ->orderByRaw($roleOrder, $bindings)
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.village-head.user-manager', [
            'users' => $users,
            'units' => BusinessUnit::orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'Kelola Akun']);
    }
}
