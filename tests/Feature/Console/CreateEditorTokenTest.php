<?php

use App\Models\Editor;

test('the command prompts for name and email and issues a token', function () {
    $this->artisan('editors:create-token')
        ->expectsQuestion('Wie heißt der Redakteur?', 'Jamie Marketing')
        ->expectsQuestion('Wie lautet die E-Mail-Adresse des Redakteurs?', 'jamie@example.com')
        ->assertExitCode(0);

    $editor = Editor::where('email', 'jamie@example.com')->first();

    expect($editor)->not->toBeNull();
    expect($editor->name)->toBe('Jamie Marketing');
    expect($editor->tokens()->count())->toBe(1);
});

test('reusing an existing email reuses the editor and only adds a new token', function () {
    $editor = Editor::factory()->create(['email' => 'jamie@example.com']);

    $this->artisan('editors:create-token')
        ->expectsQuestion('Wie heißt der Redakteur?', 'Jamie Marketing')
        ->expectsQuestion('Wie lautet die E-Mail-Adresse des Redakteurs?', 'jamie@example.com')
        ->assertExitCode(0);

    expect(Editor::count())->toBe(1);
    expect($editor->fresh()->tokens()->count())->toBe(1);
});
