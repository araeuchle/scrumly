<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class TwoFactorConfirmationForm extends Form
{
    public string $code = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'size:6'],
        ];
    }
}
