<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class SprintForm extends Form
{
    public string $name = '';

    public string $goal = '';

    public string $startsAt = '';

    public string $endsAt = '';

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'goal' => ['nullable', 'string', 'max:2000'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['required', 'date', 'after_or_equal:startsAt'],
        ];
    }
}
