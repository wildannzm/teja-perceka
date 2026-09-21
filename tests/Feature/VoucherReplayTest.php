<?php

use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\User;
use App\Support\VoucherNumber;

function makeIncomeVoucher(int $dailyId, string $date, int $unitId, int $cashId, int $revId, float $amount = 1000): string
{
    $number = VoucherNumber::next('DSB', $date, $unitId)['number'];
    JournalEntry::create([
        'voucher_number' => $number,
        'transaction_date' => $date,
        'description' => 'Pemasukan Harian - Sawah Bengkok',
        'account_id' => $cashId,
        'debit' => $amount,
        'credit' => 0,
        'daily_transaction_id' => $dailyId,
        'business_unit_id' => $unitId,
    ]);
    JournalEntry::create([
        'voucher_number' => $number,
        'transaction_date' => $date,
        'description' => 'Pemasukan Harian - Sawah Bengkok',
        'account_id' => $revId,
        'debit' => 0,
        'credit' => $amount,
        'daily_transaction_id' => $dailyId,
        'business_unit_id' => $unitId,
    ]);

    return $number;
}

function assertSequential(string $label): void
{
    $rows = JournalEntry::where('voucher_number', 'like', 'DSB%')
        ->orderBy('transaction_date')->orderBy('id')
        ->get(['voucher_number', 'transaction_date', 'daily_transaction_id']);

    $byVoucher = [];
    foreach ($rows as $r) {
        $byVoucher[$r->voucher_number][] = $r->daily_transaction_id.'@'.$r->transaction_date;
    }
    ksort($byVoucher);

    $expected = 1;
    foreach ($byVoucher as $voucher => $members) {
        $want = 'DSB'.str_pad($expected, 3, '0', STR_PAD_LEFT);
        expect($voucher)->toBe($want, "$label: expected $want, got $voucher (members: ".implode(',', $members).')');
        expect(count(array_unique($members)))->toBe(1, "$label: voucher $voucher shared by multiple dailies: ".implode(',', $members));
        $expected++;
    }
}

test('replay production september sawah bengkok sequence keeps sequential numbers', function () {
    $user = User::factory()->create();
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $rev = Account::create(['code' => '4-1000', 'name' => 'Pendapatan', 'type' => 'revenue']);

    // 14:58:36 — daily Sept 2 created first (income journal right away, scope empty)
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 703000]);
    expect(makeIncomeVoucher($d2->id, '2026-09-02', $unit->id, $cash->id, $rev->id))->toBe('DSB001');
    assertSequential('after sept2');

    // 14:59:45 — daily Sept 1 edited: delete D journals (none) then regenerate (backdated insert)
    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 260000]);
    JournalEntry::where('daily_transaction_id', $d1->id)->where('voucher_number', 'like', 'D%')->delete();
    expect(makeIncomeVoucher($d1->id, '2026-09-01', $unit->id, $cash->id, $rev->id))->toBe('DSB001');
    assertSequential('after sept1 backdate');

    // Sept 3, 4 sequential appends
    foreach (['2026-09-03', '2026-09-04'] as $i => $date) {
        $d = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => $date, 'total_income' => 1000]);
        makeIncomeVoucher($d->id, $date, $unit->id, $cash->id, $rev->id);
        assertSequential("after $date");
    }

    // Sept 7..19 appends (Sept 5-6 headers exist but income comes later, like production)
    $d5 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-05', 'total_income' => 1000]);
    $d6 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-06', 'total_income' => 1000]);
    foreach (range(7, 19) as $day) {
        $date = '2026-09-'.str_pad($day, 2, '0', STR_PAD_LEFT);
        $d = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => $date, 'total_income' => 1000]);
        makeIncomeVoucher($d->id, $date, $unit->id, $cash->id, $rev->id);
        assertSequential("after $date");
    }

    // Backdated income for Sept 5 and Sept 6 (via edit path: delete + recreate)
    foreach ([[$d5, '2026-09-05'], [$d6, '2026-09-06']] as [$d, $date]) {
        JournalEntry::where('daily_transaction_id', $d->id)->where('voucher_number', 'like', 'D%')->delete();
        makeIncomeVoucher($d->id, $date, $unit->id, $cash->id, $rev->id);
        assertSequential("after backdate $date");
    }

    // Sept 20 append
    $d20 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-20', 'total_income' => 1000]);
    makeIncomeVoucher($d20->id, '2026-09-20', $unit->id, $cash->id, $rev->id);
    assertSequential('after sept20');
});
