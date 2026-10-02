<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertOk()
        ->assertSee('id="login-success-swal"', false)
        ->assertSee('Login Berhasil!');

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['password' => 'Kata sandi salah. Silakan coba lagi.']);

    $this->assertGuest();
});

test('login with an unregistered email errors the email field', function () {
    $response = $this->post(route('login.store'), [
        'email' => 'tidak-terdaftar@example.com',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors(['email' => 'Email tidak terdaftar. Periksa kembali alamat email Anda.']);

    $this->assertGuest();
});

test('successful login shows the modal before redirecting', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response
        ->assertOk()
        ->assertSee('id="login-success-swal"', false)
        ->assertSee('Selamat datang kembali')
        ->assertSee(route('dashboard', absolute: false), false);

    $this->assertAuthenticated();
});

test('failed login renders the failure modal', function () {
    $user = User::factory()->create();

    $response = $this->from(route('login'))->followingRedirects()->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertOk()->assertSee('id="login-swal"', false);

    $response->assertSee('Kesalahan pada Kata Sandi')->assertSee('Kata sandi salah. Silakan coba lagi.');

    $this->assertGuest();
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
