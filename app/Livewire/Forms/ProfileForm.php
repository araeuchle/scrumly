<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class ProfileForm extends Form
{
    public string $name = '';

    public string $email = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ];
    }
}
