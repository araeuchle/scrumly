<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Models\TeamEventType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class EventTypes extends Component
{
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

    public Team $team;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $icon = 'calendar-days';

    public int $defaultDurationMinutes = 30;

    public string $defaultAgenda = '';

    public bool $isRecurring = false;

    public string $timing = 'start';

    public bool $trackSpeakingTime = false;

    public function mount(Team $team): void
    {
        $this->authorize('update', $team);

        $this->team = $team;
    }

    public function startCreating(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function startEditing(int $teamEventTypeId): void
    {
        $eventType = $this->team->eventTypes()->findOrFail($teamEventTypeId);

        $this->editingId = $eventType->id;
        $this->name = $eventType->name;
        $this->icon = $eventType->icon;
        $this->defaultDurationMinutes = $eventType->default_duration_minutes;
        $this->defaultAgenda = (string) $eventType->default_agenda;
        $this->isRecurring = $eventType->is_recurring;
        $this->timing = $eventType->timing;
        $this->trackSpeakingTime = $eventType->track_speaking_time;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->authorize('update', $this->team);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'in:'.implode(',', array_keys(self::ICONS))],
            'defaultDurationMinutes' => ['required', 'integer', 'min:1', 'max:600'],
            'defaultAgenda' => ['nullable', 'string', 'max:4000'],
            'isRecurring' => ['boolean'],
            'timing' => ['required', 'in:start,end'],
            'trackSpeakingTime' => ['boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'icon' => $validated['icon'],
            'default_duration_minutes' => $validated['defaultDurationMinutes'],
            'default_agenda' => $validated['defaultAgenda'] ?: null,
            'is_recurring' => $validated['isRecurring'],
            'timing' => $validated['timing'],
            'track_speaking_time' => $validated['trackSpeakingTime'],
        ];

        if ($this->editingId !== null) {
            $this->team->eventTypes()->whereKey($this->editingId)->update($data);
        } else {
            $data['sort_order'] = ((int) $this->team->eventTypes()->max('sort_order')) + 1;
            $this->team->eventTypes()->create($data);
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $teamEventTypeId): void
    {
        $this->authorize('update', $this->team);

        $this->team->eventTypes()->whereKey($teamEventTypeId)->delete();
    }

    public function moveUp(int $teamEventTypeId): void
    {
        $this->swapOrder($teamEventTypeId, -1);
    }

    public function moveDown(int $teamEventTypeId): void
    {
        $this->swapOrder($teamEventTypeId, 1);
    }

    private function swapOrder(int $teamEventTypeId, int $direction): void
    {
        $this->authorize('update', $this->team);

        $eventTypes = $this->team->eventTypes()->get();
        $index = $eventTypes->search(fn (TeamEventType $eventType) => $eventType->id === $teamEventTypeId);

        if ($index === false) {
            return;
        }

        $swapIndex = $index + $direction;

        if (! $eventTypes->has($swapIndex)) {
            return;
        }

        $current = $eventTypes->get($index);
        $swapWith = $eventTypes->get($swapIndex);

        $currentOrder = $current->sort_order;
        $current->update(['sort_order' => $swapWith->sort_order]);
        $swapWith->update(['sort_order' => $currentOrder]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'icon', 'defaultDurationMinutes', 'defaultAgenda', 'isRecurring', 'timing', 'trackSpeakingTime']);
        $this->icon = 'calendar-days';
        $this->defaultDurationMinutes = 30;
        $this->timing = 'start';
    }

    public function render(): View
    {
        return view('livewire.teams.event-types', [
            'eventTypes' => $this->team->eventTypes()->get(),
            'iconOptions' => self::ICONS,
        ]);
    }
}
