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

    public ?int $editingJournalId = null;

    public ?int $deletingJournalId = null;

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
        // No special action; any date may be picked
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
        $this->reset(['editingJournalId', 'editAccountId', 'editDescription', 'editAmount', 'editDate']);
        $this->resetErrorBag();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingJournalId = null;
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
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal menyimpan', text: 'Minimal satu item pengeluaran dengan nominal valid harus diisi.');

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

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Pengeluaran unit berhasil dicatat!');

        $this->reset(['items', 'totalExpense']);
        $this->transactionDate = Carbon::today()->format('Y-m-d');
        $this->addItem();
        $this->showCreateModal = false;
    }

    /**
     * Rows of the single logical voucher the user clicked: same voucher +
     * same month + same unit + same daily + same description. Never touch
     * another month's same-numbered voucher or a merged same-number voucher.
     */
    private function historyGroup(int $journalId)
    {
        $anchor = JournalEntry::where('id', $journalId)
            ->where('business_unit_id', $this->unitId)
            ->first();

        if (! $anchor) {
            return collect();
        }

        $monthStart = Carbon::parse($anchor->transaction_date)->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::parse($anchor->transaction_date)->endOfMonth()->format('Y-m-d');

        return JournalEntry::where('voucher_number', $anchor->voucher_number)
            ->where('business_unit_id', $this->unitId)
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->when($anchor->daily_transaction_id !== null,
                fn ($query) => $query->where('daily_transaction_id', $anchor->daily_transaction_id),
                fn ($query) => $query->whereNull('daily_transaction_id'))
            ->where('description', $anchor->description)
            ->orderBy('id')
            ->get();
    }

    public function editHistory(int $journalId): void
    {
        $group = $this->historyGroup($journalId);
        $debitJournal = $group->first(fn ($row) => $row->debit > 0);

        if (! $debitJournal) {
            return;
        }

        $this->editingJournalId = $journalId;
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

        $journals = $this->historyGroup((int) $this->editingJournalId);
        if ($journals->isEmpty()) {
            return;
        }

        $prefix = VoucherNumber::prefixOf($journals->first()->voucher_number);
        $oldMonth = substr((string) $journals->first()->transaction_date, 0, 7);
        $newMonth = substr($validated['editDate'], 0, 7);

        DB::transaction(function () use ($journals, $validated, $prefix, $oldMonth, $newMonth) {
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

            // A cross-month move leaves a gap behind: close it on both sides.
            if ($prefix !== null && $newMonth !== $oldMonth) {
                VoucherNumber::renumberScope($prefix, $oldMonth, $this->unitId);
                VoucherNumber::renumberScope($prefix, $newMonth, $this->unitId);
            }
        });

        $this->showEditModal = false;
        $this->reset(['editingJournalId', 'editAccountId', 'editDescription', 'editAmount', 'editDate']);

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Data pengeluaran berhasil diperbarui.');
    }

    public function confirmDelete(int $journalId): void
    {
        $this->deletingJournalId = $journalId;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->deletingJournalId) {
            return;
        }

        $this->deleteHistory($this->deletingJournalId, false);

        $this->showDeleteModal = false;
        $this->deletingJournalId = null;

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Data pengeluaran berhasil dihapus.');
    }

    public function deleteHistory(int $journalId, $showToast = true): void
    {
        $journals = $this->historyGroup($journalId);
        if ($journals->isEmpty()) {
            return;
        }

        $prefix = VoucherNumber::prefixOf($journals->first()->voucher_number);
        $month = substr((string) $journals->first()->transaction_date, 0, 7);

        DB::transaction(function () use ($journals, $prefix, $month) {
            foreach ($journals as $j) {
                $j->delete();
            }

            // Keep the remaining numbers gapless (001, 002, ...).
            if ($prefix !== null) {
                VoucherNumber::renumberScope($prefix, $month, $this->unitId);
            }
        });

        if ($showToast) {
            $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Data pengeluaran berhasil dihapus.');
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
