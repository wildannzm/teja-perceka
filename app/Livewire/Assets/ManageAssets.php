<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Support\Rupiah;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kelola Aset')]
class ManageAssets extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public string $name = '';

    public int $quantity = 1;

    public string $unit = 'unit';

    public string $price = '';

    public string $description = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $deleteId = null;

    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nama aset wajib diisi.',
            'quantity.required' => 'Jumlah wajib diisi.',
            'quantity.min' => 'Jumlah minimal 1.',
            'unit.required' => 'Satuan wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
        ];
    }

    #[Computed]
    public function canManage(): bool
    {
        return Auth::user()->hasAnyRole(['direktur_bumdes', 'sekretaris', 'bendahara']);
    }

    #[Computed]
    public function assets()
    {
        return Asset::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        if (! $this->canManage) {
            abort(403);
        }

        $this->reset(['name', 'quantity', 'description', 'price', 'editingId']);
        $this->quantity = 1;
        $this->unit = 'unit';
        $this->price = '';
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $id): void
    {
        if (! $this->canManage) {
            abort(403);
        }

        $asset = Asset::findOrFail($id);
        $this->editingId = $asset->id;
        $this->name = $asset->name;
        $this->quantity = $asset->quantity;
        $this->unit = $asset->unit;
        $this->price = $asset->price > 0 ? (string) $asset->price : '';
        $this->description = $asset->description ?? '';
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! $this->canManage) {
            abort(403);
        }

        $this->price = (string) Rupiah::parse($this->price);

        $validated = $this->validate();
        // Ensure the price is cast to float, 0 when empty
        $validated['price'] = (float) ($this->price ?: 0);

        if ($this->editingId) {
            Asset::findOrFail($this->editingId)->update($validated);
            session()->flash('toast_success', 'Aset berhasil diperbarui.');
        } else {
            Asset::create($validated);
            session()->flash('toast_success', 'Aset berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['name', 'quantity', 'unit', 'price', 'description', 'editingId']);
        $this->quantity = 1;
        $this->unit = 'unit';
        $this->price = '';
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->canManage || ! $this->deleteId) {
            abort(403);
        }

        Asset::findOrFail($this->deleteId)->delete();
        session()->flash('toast_success', 'Aset berhasil dihapus.');

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['name', 'quantity', 'unit', 'price', 'description', 'editingId']);
        $this->quantity = 1;
        $this->unit = 'unit';
        $this->price = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.assets.manage-assets');
    }
}
