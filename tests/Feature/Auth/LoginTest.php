<?php

use App\Livewire\Auth\Login;
use App\Models\User;
use Livewire\Livewire;

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create([
        'password' => bcrypt('test1234!'),
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'test1234!')
        ->call('login')
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('users cannot authenticate with an invalid password', function () {
    $user = User::factory()->create([
        'password' => bcrypt('test1234!'),
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('login is rate limited after too many failed attempts', function () {
    $user = User::factory()->create([
        'password' => bcrypt('test1234!'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'wrong-password')
            ->call('login');
    }

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'test1234!')
        ->call('login')
        ->assertHasErrors('email');

    $this->assertGuest();
});

test('authenticated users are redirected away from the login page', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('login'))->assertRedirect(route('dashboard'));
});
