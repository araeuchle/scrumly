<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class TwoFactorChallengeForm extends Form
{
    public string $code = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255'],
        ];
    }
}
