<?php

namespace App\Livewire\UnitHead;

use App\Enums\CategoryType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\CategoryPriceHistory;
use App\Models\TransactionCategory;
use App\Models\TransactionItem;
use App\Models\User;
use App\Notifications\CategoryPriceUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Pendapatan')]
class ManageRevenue extends Component
{
    #[Locked]
    public int $unitId;

    #[Locked]
    public string $unitName;

    private const MAX_PRICE = 999999999999;

    /** @var array<int, string> price per category, formatted id-ID (dots per 3 digits) */
    public array $prices = [];

    // ─── New category form ──────────────────────────────────────────────
    public string $categoryName = '';

    public string $categoryType = 'harga_x_qty';

    public string $categoryPrice = '';

    public ?int $categoryAccountId = null;

    public bool $showCreateForm = false;

    // ─── Edit category form ─────────────────────────────────────────────
    public bool $showEditModal = false;

    #[Locked]
    public ?int $editId = null;

    public string $editCategoryName = '';

    public string $editCategoryType = 'harga_x_qty';

    public ?int $editCategoryAccountId = null;

    // ─── Delete category form ───────────────────────────────────────────
    public bool $showDeleteModal = false;

    #[Locked]
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

    private static function sanitizeRupiah(string|int|float|null $value): float
    {
        return (float) preg_replace('/\D/', '', (string) ($value ?? ''));
    }

    private static function formatRupiah(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    private function loadPrices(): void
    {
        $categories = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('direction', TransactionType::Income)
            ->where('type', '!=', CategoryType::Custom->value)
            ->get();

        foreach ($categories as $category) {
            $this->prices[$category->id] = self::formatRupiah($category->priceAt(now()));
        }
    }

    // ─── Update existing category prices ─────────────────────────────────

    public function updatePrice(int $categoryId): void
    {
        $category = TransactionCategory::where('id', $categoryId)
            ->where('business_unit_id', $this->unitId)
            ->firstOrFail();

        $newPrice = self::sanitizeRupiah($this->prices[$categoryId] ?? null);

        if ($newPrice <= 0) {
            $this->addError('prices.'.$categoryId, 'Harga harus berupa angka positif.');

            return;
        }

        if ($newPrice > self::MAX_PRICE) {
            $this->addError('prices.'.$categoryId, 'Harga melebihi batas wajar.');

            return;
        }

        $oldPrice = $category->priceAt(now());

        if ($newPrice === $oldPrice) {
            $this->addError('prices.'.$categoryId, 'Harga tidak berubah.');

            return;
        }

        DB::transaction(function () use ($category, $oldPrice, $newPrice) {
            CategoryPriceHistory::create([
                'transaction_category_id' => $category->id,
                'price' => $newPrice,
                'effective_from' => now(),
            ]);

            $this->notifyPriceRoles(
                "Kepala Unit {$this->unitName} mengubah harga {$category->name} dari Rp "
                .self::formatRupiah($oldPrice).' menjadi Rp '.self::formatRupiah($newPrice)
            );
        });

        session()->flash('success_'.$categoryId, 'Harga berhasil diperbarui!');
        $this->prices[$categoryId] = self::formatRupiah($newPrice);
        $this->resetErrorBag();
    }

    // ─── Create new category ────────────────────────────────────────────

    private function notifyPriceRoles(string $message): void
    {
        User::role(['bendahara', 'direktur_bumdes'])
            ->each(fn (User $recipient) => $recipient->notify(new CategoryPriceUpdated($message)));
    }

    public function updatedCategoryType(): void
    {
        // Reset the price when switching to the free type
        if ($this->categoryType === CategoryType::Custom->value) {
            $this->categoryPrice = '';
        }
    }

    public function createCategory(): void
    {
        $this->categoryPrice = (string) self::sanitizeRupiah($this->categoryPrice);

        $rules = [
            'categoryName' => 'required|string|max:100',
            'categoryType' => ['required', Rule::in(CategoryType::manageableValues())],
            'categoryAccountId' => 'required|exists:accounts,id',
        ];

        if ($this->categoryType !== CategoryType::Custom->value) {
            $rules['categoryPrice'] = 'required|numeric|min:1|max:'.self::MAX_PRICE;
        }

        $this->validate($rules, [
            'categoryName.required' => 'Nama kategori wajib diisi.',
            'categoryType.required' => 'Tipe kategori wajib dipilih.',
            'categoryAccountId.required' => 'Pilih akun pendapatan untuk kategori ini.',
            'categoryPrice.required' => 'Harga atau nominal wajib diisi untuk tipe ini.',
            'categoryPrice.min' => 'Harga tidak boleh negatif.',
            'categoryPrice.max' => 'Harga melebihi batas wajar.',
        ]);

        $account = Account::where('id', $this->categoryAccountId)
            ->where('type', 'pendapatan')
            ->first();

        if (! $account) {
            $this->addError('categoryAccountId', 'Pilih akun pendapatan untuk kategori ini.');

            return;
        }

        // Check for duplicate names within the same unit
        $duplicate = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('name', $this->categoryName)
            ->exists();

        if ($duplicate) {
            $this->addError('categoryName', 'Nama kategori sudah ada di unit ini.');

            return;
        }

        $categoryType = CategoryType::from($this->categoryType);
        $price = (float) $this->categoryPrice;
        $notifyMessage = '';

        DB::transaction(function () use ($account, $categoryType, $price, &$notifyMessage) {
            $category = TransactionCategory::create([
                'business_unit_id' => $this->unitId,
                'account_id' => $account->id,
                'name' => $this->categoryName,
                'type' => $categoryType,
                'direction' => TransactionType::Income,
            ]);

            // Store the initial price unless the type is free
            if ($categoryType->needsPrice()) {
                CategoryPriceHistory::create([
                    'transaction_category_id' => $category->id,
                    'price' => $price,
                    'effective_from' => now()->startOfDay(),
                ]);

                // Append to the prices array so it shows up in the price-edit list immediately
                $this->prices[$category->id] = self::formatRupiah($price);
            }

            // Notify the treasurer & director
            $notifyMessage = "Kepala Unit {$this->unitName} menambahkan kategori pendapatan baru: "
                ."\"{$this->categoryName}\" (Tipe: {$categoryType->label()}, Akun: {$account->code} {$account->name})";
        });

        $this->notifyPriceRoles($notifyMessage);

        \Flux::toast(variant: 'success', text: "Kategori \"{$this->categoryName}\" berhasil ditambahkan!");

        // Reset the form
        $this->reset(['categoryName', 'categoryPrice', 'categoryAccountId']);
        $this->categoryType = CategoryType::PriceTimesQuantity->value;
        $this->showCreateForm = false;
    }

    // ─── Edit Category ──────────────────────────────────────────────────

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
            'editCategoryType' => ['required', Rule::in(CategoryType::manageableValues())],
            'editCategoryAccountId' => 'required|exists:accounts,id',
        ], [
            'editCategoryName.required' => 'Nama kategori wajib diisi.',
            'editCategoryType.required' => 'Tipe kategori wajib dipilih.',
            'editCategoryAccountId.required' => 'Pilih akun pendapatan.',
        ]);

        $account = Account::where('id', $this->editCategoryAccountId)
            ->where('type', 'pendapatan')
            ->first();

        if (! $account) {
            $this->addError('editCategoryAccountId', 'Pilih akun pendapatan.');

            return;
        }

        $duplicate = TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('name', $this->editCategoryName)
            ->where('id', '!=', $this->editId)
            ->exists();

        if ($duplicate) {
            $this->addError('editCategoryName', 'Nama kategori sudah ada di unit ini.');

            return;
        }

        $category = TransactionCategory::where('id', $this->editId)
            ->where('business_unit_id', $this->unitId)
            ->firstOrFail();

        $oldName = $category->name;

        $category->update([
            'name' => $this->editCategoryName,
            'type' => CategoryType::from($this->editCategoryType),
            'account_id' => $account->id,
        ]);

        // Notify about the change when the name changes
        if ($oldName !== $this->editCategoryName) {
            $this->notifyPriceRoles(
                "Kepala Unit {$this->unitName} mengubah kategori \"{$oldName}\" menjadi \"{$this->editCategoryName}\"."
            );
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
