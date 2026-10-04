<?php

use App\Livewire\Settings\Index;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

test('a user can update their profile', function () {
    $user = User::factory()->create(['name' => 'Alte Name']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('profileForm.name', 'Neuer Name')
        ->set('profileForm.email', 'neu@example.com')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($user->fresh()->name)->toBe('Neuer Name');
    expect($user->fresh()->email)->toBe('neu@example.com');
});

test('a user cannot change their email to one already in use', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'me@example.com']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('profileForm.name', $user->name)
        ->set('profileForm.email', 'taken@example.com')
        ->call('updateProfile')
        ->assertHasErrors('profileForm.email');

    expect($user->fresh()->email)->toBe('me@example.com');
});

test('a user can change their password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password-123')]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('passwordForm.currentPassword', 'old-password-123')
        ->set('passwordForm.password', 'new-password-123')
        ->set('passwordForm.password_confirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

test('a user cannot change their password with the wrong current password', function () {
    $user = User::factory()->create(['password' => bcrypt('old-password-123')]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('passwordForm.currentPassword', 'wrong-password')
        ->set('passwordForm.password', 'new-password-123')
        ->set('passwordForm.password_confirmation', 'new-password-123')
        ->call('updatePassword')
        ->assertHasErrors('passwordForm.currentPassword');
});

test('a user can enable two-factor authentication', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Index::class)
        ->call('enableTwoFactor')
        ->assertSet('showingQrCode', true);

    $secret = $user->fresh()->two_factor_secret;
    expect($secret)->not->toBeNull();

    $code = (new Google2FA())->getCurrentOtp($secret);

    $component->set('twoFactorConfirmationForm.code', $code)
        ->call('confirmTwoFactor')
        ->assertHasNoErrors();

    $user = $user->fresh();
    expect($user->hasTwoFactorEnabled())->toBeTrue();
    expect($user->two_factor_recovery_codes)->toHaveCount(8);
});

test('confirming two-factor authentication fails with an invalid code', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('enableTwoFactor')
        ->set('twoFactorConfirmationForm.code', '000000')
        ->call('confirmTwoFactor')
        ->assertHasErrors('twoFactorConfirmationForm.code');

    expect($user->fresh()->hasTwoFactorEnabled())->toBeFalse();
});

test('a user can disable two-factor authentication', function () {
    $user = User::factory()->create([
        'two_factor_secret' => (new Google2FA())->generateSecretKey(),
        'two_factor_confirmed_at' => now(),
        'two_factor_recovery_codes' => ['aaaaa-bbbbb'],
    ]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('disableTwoFactor');

    $user = $user->fresh();
    expect($user->hasTwoFactorEnabled())->toBeFalse();
    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_recovery_codes)->toBeNull();
});
