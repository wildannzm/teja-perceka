<?php

use App\Livewire\Transactions\JournalTab;
use App\Livewire\UnitHead\RecordExpense;
use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function seedExpenseScope(): array
{
    Role::firstOrCreate(['name' => 'kepala_unit']);
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $user = User::factory()->create(['business_unit_id' => $unit->id]);
    $user->assignRole('kepala_unit');
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $beban = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);

    return [$unit, $user, $cash, $beban];
}

function seedExpensePair(int $dailyId, string $voucher, string $date, int $unitId, int $expenseAccountId, int $cashId, string $description, float $amount): void
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

test('deleting an expense only removes the clicked month, never another month', function () {
    [$unit, $user, $cash, $beban] = seedExpenseScope();
    $dJuly = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-07-02', 'total_income' => 0, 'total_expense' => 265000]);
    $dSept = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 0, 'total_expense' => 110000]);
    seedExpensePair($dJuly->id, 'KSB002', '2026-07-02', $unit->id, $beban->id, $cash->id, 'Nota Juli', 265000);
    seedExpensePair($dSept->id, 'KSB002', '2026-09-02', $unit->id, $beban->id, $cash->id, 'Nota September', 110000);

    $this->actingAs($user);
    $septRowId = JournalEntry::where('daily_transaction_id', $dSept->id)->first()->id;

    Livewire::test(RecordExpense::class)
        ->call('confirmDelete', $septRowId)
        ->call('executeDelete');

    // July untouched, September gone, headers adjusted only for September.
    expect(JournalEntry::where('daily_transaction_id', $dJuly->id)->count())->toBe(2);
    expect(JournalEntry::where('daily_transaction_id', $dSept->id)->count())->toBe(0);
    expect($dJuly->fresh()->total_expense)->toEqual(265000.0);
    expect($dSept->fresh()->total_expense)->toEqual(0.0);
});

test('editing an expense only touches its own logical voucher', function () {
    [$unit, $user, $cash, $beban] = seedExpenseScope();
    $daily = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-06', 'total_income' => 0, 'total_expense' => 3100000]);
    seedExpensePair($daily->id, 'KSB007', '2026-09-06', $unit->id, $beban->id, $cash->id, 'Slip PHL', 2550000);
    seedExpensePair($daily->id, 'KSB007', '2026-09-06', $unit->id, $beban->id, $cash->id, 'KWT no.001', 550000);

    $this->actingAs($user);
    $slipRowId = JournalEntry::where('daily_transaction_id', $daily->id)->where('description', 'Slip PHL')->where('debit', '>', 0)->first()->id;

    Livewire::test(RecordExpense::class)
        ->call('editHistory', $slipRowId)
        ->set('editAmount', 2600000)
        ->set('editDate', '2026-09-06')
        ->set('editDescription', 'Slip PHL')
        ->set('editAccountId', (string) $beban->id)
        ->call('updateHistory')
        ->assertHasNoErrors();

    // Only Slip PHL changed (+50.000); KWT untouched; header follows by delta.
    expect((float) JournalEntry::where('description', 'Slip PHL')->sum('debit'))->toEqual(2600000.0);
    expect((float) JournalEntry::where('description', 'Slip PHL')->sum('credit'))->toEqual(2600000.0);
    expect((float) JournalEntry::where('description', 'KWT no.001')->sum('debit'))->toEqual(550000.0);
    expect((float) $daily->fresh()->total_expense)->toEqual(3150000.0);
});

test('journal tab expense edit keeps header expense in step with journals', function () {
    Role::firstOrCreate(['name' => 'bendahara']);
    [$unit, $user, $cash, $beban] = seedExpenseScope();
    $user->assignRole('bendahara');
    $daily = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-06', 'total_income' => 500000, 'total_expense' => 110000]);
    seedExpensePair($daily->id, 'KSB002', '2026-09-06', $unit->id, $beban->id, $cash->id, 'Nota', 110000);

    $this->actingAs($user);
    $debitRow = JournalEntry::where('daily_transaction_id', $daily->id)->where('debit', '>', 0)->first();

    $component = Livewire::test(JournalTab::class, ['unitId' => $unit->id, 'mode' => 'monthly', 'month' => '2026-09']);
    $component->call('openEdit', $debitRow->id);
    $editRows = $component->get('editRows');
    $editRows[0]['debit'] = '200000';

    $component->set('editRows', $editRows)->call('executeEdit');

    expect((float) $daily->fresh()->total_expense)->toEqual(200000.0);
    expect((float) $daily->fresh()->total_income)->toEqual(500000.0);
    expect((float) JournalEntry::where('daily_transaction_id', $daily->id)->sum('debit'))->toEqual(200000.0);
});

test('sync command resets stale expense headers to the ledger', function () {
    [$unit, $user, $cash, $beban] = seedExpenseScope();
    $daily = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-20', 'total_income' => 0, 'total_expense' => 4485000]);
    seedExpensePair($daily->id, 'KSB021', '2026-09-20', $unit->id, $beban->id, $cash->id, 'KWT', 150000);

    $this->artisan('voucher:sync-header-totals', ['--dry-run' => true])->assertSuccessful();
    expect((float) $daily->fresh()->total_expense)->toEqual(4485000.0);

    $this->artisan('voucher:sync-header-totals', ['--unit' => $unit->id, '--month' => '2026-09'])->assertSuccessful();
    expect((float) $daily->fresh()->total_expense)->toEqual(150000.0);
});

test('deleting a middle voucher closes the gap (001, 003 becomes 001, 002)', function () {
    [$unit, $user, $cash, $beban] = seedExpenseScope();
    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 0, 'total_expense' => 100000]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 0, 'total_expense' => 200000]);
    $d3 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-03', 'total_income' => 0, 'total_expense' => 300000]);
    seedExpensePair($d1->id, 'KSB001', '2026-09-01', $unit->id, $beban->id, $cash->id, 'Nota 1', 100000);
    seedExpensePair($d2->id, 'KSB002', '2026-09-02', $unit->id, $beban->id, $cash->id, 'Nota 2', 200000);
    seedExpensePair($d3->id, 'KSB003', '2026-09-03', $unit->id, $beban->id, $cash->id, 'Nota 3', 300000);

    $this->actingAs($user);
    $middleRowId = JournalEntry::where('daily_transaction_id', $d2->id)->first()->id;

    Livewire::test(RecordExpense::class)
        ->call('confirmDelete', $middleRowId)
        ->call('executeDelete')
        ->assertDispatched('swal-alert', icon: 'success', title: 'Berhasil');

    expect(JournalEntry::where('daily_transaction_id', $d2->id)->count())->toBe(0);
    expect(JournalEntry::where('daily_transaction_id', $d1->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB001']);
    expect(JournalEntry::where('daily_transaction_id', $d3->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB002']);
    expect((float) $d1->fresh()->total_expense)->toEqual(100000.0);
    expect((float) $d2->fresh()->total_expense)->toEqual(0.0);
    expect((float) $d3->fresh()->total_expense)->toEqual(300000.0);
});

test('moving an expense to another month closes the gap left behind', function () {
    [$unit, $user, $cash, $beban] = seedExpenseScope();
    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 0, 'total_expense' => 100000]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 0, 'total_expense' => 200000]);
    seedExpensePair($d1->id, 'KSB001', '2026-09-01', $unit->id, $beban->id, $cash->id, 'Nota 1', 100000);
    seedExpensePair($d2->id, 'KSB002', '2026-09-02', $unit->id, $beban->id, $cash->id, 'Nota 2', 200000);

    $this->actingAs($user);
    $rowId = JournalEntry::where('daily_transaction_id', $d1->id)->first()->id;

    Livewire::test(RecordExpense::class)
        ->call('editHistory', $rowId)
        ->set('editDate', '2026-10-01')
        ->set('editDescription', 'Nota 1')
        ->set('editAmount', 100000)
        ->set('editAccountId', (string) $beban->id)
        ->call('updateHistory')
        ->assertHasNoErrors();

    // September keeps only d2, renumbered to KSB001; October holds the moved rows.
    expect(JournalEntry::where('daily_transaction_id', $d2->id)->pluck('voucher_number')->unique()->all())->toBe(['KSB001']);
    expect(JournalEntry::where('daily_transaction_id', $d1->id)->whereDate('transaction_date', '2026-10-01')->count())->toBe(2);
});

test('journal tab delete closes the income gap and removes its daily', function () {
    Role::firstOrCreate(['name' => 'bendahara']);
    [$unit, $user, $cash] = seedExpenseScope();
    $user->assignRole('bendahara');
    $rev = Account::create(['code' => '4-2000', 'name' => 'Pendapatan Jasa', 'type' => 'pendapatan']);
    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-01', 'total_income' => 1000, 'total_expense' => 0]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-02', 'total_income' => 2000, 'total_expense' => 0]);
    $d3 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-09-03', 'total_income' => 3000, 'total_expense' => 0]);
    foreach ([$d1, $d2, $d3] as $i => $d) {
        $n = 'DSB00'.($i + 1);
        JournalEntry::create(['voucher_number' => $n, 'transaction_date' => $d->transaction_date, 'description' => 'Pemasukan', 'account_id' => $cash->id, 'debit' => 1000, 'credit' => 0, 'daily_transaction_id' => $d->id, 'business_unit_id' => $unit->id]);
        JournalEntry::create(['voucher_number' => $n, 'transaction_date' => $d->transaction_date, 'description' => 'Pemasukan', 'account_id' => $rev->id, 'debit' => 0, 'credit' => 1000, 'daily_transaction_id' => $d->id, 'business_unit_id' => $unit->id]);
    }

    $this->actingAs($user);
    $middleId = JournalEntry::where('daily_transaction_id', $d2->id)->first()->id;

    Livewire::test(JournalTab::class, ['unitId' => $unit->id, 'mode' => 'monthly', 'month' => '2026-09'])
        ->call('confirmDelete', $middleId)
        ->call('executeDelete');

    expect(DailyTransaction::find($d2->id))->toBeNull();
    expect(JournalEntry::where('daily_transaction_id', $d1->id)->pluck('voucher_number')->unique()->all())->toBe(['DSB001']);
    expect(JournalEntry::where('daily_transaction_id', $d3->id)->pluck('voucher_number')->unique()->all())->toBe(['DSB002']);
});
