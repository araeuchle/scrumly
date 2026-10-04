<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class OneOnOneForm extends Form
{
    public string $heldAt = '';

    public string $notes = '';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'heldAt' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
