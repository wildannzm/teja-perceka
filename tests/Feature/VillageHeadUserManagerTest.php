<?php

use App\Livewire\VillageHead\UserManager;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('village head cannot open or save privileged accounts even by id', function () {
    Role::firstOrCreate(['name' => 'kepala_desa']);
    Role::firstOrCreate(['name' => 'kepala_unit']);
    Role::firstOrCreate(['name' => 'super_admin']);

    $kades = User::factory()->create();
    $kades->assignRole('kepala_desa');
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($kades);

    expect(fn () => Livewire::test(UserManager::class)->call('editUser', $admin->id))
        ->toThrow(ModelNotFoundException::class);

    expect(User::find($admin->id)->name)->toBe($admin->name);
});

test('village head can still manage unit head accounts', function () {
    Role::firstOrCreate(['name' => 'kepala_desa']);
    Role::firstOrCreate(['name' => 'kepala_unit']);

    $kades = User::factory()->create();
    $kades->assignRole('kepala_desa');
    $staff = User::factory()->create();
    $staff->assignRole('kepala_unit');

    $this->actingAs($kades);

    Livewire::test(UserManager::class)
        ->call('editUser', $staff->id)
        ->set('name', 'Nama Baru')
        ->set('email', 'baru@example.com')
        ->call('saveUser')
        ->assertHasNoErrors();

    expect(User::find($staff->id)->name)->toBe('Nama Baru');
});
