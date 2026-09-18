<?php

namespace App\Livewire\UnitHead;

use App\Enums\TransactionType;
use App\Enums\CategoryType;
use App\Models\CategoryPriceHistory;
use App\Models\TransactionCategory;
use App\Models\Account;
use App\Models\TransactionItem;
use App\Models\User;
use App\Notifications\CategoryPriceUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Pendapatan')]
class ManageRevenue extends Component
{
    public int $unitId;

    public string $unitName;

    /** @var array<int, float|string> harga per kategori (untuk edit harga existing) */
    public array $prices = [];

    // ─── Form tambah kategori baru ────────────────────────────────────────
    public string $categoryName = '';

    public string $categoryType = 'harga_x_qty';

    public string $categoryPrice = '';

    public ?int $categoryAccountId = null;

    public bool $showCreateForm = false;

    // ─── Form edit kategori ────────────────────────────────────────────────
    public bool $showEditModal = false;

    public ?int $editId = null;

    public string $editCategoryName = '';

    public string $editCategoryType = 'harga_x_qty';

    public ?int $editCategoryAccountId = null;

    // ─── Form hapus kategori ───────────────────────────────────────────────
    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

    public string $deleteCategoryName = '';

    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->business_unit_id) {
            abort(403, 'Akses ditolak.');
        }

        $this->unitId = $user->business_unit_id;
        $this->unitName = $user->businessUnit->name;

        $this->loadPrices();
    }

    private function loadPrices(): void
    {
        $categories = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('direction', TransactionType::Income)
            ->where('type', '!=', CategoryType::Custom->value)
            ->get();

        foreach ($categories as $category) {
            $this->prices[$category->id] = $category->priceAt(now());
        }
    }

    // ─── Update harga kategori existing ──────────────────────────────────

    public function updatePrice(int $categoryId): void
    {
        $category = TransactionCategory::where('id', $categoryId)
            ->where('business_unit_id', $this->unitId)
            ->firstOrFail();

        $newPrice = (float) str_replace(['Rp', '.', ',', ' '], '', $this->prices[$categoryId] ?? '0');

        if ($newPrice <= 0) {
            $this->addError('prices.'.$categoryId, 'Harga harus berupa angka positif.');

            return;
        }

        $oldPrice = $category->priceAt(now());

        if ($newPrice === $oldPrice) {
            $this->addError('prices.'.$categoryId, 'Harga tidak berubah.');

            return;
        }

        CategoryPriceHistory::create([
            'transaction_category_id' => $category->id,
            'price' => $newPrice,
            'effective_from' => now(),
        ]);

        $message = "Kepala Unit {$this->unitName} mengubah harga {$category->name} dari Rp "
            .number_format($oldPrice, 0, ',', '.').' menjadi Rp '.number_format($newPrice, 0, ',', '.');

        $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new CategoryPriceUpdated($message));
        }

        session()->flash('success_'.$categoryId, 'Harga berhasil diperbarui!');
        $this->prices[$categoryId] = $newPrice;
        $this->resetErrorBag();
    }

    // ─── Tambah kategori baru ─────────────────────────────────────────────

    public function updatedCategoryType(): void
    {
        // Reset the price when switching to the free type
        if ($this->categoryType === 'bebas') {
            $this->categoryPrice = '';
        }
    }

    public function createCategory(): void
    {
        $rules = [
            'categoryName' => 'required|string|max:100',
            'categoryType' => 'required|in:harga_x_qty,tahunan,bebas',
            'categoryAccountId' => 'required|exists:accounts,id',
        ];

        if (in_array($this->categoryType, ['harga_x_qty', 'tahunan'])) {
            $rules['categoryPrice'] = 'required|numeric|min:0';
        }

        $this->validate($rules, [
            'categoryName.required' => 'Nama kategori wajib diisi.',
            'categoryType.required' => 'Tipe kategori wajib dipilih.',
            'categoryAccountId.required' => 'Pilih akun pendapatan untuk kategori ini.',
            'categoryPrice.required' => 'Harga atau nominal wajib diisi untuk tipe ini.',
            'categoryPrice.min' => 'Harga tidak boleh negatif.',
        ]);

        // Check for duplicate names within the same unit
        $duplicate = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('name', $this->categoryName)
            ->exists();

        if ($duplicate) {
            $this->addError('categoryName', 'Nama kategori sudah ada di unit ini.');

            return;
        }

        DB::transaction(function () {
            $category = TransactionCategory::create([
                'business_unit_id' => $this->unitId,
                'account_id' => $this->categoryAccountId,
                'name' => $this->categoryName,
                'type' => CategoryType::from($this->categoryType),
                'direction' => TransactionType::Income,
            ]);

            // Store the initial price unless the type is free
            if (in_array($this->categoryType, ['harga_x_qty', 'tahunan'])) {
                CategoryPriceHistory::create([
                    'transaction_category_id' => $category->id,
                    'price' => (float) $this->categoryPrice,
                    'effective_from' => now()->startOfDay(),
                ]);

                // Append to the prices array so it shows up in the price-edit list immediately
                $this->prices[$category->id] = (float) $this->categoryPrice;
            }

            // Notify the treasurer & director
            $account = Account::find($this->categoryAccountId);
            $message = "Kepala Unit {$this->unitName} menambahkan kategori pendapatan baru: "
                ."\"{$this->categoryName}\" (Tipe: {$this->categoryType}, Akun: {$account?->code} {$account?->name})";

            $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new CategoryPriceUpdated($message));
            }
        });

        \Flux::toast(variant: 'success', text: "Kategori \"{$this->categoryName}\" berhasil ditambahkan!");

        // Reset the form
        $this->reset(['categoryName', 'categoryPrice', 'categoryAccountId']);
        $this->categoryType = 'harga_x_qty';
        $this->showCreateForm = false;
    }

    // ─── Edit Kategori ───────────────────────────────────────────────────

    public function editCategory(int $id): void
    {
        $category = TransactionCategory::where('id', $id)
            ->where('business_unit_id', $this->unitId)
            ->firstOrFail();

        $this->editId = $category->id;
        $this->editCategoryName = $category->name;
        $this->editCategoryType = $category->type->value;
        $this->editCategoryAccountId = $category->account_id;

        $this->showEditModal = true;
    }

    public function saveCategoryEdit(): void
    {
        $this->validate([
            'editCategoryName' => 'required|string|max:100',
            'editCategoryType' => 'required|in:harga_x_qty,tahunan,bebas',
            'editCategoryAccountId' => 'required|exists:accounts,id',
        ], [
            'editCategoryName.required' => 'Nama kategori wajib diisi.',
            'editCategoryType.required' => 'Tipe kategori wajib dipilih.',
            'editCategoryAccountId.required' => 'Pilih akun pendapatan.',
        ]);

        $duplicate = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('name', $this->editCategoryName)
            ->where('id', '!=', $this->editId)
            ->exists();

        if ($duplicate) {
            $this->addError('editCategoryName', 'Nama kategori sudah ada di unit ini.');

            return;
        }

        $category = TransactionCategory::findOrFail($this->editId);

        $oldName = $category->name;

        $category->update([
            'name' => $this->editCategoryName,
            'type' => CategoryType::from($this->editCategoryType),
            'account_id' => $this->editCategoryAccountId,
        ]);

        // Notify about the change when the name changes
        if ($oldName !== $this->editCategoryName) {
            $message = "Kepala Unit {$this->unitName} mengubah kategori \"{$oldName}\" menjadi \"{$this->editCategoryName}\".";
            $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new CategoryPriceUpdated($message));
            }
        }

        \Flux::toast(variant: 'success', text: 'Kategori berhasil diperbarui!');

        $this->showEditModal = false;
        $this->reset(['editId', 'editCategoryName', 'editCategoryType', 'editCategoryAccountId']);
        $this->loadPrices();
    }

    public function confirmDelete(int $id): void
    {
        $category = TransactionCategory::where('id', $id)
            ->where('business_unit_id', $this->unitId)
            ->firstOrFail();

        $this->deleteId = $category->id;
        $this->deleteCategoryName = $category->name;
        $this->showDeleteModal = true;
    }

    public function deleteCategory(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $category = TransactionCategory::where('id', $this->deleteId)
            ->where('business_unit_id', $this->unitId)
            ->firstOrFail();

        // Check usage in transaction details (undeletable when referenced, to prevent data loss)
        $isUsed = TransactionItem::where('transaction_category_id', $this->deleteId)->exists();

        if ($isUsed) {
            \Flux::toast(variant: 'danger', text: 'Kategori tidak bisa dihapus karena sudah dipakai dalam history transaksi!');
            $this->showDeleteModal = false;

            return;
        }

        $id = $this->deleteId;
        $category->delete();
        \Flux::toast(variant: 'success', text: 'Kategori berhasil dihapus!');

        unset($this->prices[$id]);
        $this->loadPrices();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteCategoryName = '';
    }

    // ─── Render ──────────────────────────────────────────────────────────

    public function render()
    {
        $categories = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('direction', TransactionType::Income)
            ->orderBy('name')
            ->get();

        $revenueAccounts = Account::where('type', 'pendapatan')
            ->orderBy('code')
            ->get();

        return view('livewire.unit-head.manage-revenue', [
            'categories' => $categories,
            'revenueAccounts' => $revenueAccounts,
        ]);
    }
}
