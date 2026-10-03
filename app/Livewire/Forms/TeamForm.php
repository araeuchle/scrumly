<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class TeamForm extends Form
{
    public string $name = '';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
