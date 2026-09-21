<?php

use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\User;
use App\Support\VoucherNumber;

function seedUnitWithAccounts(): array
{
    $user = User::factory()->create();
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $rev = Account::create(['code' => '4-1000', 'name' => 'Pendapatan', 'type' => 'revenue']);

    return [$user, $unit, $cash, $rev];
}

function seedIncomeVoucher(int $dailyId, string $voucher, string $date, int $unitId, int $cashId, int $revId, float $amount = 1000): void
{
    JournalEntry::create([
        'voucher_number' => $voucher, 'transaction_date' => $date, 'description' => 'Pemasukan Harian',
        'account_id' => $cashId, 'debit' => $amount, 'credit' => 0,
        'daily_transaction_id' => $dailyId, 'business_unit_id' => $unitId,
    ]);
    JournalEntry::create([
        'voucher_number' => $voucher, 'transaction_date' => $date, 'description' => 'Pemasukan Harian',
        'account_id' => $revId, 'debit' => 0, 'credit' => $amount,
        'daily_transaction_id' => $dailyId, 'business_unit_id' => $unitId,
    ]);
}

test('voucher numbering refuses to grow a scope that already has a duplicate', function () {
    [$user, $unit, $cash, $rev] = seedUnitWithAccounts();

    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 1000]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 1000]);
    seedIncomeVoucher($d1->id, 'DSB001', '2026-09-01', $unit->id, $cash->id, $rev->id);
    seedIncomeVoucher($d2->id, 'DSB001', '2026-09-02', $unit->id, $cash->id, $rev->id);

    expect(fn () => VoucherNumber::next('DSB', '2026-09-03', $unit->id))
        ->toThrow(RuntimeException::class, 'DSB001');
});

test('voucher numbering works fine when each voucher belongs to one daily', function () {
    [$user, $unit, $cash, $rev] = seedUnitWithAccounts();

    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 1000]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 1000]);
    seedIncomeVoucher($d1->id, 'DSB001', '2026-09-01', $unit->id, $cash->id, $rev->id);
    seedIncomeVoucher($d2->id, 'DSB002', '2026-09-02', $unit->id, $cash->id, $rev->id);

    expect(VoucherNumber::next('DSB', '2026-09-03', $unit->id)['number'])->toBe('DSB003');
});

test('repair command renumbers duplicate vouchers chronologically', function () {
    [$user, $unit, $cash, $rev] = seedUnitWithAccounts();

    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 1000]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 2000]);
    $d3 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-03', 'total_income' => 3000]);
    seedIncomeVoucher($d1->id, 'DSB001', '2026-09-01', $unit->id, $cash->id, $rev->id, 1000);
    seedIncomeVoucher($d2->id, 'DSB003', '2026-09-02', $unit->id, $cash->id, $rev->id, 2000);
    seedIncomeVoucher($d3->id, 'DSB003', '2026-09-03', $unit->id, $cash->id, $rev->id, 3000);

    $this->artisan('voucher:repair-duplicates')->assertSuccessful();

    $numbers = JournalEntry::where('business_unit_id', $unit->id)
        ->orderBy('transaction_date')
        ->get(['daily_transaction_id', 'voucher_number', 'debit', 'credit']);

    expect($numbers->where('daily_transaction_id', $d1->id)->pluck('voucher_number')->unique()->all())->toBe(['DSB001']);
    expect($numbers->where('daily_transaction_id', $d2->id)->pluck('voucher_number')->unique()->all())->toBe(['DSB002']);
    expect($numbers->where('daily_transaction_id', $d3->id)->pluck('voucher_number')->unique()->all())->toBe(['DSB003']);

    // Amounts untouched, vouchers still balanced.
    foreach ([$d1->id => 1000, $d2->id => 2000, $d3->id => 3000] as $dailyId => $amount) {
        $group = $numbers->where('daily_transaction_id', $dailyId);
        expect((float) $group->sum('debit'))->toEqual($amount);
        expect((float) $group->sum('credit'))->toEqual($amount);
    }

    $this->artisan('voucher:repair-duplicates')->assertSuccessful();
});

test('repair command dry-run changes nothing', function () {
    [$user, $unit, $cash, $rev] = seedUnitWithAccounts();

    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 1000]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 1000]);
    seedIncomeVoucher($d1->id, 'DSB001', '2026-09-01', $unit->id, $cash->id, $rev->id);
    seedIncomeVoucher($d2->id, 'DSB001', '2026-09-02', $unit->id, $cash->id, $rev->id);

    $this->artisan('voucher:repair-duplicates', ['--dry-run' => true])->assertSuccessful();

    expect(JournalEntry::where('daily_transaction_id', $d2->id)->first()->voucher_number)->toBe('DSB001');
});
