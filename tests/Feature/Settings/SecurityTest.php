<?php

use App\Livewire\Settings\Security;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
    Features::passkeys([
        'confirmPassword' => true,
    ]);
});

test('security settings page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'));

    $response->assertOk();

    $response->assertSee('Ubah Kata Sandi');
    $response->assertSee('Autentikasi Dua Faktor');
    $response->assertSee('Aktifkan 2FA');
    $response->assertSee('Tambah Passkey');
    $response->assertSee('Belum ada passkey');
    $response->assertSee('Cara pakai');
});

test('security settings page requires password confirmation when enabled', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('security.edit'));

    $response->assertRedirect(route('password.confirm'));
});

test('security settings page renders without two factor when feature is disabled', function () {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Ubah Kata Sandi')
        ->assertDontSeeText('Autentikasi Dua Faktor')
        ->assertDontSeeText('Aktifkan 2FA')
        ->assertDontSeeText('Tambah Passkey')
        ->assertDontSeeText('Belum ada passkey');
});

test('two factor authentication disabled when confirmation abandoned between requests', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test(Security::class);

    $component->assertSet('twoFactorEnabled', false);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Security::class)
        ->set('current_password', 'password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasNoErrors();
    $response->assertDispatched('swal-alert', icon: 'success', title: 'Berhasil', text: 'Kata sandi berhasil diperbarui.');

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Security::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasErrors(['current_password']);
    $response->assertDispatched('swal-alert', icon: 'error');
});

test('passkey management section lists owned passkeys', function () {
    $user = User::factory()->create();

    $user->passkeys()->create([
        'name' => 'Laptop Kerja',
        'credential_id' => 'credential-123',
        'credential' => ['aaguid' => '00000000-0000-0000-0000-000000000000'],
    ]);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Laptop Kerja');
});

test('user can delete their own passkey', function () {
    $user = User::factory()->create();

    $passkey = $user->passkeys()->create([
        'name' => 'Laptop Kerja',
        'credential_id' => 'credential-123',
        'credential' => ['aaguid' => '00000000-0000-0000-0000-000000000000'],
    ]);

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->call('confirmDeletePasskey', $passkey->id)
        ->assertSet('showDeletePasskeyModal', true)
        ->call('deletePasskey')
        ->assertHasNoErrors()
        ->assertDispatched('swal-alert', icon: 'success', title: 'Berhasil', text: 'Passkey berhasil dihapus.');

    $this->assertDatabaseMissing('passkeys', ['id' => $passkey->id]);
});

test('user cannot delete another user passkey', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $passkey = $owner->passkeys()->create([
        'name' => 'Laptop Kerja',
        'credential_id' => 'credential-123',
        'credential' => ['aaguid' => '00000000-0000-0000-0000-000000000000'],
    ]);

    $this->actingAs($intruder);

    expect(fn () => Livewire::test(Security::class)->call('confirmDeletePasskey', $passkey->id))
        ->toThrow(ModelNotFoundException::class);

    $this->assertDatabaseHas('passkeys', ['id' => $passkey->id]);
});

test('user can disable two factor after confirmation', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->call('confirmDisableTwoFactor')
        ->assertSet('showDisableTwoFactorModal', true)
        ->call('disable')
        ->assertHasNoErrors()
        ->assertDispatched('swal-alert', icon: 'success', title: 'Berhasil', text: 'Autentikasi dua faktor dinonaktifkan.');

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();
});

test('user can cancel disabling two factor', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->call('confirmDisableTwoFactor')
        ->assertSet('showDisableTwoFactorModal', true)
        ->call('cancelDisableTwoFactor')
        ->assertSet('showDisableTwoFactorModal', false);

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});
