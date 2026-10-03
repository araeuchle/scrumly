<?php

namespace App\Livewire\Teams;

use App\Livewire\Forms\TeamEventTypeForm;
use App\Models\Team;
use App\Models\TeamEventType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class EventTypes extends Component
{
    public Team $team;

    public bool $showForm = false;

    public ?int $editingId = null;

    public TeamEventTypeForm $form;

    public function mount(Team $team): void
    {
        $this->authorize('update', $team);

        $this->team = $team;
    }

    public function startCreating(): void
    {
        $this->form->reset();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function startEditing(int $teamEventTypeId): void
    {
        $eventType = $this->team->eventTypes()->findOrFail($teamEventTypeId);

        $this->editingId = $eventType->id;
        $this->form->name = $eventType->name;
        $this->form->icon = $eventType->icon;
        $this->form->defaultDurationMinutes = $eventType->default_duration_minutes;
        $this->form->defaultAgenda = (string) $eventType->default_agenda;
        $this->form->isRecurring = $eventType->is_recurring;
        $this->form->timing = $eventType->timing;
        $this->form->trackSpeakingTime = $eventType->track_speaking_time;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->form->reset();
        $this->editingId = null;
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->authorize('update', $this->team);

        $validated = $this->form->validate();

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
            $maxSortOrder = $this->team->eventTypes()->max('sort_order');
            $data['sort_order'] = (is_numeric($maxSortOrder) ? (int) $maxSortOrder : 0) + 1;
            $this->team->eventTypes()->create($data);
        }

        $this->form->reset();
        $this->editingId = null;
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

        if (! $current instanceof TeamEventType || ! $swapWith instanceof TeamEventType) {
            return;
        }

        $currentOrder = $current->sort_order;
        $current->update(['sort_order' => $swapWith->sort_order]);
        $swapWith->update(['sort_order' => $currentOrder]);
    }

    public function render(): View
    {
        return view('livewire.teams.event-types', [
            'eventTypes' => $this->team->eventTypes()->get(),
            'iconOptions' => TeamEventTypeForm::ICONS,
        ]);
    }
}
