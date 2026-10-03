<?php

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

test('a reset link can be requested for an existing user', function () {
    Notification::fake();

    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('form.email', $user->email)
        ->call('sendResetLink')
        ->assertHasNoErrors();

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

test('requesting a reset link for an unknown email shows an error', function () {
    Notification::fake();

    Livewire::test(ForgotPassword::class)
        ->set('form.email', 'unknown@example.com')
        ->call('sendResetLink')
        ->assertHasErrors('form.email');

    Notification::assertNothingSent();
});

test('a password can be reset with a valid token', function () {
    $user = User::factory()->create();

    $token = Password::createToken($user);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('form.email', $user->email)
        ->set('form.password', 'new-password123')
        ->set('form.password_confirmation', 'new-password123')
        ->call('resetPassword')
        ->assertRedirect(route('login'));

    expect(Hash::check('new-password123', $user->fresh()->password))->toBeTrue();
});

test('a password cannot be reset with an invalid token', function () {
    $user = User::factory()->create();
    $originalPassword = $user->password;

    Livewire::test(ResetPassword::class, ['token' => 'invalid-token'])
        ->set('form.email', $user->email)
        ->set('form.password', 'new-password123')
        ->set('form.password_confirmation', 'new-password123')
        ->call('resetPassword')
        ->assertHasErrors('form.email');

    expect($user->fresh()->password)->toBe($originalPassword);
});
