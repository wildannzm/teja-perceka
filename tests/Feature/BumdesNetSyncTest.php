<?php

use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\User;
use App\Support\BumdesCashBalance;

test('bumdes unit net comes from journals and ignores stale header totals', function () {
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $user = User::factory()->create(['business_unit_id' => $unit->id]);
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $revenue = Account::create(['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'type' => 'pendapatan']);
    $expense = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);

    $daily = DailyTransaction::create([
        'business_unit_id' => $unit->id, 'user_id' => $user->id,
        'transaction_date' => '2026-09-02', 'total_income' => 703000,
        // Stale header on purpose: ledger only holds 110.000 of expenses.
        'total_expense' => 410000,
    ]);
    JournalEntry::create(['voucher_number' => 'DSB002', 'transaction_date' => '2026-09-02', 'description' => 'Pemasukan Harian', 'account_id' => $cash->id, 'debit' => 703000, 'credit' => 0, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'DSB002', 'transaction_date' => '2026-09-02', 'description' => 'Pemasukan Harian', 'account_id' => $revenue->id, 'debit' => 0, 'credit' => 703000, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'KSB002', 'transaction_date' => '2026-09-02', 'description' => 'Nota', 'account_id' => $expense->id, 'debit' => 110000, 'credit' => 0, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'KSB002', 'transaction_date' => '2026-09-02', 'description' => 'Nota', 'account_id' => $cash->id, 'debit' => 0, 'credit' => 110000, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);

    $nets = BumdesCashBalance::getNetIncomePerUnit('2026-09-01', '2026-09-30');

    // 703.000 - 110.000 from the ledger, not 703.000 - 410.000 from the header.
    expect($nets->count())->toBe(1);
    expect((float) $nets->first()['amount'])->toEqual(593000.0);
    expect($nets->first()['unit']->id)->toBe($unit->id);
});

test('bumdes opening balance comes from prior journals', function () {
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $user = User::factory()->create(['business_unit_id' => $unit->id]);
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $revenue = Account::create(['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'type' => 'pendapatan']);
    $expense = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);

    $daily = DailyTransaction::create([
        'business_unit_id' => $unit->id, 'user_id' => $user->id,
        'transaction_date' => '2026-08-02', 'total_income' => 500000, 'total_expense' => 999999,
    ]);
    JournalEntry::create(['voucher_number' => 'DSB001', 'transaction_date' => '2026-08-02', 'description' => 'Pemasukan Harian', 'account_id' => $cash->id, 'debit' => 500000, 'credit' => 0, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'DSB001', 'transaction_date' => '2026-08-02', 'description' => 'Pemasukan Harian', 'account_id' => $revenue->id, 'debit' => 0, 'credit' => 500000, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'KSB001', 'transaction_date' => '2026-08-02', 'description' => 'Nota', 'account_id' => $expense->id, 'debit' => 200000, 'credit' => 0, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);
    JournalEntry::create(['voucher_number' => 'KSB001', 'transaction_date' => '2026-08-02', 'description' => 'Nota', 'account_id' => $cash->id, 'debit' => 0, 'credit' => 200000, 'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id]);

    // Prior ledger net 500.000 - 200.000, stale header expense ignored.
    expect(BumdesCashBalance::getOpeningBalance('2026-09-01'))->toEqual(300000.0);
});
