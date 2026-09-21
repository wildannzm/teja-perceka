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

function seedExpenseVoucher(int $dailyId, string $voucher, string $date, int $unitId, int $expenseAccountId, int $cashId, string $description, float $amount): void
{
    JournalEntry::create([
        'voucher_number' => $voucher, 'transaction_date' => $date, 'description' => $description,
        'account_id' => $expenseAccountId, 'debit' => $amount, 'credit' => 0,
        'daily_transaction_id' => $dailyId, 'business_unit_id' => $unitId,
    ]);
    JournalEntry::create([
        'voucher_number' => $voucher, 'transaction_date' => $date, 'description' => $description,
        'account_id' => $cashId, 'debit' => 0, 'credit' => $amount,
        'daily_transaction_id' => $dailyId, 'business_unit_id' => $unitId,
    ]);
}

test('repair keeps each expense voucher of the same daily on its own number', function () {
    [$user, $unit, $cash] = seedUnitWithAccounts();
    $beban = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);

    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-07-18', 'total_income' => 0]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-07-19', 'total_income' => 0]);

    // One daily owning two distinct expense vouchers plus a number shared with another daily.
    seedExpenseVoucher($d1->id, 'KSB001', '2026-07-18', $unit->id, $beban->id, $cash->id, 'Nota A', 100000);
    seedExpenseVoucher($d1->id, 'KSB002', '2026-07-18', $unit->id, $beban->id, $cash->id, 'Nota B', 200000);
    seedExpenseVoucher($d2->id, 'KSB002', '2026-07-19', $unit->id, $beban->id, $cash->id, 'Nota C', 300000);

    $this->artisan('voucher:repair-duplicates', ['--prefix' => 'KSB', '--unit' => $unit->id, '--month' => '2026-07'])->assertSuccessful();

    $vouchersFor = fn (int $dailyId, string $desc) => JournalEntry::where('daily_transaction_id', $dailyId)
        ->where('description', $desc)->pluck('voucher_number')->unique()->values()->all();

    // d1 keeps two separate numbers, shared KSB002 splits chronologically.
    expect($vouchersFor($d1->id, 'Nota A'))->toBe(['KSB001']);
    expect($vouchersFor($d1->id, 'Nota B'))->toBe(['KSB002']);
    expect($vouchersFor($d2->id, 'Nota C'))->toBe(['KSB003']);

    // Amounts and descriptions untouched.
    expect((float) JournalEntry::where('description', 'Nota B')->sum('debit'))->toEqual(200000.0);
    expect((float) JournalEntry::where('description', 'Nota C')->sum('credit'))->toEqual(300000.0);
});

function seedGappedScope(): array
{
    [$user, $unit, $cash, $rev] = seedUnitWithAccounts();
    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 1000]);
    $d3 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-03', 'total_income' => 3000]);
    seedIncomeVoucher($d1->id, 'KSB001', '2026-09-01', $unit->id, $cash->id, $rev->id, 1000);
    seedIncomeVoucher($d3->id, 'KSB003', '2026-09-03', $unit->id, $cash->id, $rev->id, 3000);

    return [$unit, $d1, $d3];
}

test('append after a gap continues from the highest number instead of colliding', function () {
    [$unit] = seedGappedScope();

    // KSB002 is missing (deleted); appending Sep 4 must yield KSB004, not reuse KSB003.
    expect(VoucherNumber::next('KSB', '2026-09-04', $unit->id)['number'])->toBe('KSB004');
});

test('backdated insert fills a gap and shifts later vouchers', function () {
    [$unit, $d1, $d3] = seedGappedScope();

    expect(VoucherNumber::next('KSB', '2026-09-02', $unit->id)['number'])->toBe('KSB002');
    expect(JournalEntry::where('daily_transaction_id', $d3->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB004']);
    expect(JournalEntry::where('daily_transaction_id', $d1->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB001']);
});

test('repair compacts gaps left by deletions', function () {
    [$unit, $d1, $d3] = seedGappedScope();

    $this->artisan('voucher:repair-duplicates', ['--prefix' => 'KSB', '--unit' => $unit->id, '--month' => '2026-09'])->assertSuccessful();

    expect(JournalEntry::where('daily_transaction_id', $d1->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB001']);
    expect(JournalEntry::where('daily_transaction_id', $d3->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB002']);

    // Second run is a no-op on the now sequential scope.
    $this->artisan('voucher:repair-duplicates', ['--prefix' => 'KSB', '--unit' => $unit->id, '--month' => '2026-09'])->assertSuccessful();
    expect(JournalEntry::where('daily_transaction_id', $d3->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB002']);
});
