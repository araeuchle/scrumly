<?php

namespace App\Livewire\Forms;

use App\Enums\ImpedimentPriority;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ImpedimentForm extends Form
{
    public string $title = '';

    public string $description = '';

    public string $priority = 'medium';

    public ?string $sprintId = null;

    public string $reportedBy = '';

    public string $owner = '';

    public string $resolutionNotes = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'priority' => ['required', Rule::enum(ImpedimentPriority::class)],
            'sprintId' => ['nullable', 'uuid'],
            'reportedBy' => ['nullable', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'resolutionNotes' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
