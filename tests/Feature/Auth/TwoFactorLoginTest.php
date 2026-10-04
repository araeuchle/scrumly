<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

test('a user with two-factor enabled must enter a code after a correct password', function () {
    $secret = (new Google2FA())->generateSecretKey();
    $user = User::factory()->create([
        'password' => bcrypt('test1234!'),
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => ['aaaaa-bbbbb'],
    ]);

    $component = Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'test1234!')
        ->call('login')
        ->assertSet('needsTwoFactor', true);

    $this->assertGuest();

    $code = (new Google2FA())->getCurrentOtp($secret);

    $component->set('twoFactorForm.code', $code)
        ->call('confirmTwoFactorChallenge')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('an invalid two-factor code is rejected', function () {
    $secret = (new Google2FA())->generateSecretKey();
    $user = User::factory()->create([
        'password' => bcrypt('test1234!'),
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'test1234!')
        ->call('login')
        ->set('twoFactorForm.code', '000000')
        ->call('confirmTwoFactorChallenge')
        ->assertHasErrors('twoFactorForm.code');

    $this->assertGuest();
});

test('a valid recovery code logs the user in and is consumed', function () {
    $secret = (new Google2FA())->generateSecretKey();
    $user = User::factory()->create([
        'password' => bcrypt('test1234!'),
        'two_factor_secret' => $secret,
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => ['aaaaa-bbbbb', 'ccccc-ddddd'],
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'test1234!')
        ->call('login')
        ->set('twoFactorForm.code', 'aaaaa-bbbbb')
        ->call('confirmTwoFactorChallenge')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
    expect($user->fresh()->two_factor_recovery_codes)->toBe(['ccccc-ddddd']);

    auth()->logout();

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'test1234!')
        ->call('login')
        ->set('twoFactorForm.code', 'aaaaa-bbbbb')
        ->call('confirmTwoFactorChallenge')
        ->assertHasErrors('twoFactorForm.code');

    $this->assertGuest();
});

test('a user without two-factor enabled logs in directly', function () {
    $user = User::factory()->create(['password' => bcrypt('test1234!')]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'test1234!')
        ->call('login')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});
