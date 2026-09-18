<?php

namespace App\Livewire\UnitHead;

use App\Enums\TransactionType;
use App\Enums\CategoryType;
use App\Models\JournalEntry;
use App\Models\TransactionCategory;
use App\Models\Account;
use App\Models\TransactionItem;
use App\Models\DailyTransaction;
use App\Models\BusinessUnit;
use App\Support\VoucherNumber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class RecordDailyTransaction extends Component
{
    #[Locked]
    public ?int $unitId = null;

    public ?BusinessUnit $unit = null;

    public string $transactionDate = '';

    public ?string $endDate = null;

    public bool $isWeekly = false;

    /** When set, the component is in edit mode for this DailyTransaction ID. */
    #[Locked]
    public ?int $editId = null;

    public bool $isEditing = false;

    // Holds the input state of each category
    // Format: [kategori_id => ['quantity' => value, 'amount' => value, 'active' => boolean, 'subtotal' => value]]
    public array $inputs = [];

    public bool $alreadySubmitted = false;

    public bool $showDuplicateError = false;

    public float $totalIncome = 0;

    public function mount(?int $editId = null)
    {
        $user = Auth::user();

        // Restrict access to the kepala_unit role owning a business unit
        if (! $user->hasRole('kepala_unit') || ! $user->business_unit_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->business_unit_id;
        $this->unit = BusinessUnit::findOrFail($this->unitId);

        $today = Carbon::today();
        $this->isWeekly = $this->unit->input_frequency === 'weekly';

        if ($editId) {
            $this->loadEditMode($editId);
        } else {
            if ($this->isWeekly) {
                // For weekly units (TPS), default to today as the specific entry date,
                // while tanggalAkhir marks the end of the current week period.
                $this->transactionDate = $today->format('Y-m-d');
                $this->endDate = $today->copy()->endOfWeek()->format('Y-m-d');
            } else {
                $this->transactionDate = $today->format('Y-m-d');
            }

            $this->checkAlreadySubmitted();
            $this->initCategoryInputs();
        }
    }

    /**
     * Load an existing DailyTransaction into edit mode.
     */
    private function loadEditMode(int $editId): void
    {
        $transaction = DailyTransaction::with('items.transactionCategory')
            ->where('business_unit_id', $this->unitId)
            ->findOrFail($editId);

        $this->editId = $editId;
        $this->isEditing = true;
        $this->transactionDate = $transaction->transaction_date->format('Y-m-d');
        $this->endDate = $transaction->end_date?->format('Y-m-d');

        // Build a lookup of existing detail values keyed by kategori_id
        $existingDetails = $transaction->items->keyBy('transaction_category_id');

        $this->initCategoryInputs();

        // Overlay existing values onto the initialised inputs
        foreach ($existingDetails as $kategoriId => $detail) {
            if (! isset($this->inputs[$kategoriId])) {
                continue;
            }

            $type = $this->inputs[$kategoriId]['type'];

            if ($type === CategoryType::PriceTimesQuantity->value || $type === CategoryType::Yearly->value) {
                $this->inputs[$kategoriId]['quantity'] = $detail->quantity ?? '';
            } elseif ($type === CategoryType::Flat->value) {
                $this->inputs[$kategoriId]['active'] = $detail->subtotal > 0;
            } elseif ($type === CategoryType::Custom->value) {
                $this->inputs[$kategoriId]['amount'] = $detail->subtotal > 0 ? (string) (int) $detail->subtotal : '';
            }
        }

        $this->recalculateSubtotals();
    }

    public function updatedTransactionDate(): void
    {
        if ($this->isWeekly) {
            // For weekly units (TPS), the user picks a specific day within the week.
            // Keep their chosen date and derive tanggalAkhir as the end of that week.
            $date = Carbon::parse($this->transactionDate);
            $this->endDate = $date->copy()->endOfWeek()->format('Y-m-d');
        }

        if (! $this->isEditing) {
            $this->checkAlreadySubmitted();
        }

        // Recalculate all subtotals since prices may differ on the new date
        $this->recalculateSubtotals();
    }

    public function updatedInputs()
    {
        $this->recalculateSubtotals();
    }

    private function checkAlreadySubmitted(): void
    {
        $this->alreadySubmitted = DailyTransaction::where('business_unit_id', $this->unitId)
            ->whereDate('transaction_date', $this->transactionDate)
            ->exists();
    }

    #[Computed]
    public function categoryList()
    {
        return TransactionCategory::where('business_unit_id', $this->unitId)
            ->where('direction', TransactionType::Income)
            ->get()->keyBy('id');
    }

    private function initCategoryInputs()
    {
        $this->inputs = [];
        $currentYear = Carbon::parse($this->transactionDate)->year;

        foreach ($this->categoryList as $category) {
            // Special rule for yearly categories: hide when already submitted this year,
            // except while editing (show every category from the original data).
            if (! $this->isEditing && $category->type === CategoryType::Yearly) {
                // Check whether it was already submitted in the current year
                $yearlyExists = TransactionItem::where('transaction_category_id', $category->id)
                    ->whereHas('dailyTransaction', function ($query) use ($currentYear) {
                        $query->whereYear('transaction_date', $currentYear);
                    })->exists();

                if ($yearlyExists) {
                    continue; // Hide when already submitted this year
                }
            }

            $this->inputs[$category->id] = [
                'type' => $category->type->value,
                'quantity' => '',
                'amount' => '',
                'active' => false,
                'subtotal' => 0,
            ];
        }
    }

    private function recalculateSubtotals()
    {
        $total = 0;
        $date = Carbon::parse($this->transactionDate);

        foreach ($this->inputs as $id => $input) {
            $category = $this->categoryList->get($id);
            if (! $category) {
                continue;
            }

            $subtotal = 0;

            if ($input['type'] === CategoryType::PriceTimesQuantity->value || $input['type'] === CategoryType::Yearly->value) {
                $quantity = (int) ($input['quantity'] ?: 0);
                if ($quantity > 0) {
                    $unitPrice = $category->priceAt($date);
                    $subtotal = $quantity * $unitPrice;
                }
            } elseif ($input['type'] === CategoryType::Flat->value) {
                if ($input['active']) {
                    $subtotal = $category->priceAt($date);
                }
            } elseif ($input['type'] === CategoryType::Custom->value) {
                $subtotal = (float) ($input['amount'] ?: 0);
            }

            $this->inputs[$id]['subtotal'] = $subtotal;
            $total += $subtotal;
        }

        $this->totalIncome = $total;
    }

    public function submit()
    {
        // Base validation
        $this->validate([
            'transactionDate' => 'required|date',
            'totalIncome' => 'required|numeric|min:0',
        ]);

        if ($this->totalIncome <= 0) {
            $this->addError('totalIncome', 'Total pemasukan tidak boleh nol. Silakan isi minimal satu transaksi.');

            return;
        }

        if (! $this->isEditing) {
            $this->checkAlreadySubmitted();
            if ($this->alreadySubmitted) {
                $this->showDuplicateError = true;

                return;
            }
        }

        if ($this->isEditing && $this->editId) {
            return $this->executeUpdate();
        } else {
            return $this->executeCreate();
        }
    }

    private function executeCreate()
    {
        DB::beginTransaction();

        try {
            $transaction = DailyTransaction::create([
                'business_unit_id' => $this->unitId,
                'user_id' => Auth::id(),
                'transaction_date' => $this->transactionDate,
                'end_date' => $this->endDate,
                'total_income' => $this->totalIncome,
            ]);

            $date = Carbon::parse($this->transactionDate);

            foreach ($this->inputs as $id => $input) {
                $subtotal = $input['subtotal'];
                if ($subtotal > 0) {
                    $category = $this->categoryList->get($id);
                    $unitPrice = 0;
                    $quantity = null;

                    if ($input['type'] === CategoryType::PriceTimesQuantity->value) {
                        $quantity = (int) $input['quantity'];
                        $unitPrice = $category->priceAt($date);
                    } elseif ($input['type'] === CategoryType::Flat->value) {
                        $unitPrice = $category->priceAt($date);
                    }

                    TransactionItem::create([
                        'daily_transaction_id' => $transaction->id,
                        'transaction_category_id' => $id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);
                }
            }

            $this->createJournalEntries($transaction, $date);

            DB::commit();

            // Reset the form
            $this->initCategoryInputs();
            $this->totalIncome = 0;

            session()->flash('status', 'Transaksi berhasil disimpan!');

            // Redirect to the same page to re-render a clean state
            return $this->redirect(route('unit.input-transaksi'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('submit', 'Terjadi kesalahan saat menyimpan transaksi: '.$e->getMessage());
        }
    }

    private function executeUpdate()
    {
        DB::beginTransaction();

        try {
            $transaction = DailyTransaction::where('business_unit_id', $this->unitId)
                ->findOrFail($this->editId);

            $date = Carbon::parse($this->transactionDate);

            // Update header
            $transaction->update([
                'transaction_date' => $this->transactionDate,
                'end_date' => $this->endDate,
                'total_income' => $this->totalIncome,
                'user_id' => Auth::id(),
            ]);

            // Delete old details and regenerate
            $transaction->items()->delete();

            foreach ($this->inputs as $id => $input) {
                $subtotal = $input['subtotal'];
                if ($subtotal > 0) {
                    $category = $this->categoryList->get($id);
                    $unitPrice = 0;
                    $quantity = null;

                    if ($input['type'] === CategoryType::PriceTimesQuantity->value) {
                        $quantity = (int) $input['quantity'];
                        $unitPrice = $category->priceAt($date);
                    } elseif ($input['type'] === CategoryType::Flat->value) {
                        $unitPrice = $category->priceAt($date);
                    }

                    TransactionItem::create([
                        'daily_transaction_id' => $transaction->id,
                        'transaction_category_id' => $id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);
                }
            }

            // Regenerate income journals only. Expense vouchers (K prefix)
            // linked to this header must survive an income edit; wiping them
            // orphans the header expense total from the ledger.
            // Regenerate income journals only. Expense vouchers (K prefix)
            // linked to this header must survive an income edit; wiping them
            // orphans the header expense total from the ledger.
            JournalEntry::where('daily_transaction_id', $transaction->id)
                ->where('voucher_number', 'like', 'D%')
                ->delete();
            $this->createJournalEntries($transaction, $date);

            DB::commit();

            session()->flash('status', 'Transaksi berhasil diperbarui!');
            session()->flash('swal', ['icon' => 'success', 'title' => 'Berhasil', 'text' => 'Transaksi berhasil diperbarui!']);

            return $this->redirect(route('riwayat-rekap'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('submit', 'Terjadi kesalahan saat memperbarui transaksi: '.$e->getMessage());
        }
    }

    /**
     * Create JournalEntry entries for a DailyTransaction.
     *
     * @throws \Exception
     */
    private function createJournalEntries(DailyTransaction $transaction, Carbon $date): void
    {
        $cashAccount = Account::where('code', '1-1100')->first();
        if (! $cashAccount) {
            throw new \Exception('Akun Kas (1-1100) tidak ditemukan di sistem. Harap hubungi administrator.');
        }

        // Prefix: 'D' (Income) + business unit code
        $unitCode = strtoupper($this->unit->code ?? 'XX');
        $numberPrefix = 'D'.$unitCode;

        // Chronological insert-and-shift within the prefix+month+unit scope.
        $voucherNumber = VoucherNumber::next($numberPrefix, $date->format('Y-m-d'), $this->unitId)['number'];
        $journalDescription = 'Pemasukan Harian - '.$this->unit->name;

        // 1. Record Debit to Cash account
        JournalEntry::create([
            'voucher_number' => $voucherNumber,
            'transaction_date' => $this->transactionDate,
            'description' => $journalDescription,
            'account_id' => $cashAccount->id,
            'debit' => $this->totalIncome,
            'credit' => 0,
            'daily_transaction_id' => $transaction->id,
            'business_unit_id' => $this->unitId,
        ]);

        // 2. Group Credits per account_id from submitted inputs
        $creditGroups = [];
        foreach ($this->inputs as $id => $input) {
            $subtotal = $input['subtotal'];
            if ($subtotal > 0) {
                $category = $this->categoryList->get($id);
                $akunId = $category->account_id;
                if (! $akunId) {
                    throw new \Exception('Kategori "'.$category->name.'" belum terhubung ke Kode Akun (Chart of Account).');
                }

                if (! isset($creditGroups[$akunId])) {
                    $creditGroups[$akunId] = 0;
                }
                $creditGroups[$akunId] += $subtotal;
            }
        }

        // 3. Record Credit for each corresponding revenue account
        foreach ($creditGroups as $akunId => $creditAmount) {
            JournalEntry::create([
                'voucher_number' => $voucherNumber,
                'transaction_date' => $this->transactionDate,
                'description' => $journalDescription,
                'account_id' => $akunId,
                'debit' => 0,
                'credit' => $creditAmount,
                'daily_transaction_id' => $transaction->id,
                'business_unit_id' => $this->unitId,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.unit-head.record-daily-transaction');
    }
}
