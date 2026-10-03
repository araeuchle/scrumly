<?php

namespace App\Livewire\Forms;

use Illuminate\Validation\Rules\Password;
use Livewire\Form;

class ResetPasswordForm extends Form
{
    public string $token = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ];
    }
}
