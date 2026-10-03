<?php

use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

test('new users can register and are logged in automatically', function () {
    Livewire::test(Register::class)
        ->set('form.name', 'Jane Doe')
        ->set('form.email', 'jane@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Jane Doe');

    $this->assertAuthenticatedAs($user);
});

test('registration requires a unique email', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Register::class)
        ->set('form.name', 'Jane Doe')
        ->set('form.email', 'taken@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors('form.email');
});

test('registration requires a matching password confirmation', function () {
    Livewire::test(Register::class)
        ->set('form.name', 'Jane Doe')
        ->set('form.email', 'jane@example.com')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'different')
        ->call('register')
        ->assertHasErrors('form.password');

    $this->assertGuest();
});
