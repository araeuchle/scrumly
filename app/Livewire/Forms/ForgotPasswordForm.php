<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class ForgotPasswordForm extends Form
{
    public string $email = '';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }
}
