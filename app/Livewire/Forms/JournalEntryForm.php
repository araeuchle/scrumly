<?php

namespace App\Livewire\Forms;

use App\Enums\JournalEmotion;
use Illuminate\Validation\Rule;
use Livewire\Form;

class JournalEntryForm extends Form
{
    public string $entryDate = '';

    public string $wentWell = '';

    public string $onMyMind = '';

    public string $shouldIntervene = '';

    /** @var array<int, string> */
    public array $emotions = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'entryDate' => ['required', 'date'],
            'wentWell' => ['nullable', 'string', 'max:4000'],
            'onMyMind' => ['nullable', 'string', 'max:4000'],
            'shouldIntervene' => ['nullable', 'string', 'max:4000'],
            'emotions' => ['array'],
            'emotions.*' => [Rule::enum(JournalEmotion::class)],
        ];
    }
}
