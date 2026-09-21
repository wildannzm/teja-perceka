<?php

use App\Livewire\Transactions\RevenueTab;
use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\TransactionCategory;
use App\Models\TransactionItem;
use App\Models\User;
use Livewire\Livewire;

function seedRevenueScope(): array
{
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $user = User::factory()->create(['business_unit_id' => $unit->id]);
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $revenue = Account::create(['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'type' => 'pendapatan']);
    $expense = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);
    $category = TransactionCategory::create([
        'business_unit_id' => $unit->id,
        'name' => 'Tiket',
        'type' => 'flat',
        'direction' => 'pemasukan',
        'account_id' => $revenue->id,
        'price' => 100000,
    ]);

    $daily = DailyTransaction::create([
        'business_unit_id' => $unit->id, 'user_id' => $user->id,
        'transaction_date' => '2026-09-02', 'total_income' => 703000,
        // Stale header on purpose: journals below only hold 110.000.
        'total_expense' => 410000,
    ]);
    TransactionItem::create([
        'daily_transaction_id' => $daily->id, 'transaction_category_id' => $category->id,
        'quantity' => null, 'unit_price' => 703000, 'subtotal' => 703000,
    ]);

    // Income journals (D) + one expense voucher (K) in the ledger.
    JournalEntry::create(['voucher_number' => 'DSB002', 'transaction_date' => '2026-09-02', 'description' => 'Pemasukan Harian', 'account_id' => $cash->id, 'debit' => 703000, 'credit' => 0, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'DSB002', 'transaction_date' => '2026-09-02', 'description' => 'Pemasukan Harian', 'account_id' => $revenue->id, 'debit' => 0, 'credit' => 703000, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'KSB002', 'transaction_date' => '2026-09-02', 'description' => 'Nota', 'account_id' => $expense->id, 'debit' => 110000, 'credit' => 0, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'KSB002', 'transaction_date' => '2026-09-02', 'description' => 'Nota', 'account_id' => $cash->id, 'debit' => 0, 'credit' => 110000, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);

    return [$unit, $daily, $user];
}

test('rekap totals come from journals like laba rugi, ignoring stale header totals', function () {
    [$unit, , $user] = seedRevenueScope();
    $this->actingAs($user);

    $data = Livewire::test(RevenueTab::class, ['unitId' => $unit->id, 'mode' => 'monthly', 'month' => '2026-09'])
        ->get('reportData');

    // Ledger: revenue 703.000, expenses 110.000 — header claims 410.000.
    expect((float) $data['totalRevenue'])->toEqual(703000.0);
    expect((float) $data['unitTotalExpense'])->toEqual(110000.0);
    expect((float) $data['netIncome'])->toEqual(593000.0);
});

test('rekap net matches laba rugi net from the same journals', function () {
    [$unit, , $user] = seedRevenueScope();
    $this->actingAs($user);

    $rekap = Livewire::test(RevenueTab::class, ['unitId' => $unit->id, 'mode' => 'monthly', 'month' => '2026-09'])
        ->get('reportData');

    $revenue = (float) JournalEntry::where('business_unit_id', $unit->id)
        ->whereBetween('transaction_date', ['2026-09-01', '2026-09-30'])
        ->whereHas('account', fn ($q) => $q->where('type', 'pendapatan'))
        ->sum('credit');
    $expenses = (float) JournalEntry::where('business_unit_id', $unit->id)
        ->whereBetween('transaction_date', ['2026-09-01', '2026-09-30'])
        ->whereHas('account', fn ($q) => $q->where('type', 'beban'))
        ->sum('debit');

    expect((float) $rekap['netIncome'])->toEqual($revenue - $expenses);
});
