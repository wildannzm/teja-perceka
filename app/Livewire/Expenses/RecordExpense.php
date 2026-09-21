<?php

namespace App\Livewire\Expenses;

use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\JournalEntry;
use App\Support\VoucherNumber;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Catat Pengeluaran')]
class RecordExpense extends Component
{
    #[Locked]
    public ?int $unitId = null;

    public ?BusinessUnit $unit = null;

    public string $transactionDate = '';

    public array $items = [];

    public float $totalExpense = 0;

    public string $filterMode = 'daily';

    public string $filterDate = '';

    public string $filterMonth = '';

    public string $filterSemester = '1';

    public string $filterSemesterYear = '';

    public string $filterYear = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public bool $showDeleteModal = false;

    public ?string $editingVoucherNumber = null;

    public ?string $deletingVoucherNumber = null;

    public string $editDate = '';

    public string $editAccountId = '';

    public string $editDescription = '';

    public $editAmount = '';

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasAnyRole(['direktur_bumdes', 'sekretaris', 'bendahara'])) {
            abort(403, 'Akses ditolak.');
        }

        $this->unitId = null;
        $this->unit = null;
        $this->transactionDate = Carbon::today()->format('Y-m-d');

        $today = Carbon::today();
        $this->filterMode = 'daily';
        $this->filterDate = $today->format('Y-m-d');
        $this->filterMonth = $today->format('Y-m');
        $this->filterSemester = $today->month <= 6 ? '1' : '2';
        $this->filterSemesterYear = $today->format('Y');
        $this->filterYear = $today->format('Y');

        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'account_id' => '',
            'description' => '',
            'amount' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->items = array_values($this->items);
        $this->calculateTotal();
    }

    public function updatedItems(): void
    {
        $this->calculateTotal();
    }

    public function updatedTransactionDate(): void
    {
        // Tidak ada aksi khusus, tanggal bebas dipilih
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->reset(['items', 'totalExpense']);
        $this->transactionDate = Carbon::today()->format('Y-m-d');
        $this->addItem();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetErrorBag();
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->reset(['editingVoucherNumber', 'editAccountId', 'editDescription', 'editAmount', 'editDate']);
        $this->resetErrorBag();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingVoucherNumber = null;
    }

    public function resetFilter(): void
    {
        $today = Carbon::today();
        $this->filterMode = 'daily';
        $this->filterDate = $today->format('Y-m-d');
        $this->filterMonth = $today->format('Y-m');
        $this->filterSemester = $today->month <= 6 ? '1' : '2';
        $this->filterSemesterYear = $today->format('Y');
        $this->filterYear = $today->format('Y');
    }

    private function calculateTotal(): void
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += (float) ($item['amount'] ?: 0);
        }
        $this->totalExpense = $total;
    }

    public function submit(): void
    {
        $this->validate([
            'transactionDate' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.description' => 'required|string|max:500',
            'items.*.amount' => 'required|numeric|min:1|max:9999999999999',
        ], [
            'items.required' => 'Minimal satu item pengeluaran harus diisi.',
            'items.*.account_id.required' => 'Pilih jenis biaya untuk setiap item.',
            'items.*.description.required' => 'Keterangan wajib diisi untuk setiap item.',
            'items.*.amount.required' => 'Nominal wajib diisi untuk setiap item.',
            'items.*.amount.min' => 'Nominal harus lebih dari 0.',
            'items.*.amount.max' => 'Nominal terlalu besar.',
        ]);

        $validItems = array_values(array_filter($this->items, fn ($item) => (float) ($item['amount'] ?? 0) > 0 && ! empty($item['account_id'])));

        if (empty($validItems)) {
            $this->addError('items', 'Minimal satu item pengeluaran dengan nominal valid harus diisi.');

            return;
        }

        $date = Carbon::parse($this->transactionDate);
        $prefix = 'KBM';

        DB::beginTransaction();

        try {
            $cashAccount = Account::where('code', '1-1100')->firstOrFail();

            // Chronological batch reservation under a single lock.
            $voucherBatch = VoucherNumber::nextBatch($prefix, $date->format('Y-m-d'), count($validItems), $this->unitId);

            $newTotalExpense = (float) $this->totalExpense;

            foreach ($validItems as $index => $item) {
                $itemAmount = (float) $item['amount'];
                $voucherNumber = $voucherBatch[$index];
                $description = $item['description'];

                JournalEntry::create([
                    'voucher_number' => $voucherNumber,
                    'transaction_date' => $this->transactionDate,
                    'description' => $description,
                    'account_id' => (int) $item['account_id'],
                    'debit' => $itemAmount,
                    'credit' => 0,
                    'daily_transaction_id' => null,
                    'business_unit_id' => $this->unitId,
                ]);

                JournalEntry::create([
                    'voucher_number' => $voucherNumber,
                    'transaction_date' => $this->transactionDate,
                    'description' => $description,
                    'account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $itemAmount,
                    'daily_transaction_id' => null,
                    'business_unit_id' => $this->unitId,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: $e->getMessage());

            return;
        }

        \Flux::toast(variant: 'success', text: 'Pengeluaran unit berhasil dicatat!');

        $this->reset(['items', 'totalExpense']);
        $this->transactionDate = Carbon::today()->format('Y-m-d');
        $this->addItem();
        $this->showCreateModal = false;
    }

    public function editHistory($voucherNumber): void
    {
        $debitJournal = JournalEntry::where('voucher_number', $voucherNumber)
            ->where('business_unit_id', $this->unitId)
            ->where('debit', '>', 0)
            ->first();

        if (! $debitJournal) {
            return;
        }

        $this->editingVoucherNumber = $voucherNumber;
        $this->editDate = $debitJournal->transaction_date
            ? Carbon::parse($debitJournal->transaction_date)->format('Y-m-d')
            : Carbon::today()->format('Y-m-d');
        $this->editAccountId = (string) $debitJournal->account_id;
        $this->editDescription = $debitJournal->description;
        $this->editAmount = $debitJournal->debit;
        $this->showEditModal = true;
    }

    public function updateHistory(): void
    {
        $validated = $this->validate([
            'editDate' => 'required|date',
            'editAccountId' => 'required|exists:accounts,id',
            'editDescription' => 'required|string|max:500',
            'editAmount' => 'required|numeric|min:1|max:9999999999999',
        ]);

        $journals = JournalEntry::where('voucher_number', $this->editingVoucherNumber)
            ->where('business_unit_id', $this->unitId)
            ->get();

        $oldTotal = $journals->sum('debit');
        $oldTotal = $journals->sum('debit');

        DB::transaction(function () use ($journals, $validated) {
            $cashAccount = Account::where('code', '1-1100')->firstOrFail();

            foreach ($journals as $j) {
                $isDebit = $j->debit > 0;
                $j->update([
                    'transaction_date' => $validated['editDate'],
                    'description' => $validated['editDescription'],
                    'account_id' => $isDebit ? (int) $validated['editAccountId'] : $cashAccount->id,
                    'debit' => $isDebit ? $validated['editAmount'] : 0,
                    'credit' => $isDebit ? 0 : $validated['editAmount'],
                ]);
            }

        });

        $this->showEditModal = false;
        $this->reset(['editingVoucherNumber', 'editAccountId', 'editDescription', 'editAmount', 'editDate']);

        \Flux::toast(variant: 'success', text: 'Data pengeluaran berhasil diperbarui.');
    }

    public function confirmDelete($voucherNumber): void
    {
        $this->deletingVoucherNumber = $voucherNumber;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->deletingVoucherNumber) {
            return;
        }

        $this->deleteHistory($this->deletingVoucherNumber, false);

        $this->showDeleteModal = false;
        $this->deletingVoucherNumber = null;

        \Flux::toast(variant: 'success', text: 'Data pengeluaran berhasil dihapus.');
    }

    public function deleteHistory($voucherNumber, $showToast = true): void
    {
        $journals = JournalEntry::where('voucher_number', $voucherNumber)->where('business_unit_id', $this->unitId)->get();
        if ($journals->isEmpty()) {
            return;
        }

        $totalDebit = $journals->sum('debit');
        $totalDebit = $journals->sum('debit');

        DB::transaction(function () use ($journals) {
            foreach ($journals as $j) {
                $j->delete();
            }

        });

        if ($showToast) {
            \Flux::toast(variant: 'success', text: 'Data pengeluaran berhasil dihapus.');
        }
    }

    public function updatedFilterMode(): void
    {
        $today = Carbon::today();
        match ($this->filterMode) {
            'daily' => $this->filterDate = $this->filterDate ?: $today->format('Y-m-d'),
            'monthly' => $this->filterMonth = $this->filterMonth ?: $today->format('Y-m'),
            'semester' => [
                $this->filterSemester = $this->filterSemester ?: '1',
                $this->filterSemesterYear = $this->filterSemesterYear ?: $today->format('Y'),
            ],
            'yearly' => $this->filterYear = $this->filterYear ?: $today->format('Y'),
            default => $this->filterDate = $this->filterDate ?: $today->format('Y-m-d'),
        };
    }

    #[Computed]
    public function history()
    {
        $query = JournalEntry::with('account')
            ->where('business_unit_id', $this->unitId)
            ->where('credit', 0)
            ->where('voucher_number', 'like', 'K%');

        match ($this->filterMode) {
            'daily' => $query->whereDate('transaction_date', $this->filterDate ?: Carbon::today()->format('Y-m-d')),
            'monthly' => $query->whereBetween('transaction_date', [
                Carbon::parse(($this->filterMonth ?: date('Y-m')).'-01')->startOfMonth()->format('Y-m-d'),
                Carbon::parse(($this->filterMonth ?: date('Y-m')).'-01')->endOfMonth()->format('Y-m-d'),
            ]),
            'semester' => $query->whereBetween('transaction_date', [
                Carbon::create((int) ($this->filterSemesterYear ?: date('Y')), $this->filterSemester === '1' ? 1 : 7, 1)->startOfMonth()->format('Y-m-d'),
                Carbon::create((int) ($this->filterSemesterYear ?: date('Y')), $this->filterSemester === '1' ? 6 : 12, 1)->endOfMonth()->format('Y-m-d'),
            ]),
            'yearly' => $query->whereBetween('transaction_date', [
                Carbon::create((int) ($this->filterYear ?: date('Y')), 1, 1)->startOfDay()->format('Y-m-d'),
                Carbon::create((int) ($this->filterYear ?: date('Y')), 12, 31)->endOfDay()->format('Y-m-d'),
            ]),
            default => null,
        };

        $query->orderByRaw('LENGTH(voucher_number) ASC')
            ->orderByRaw('voucher_number ASC');

        return $query->limit(50)->get();
    }

    #[Computed]
    public function historyTotal(): float
    {
        return $this->history->sum('debit');
    }

    public function render()
    {
        $expenseAccounts = Account::where('type', 'beban')->orderBy('code')->get();

        return view('livewire.expenses.record-expense', [
            'expenseAccounts' => $expenseAccounts,
            'history' => $this->history,
            'historyTotal' => $this->historyTotal,
        ]);
    }
}
