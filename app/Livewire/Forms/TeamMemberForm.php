<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class TeamMemberForm extends Form
{
    public string $firstName = '';

    public string $lastName = '';

    public int $defaultCapacityPercent = 100;

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'defaultCapacityPercent' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }
}
