<?php

use App\Livewire\UnitHead\RecordExpense;
use App\Models\Account;
use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\JournalEntry;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('recording multiple expense items at once reserves distinct voucher numbers', function () {
    Role::create(['name' => 'kepala_unit']);
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $user = User::factory()->create(['business_unit_id' => $unit->id]);
    $user->assignRole('kepala_unit');
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $beban = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);

    $this->actingAs($user);

    Livewire::test(RecordExpense::class)
        ->set('transactionDate', '2026-09-02')
        ->set('items', [
            ['account_id' => $beban->id, 'description' => 'Item satu', 'amount' => 100000],
            ['account_id' => $beban->id, 'description' => 'Item dua', 'amount' => 200000],
        ])
        ->call('submit')
        ->assertHasNoErrors();

    $numbers = JournalEntry::where('business_unit_id', $unit->id)
        ->where('voucher_number', 'like', 'KSB%')
        ->distinct()
        ->pluck('voucher_number')
        ->sort()
        ->values()
        ->all();

    // One voucher per item: KSB001 + KSB002, each with balanced debit/credit rows.
    expect($numbers)->toBe(['KSB001', 'KSB002']);

    foreach ($numbers as $number) {
        $group = JournalEntry::where('voucher_number', $number)->get();
        expect($group->count())->toBe(2);
        expect((float) $group->sum('debit'))->toEqual((float) $group->sum('credit'));
    }
});

test('recording into a corrupt scope shows an error instead of a server error and changes nothing', function () {
    Role::create(['name' => 'kepala_unit']);
    $unit = BusinessUnit::create(['name' => 'Sawah Bengkok', 'code' => 'SB', 'input_frequency' => 'daily']);
    $user = User::factory()->create(['business_unit_id' => $unit->id]);
    $user->assignRole('kepala_unit');
    $cash = Account::create(['code' => '1-1100', 'name' => 'Kas', 'type' => 'asset']);
    $beban = Account::create(['code' => '5-1000', 'name' => 'Beban Operasional', 'type' => 'beban']);

    $d1 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-07-18', 'total_income' => 0]);
    $d2 = DailyTransaction::create(['business_unit_id' => $unit->id, 'user_id' => $user->id, 'transaction_date' => '2026-07-19', 'total_income' => 0]);
    foreach ([$d1, $d2] as $daily) {
        JournalEntry::create([
            'voucher_number' => 'KSB024', 'transaction_date' => $daily->transaction_date, 'description' => 'Nota',
            'account_id' => $beban->id, 'debit' => 50000, 'credit' => 0,
            'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id,
        ]);
        JournalEntry::create([
            'voucher_number' => 'KSB024', 'transaction_date' => $daily->transaction_date, 'description' => 'Nota',
            'account_id' => $cash->id, 'debit' => 0, 'credit' => 50000,
            'daily_transaction_id' => $daily->id, 'business_unit_id' => $unit->id,
        ]);
    }

    $this->actingAs($user);

    Livewire::test(RecordExpense::class)
        ->set('transactionDate', '2026-07-20')
        ->set('items', [
            ['account_id' => $beban->id, 'description' => 'Item baru', 'amount' => 75000],
        ])
        ->call('submit')
        ->assertDispatched('swal-alert', icon: 'error', title: 'Gagal menyimpan');

    // Guard rolled everything back: no new rows, corrupt numbers untouched.
    expect(JournalEntry::count())->toBe(4);
    expect(JournalEntry::where('description', 'Item baru')->count())->toBe(0);
});
