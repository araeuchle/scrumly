<?php

namespace App\Console\Commands;

use App\Models\Editor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\text;

class CreateEditorToken extends Command
{
    protected $signature = 'editors:create-token';

    protected $description = 'Create (or reuse) an editor and issue a new Sanctum API token for the Magazin blog API';

    public function handle(): void
    {
        $name = text(
            label: 'Wie heißt der Redakteur?',
            required: true,
        );

        $email = text(
            label: 'Wie lautet die E-Mail-Adresse des Redakteurs?',
            required: true,
            validate: fn (string $value) => Validator::make(['email' => $value], ['email' => ['required', 'email']])
                ->errors()
                ->first('email'),
        );

        $editor = Editor::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name],
        );

        $token = $editor->createToken('cli')->plainTextToken;

        $this->info("Editor: {$editor->name} <{$editor->email}>");
        $this->newLine();
        $this->line('API-Token (nur jetzt sichtbar, sicher übermitteln):');
        $this->line($token);
    }
}
