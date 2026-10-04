<?php

namespace App\Livewire\Forms;

use Livewire\Form;

class TeamEventTypeForm extends Form
{
    /**
     * @var array<string, string>
     */
    public const ICONS = [
        'calendar-days' => 'Kalender',
        'sun' => 'Sonne',
        'clipboard-document-list' => 'Checkliste',
        'presentation-chart-line' => 'Präsentation',
        'arrow-path' => 'Kreislauf',
        'chat-bubble-left-right' => 'Sprechblasen',
        'light-bulb' => 'Glühbirne',
        'user-group' => 'Gruppe',
        'clock' => 'Uhr',
        'flag' => 'Flagge',
    ];

    public string $name = '';

    public string $icon = 'calendar-days';

    public int $defaultDurationMinutes = 30;

    public string $defaultAgenda = '';

    public bool $isRecurring = false;

    public string $timing = 'start';

    public bool $trackSpeakingTime = false;

    public bool $isRetrospective = false;

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'in:'.implode(',', array_keys(self::ICONS))],
            'defaultDurationMinutes' => ['required', 'integer', 'min:1', 'max:600'],
            'defaultAgenda' => ['nullable', 'string', 'max:4000'],
            'isRecurring' => ['boolean'],
            'timing' => ['required', 'in:start,end'],
            'trackSpeakingTime' => ['boolean'],
            'isRetrospective' => ['boolean'],
        ];
    }
}
