<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class RetroActionItemForm extends Form
{
    public string $description = '';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:2000'],
        ];
    }
}
