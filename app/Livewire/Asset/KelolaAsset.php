<?php

namespace App\Livewire\Asset;

use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Kelola Aset')]
class KelolaAsset extends Component
{
    use WithPagination;

    public string $search = '';

    // Form fields
    public string $nama_aset = '';

    public int $jumlah = 1;

    public string $satuan = 'unit';

    public string $harga = '';

    public string $keterangan = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public ?int $deleteId = null;

    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        return [
            'nama_aset' => 'required|string|max:255',
            'jumlah' => 'required|integer|min:1',
            'satuan' => 'required|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'nama_aset.required' => 'Nama aset wajib diisi.',
            'jumlah.required' => 'Jumlah wajib diisi.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'satuan.required' => 'Satuan wajib diisi.',
            'harga.numeric' => 'Harga harus berupa angka.',
            'harga.min' => 'Harga tidak boleh negatif.',
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
            ->when($this->search, fn ($q) => $q->where('nama_aset', 'like', '%'.$this->search.'%')
                ->orWhere('keterangan', 'like', '%'.$this->search.'%'))
            ->orderBy('nama_aset')
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

        $this->reset(['nama_aset', 'jumlah', 'keterangan', 'harga', 'editingId']);
        $this->jumlah = 1;
        $this->satuan = 'unit';
        $this->harga = '';
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
        $this->nama_aset = $asset->nama_aset;
        $this->jumlah = $asset->jumlah;
        $this->satuan = $asset->satuan;
        $this->harga = $asset->harga > 0 ? (string) $asset->harga : '';
        $this->keterangan = $asset->keterangan ?? '';
        $this->showModal = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! $this->canManage) {
            abort(403);
        }

        $validated = $this->validate();
        // Ensure the price is cast to float, 0 when empty
        $validated['harga'] = (float) ($this->harga ?: 0);

        if ($this->editingId) {
            Asset::findOrFail($this->editingId)->update($validated);
            session()->flash('toast_success', 'Aset berhasil diperbarui.');
        } else {
            Asset::create($validated);
            session()->flash('toast_success', 'Aset berhasil ditambahkan.');
        }

        $this->showModal = false;
        $this->reset(['nama_aset', 'jumlah', 'satuan', 'harga', 'keterangan', 'editingId']);
        $this->jumlah = 1;
        $this->satuan = 'unit';
        $this->harga = '';
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
        $this->reset(['nama_aset', 'jumlah', 'satuan', 'harga', 'keterangan', 'editingId']);
        $this->jumlah = 1;
        $this->satuan = 'unit';
        $this->harga = '';
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.asset.kelola-asset');
    }
}
