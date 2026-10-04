<?php

namespace App\Livewire\Sprints;

use App\Enums\SprintEventStatus;
use App\Models\Sprint;
use App\Models\SprintEvent;
use App\Models\TeamEventType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public Sprint $sprint;

    public function mount(Sprint $sprint): void
    {
        $this->authorize('view', $sprint);

        $this->sprint = $sprint;
    }

    public function updateCapacity(string $capacityId, int $value): void
    {
        $this->authorize('update', $this->sprint);

        $value = max(0, min(100, $value));

        $this->sprint->capacities()->whereKey($capacityId)->update(['capacity_percent' => $value]);
    }

    public function updateCapacityNote(string $capacityId, string $note): void
    {
        $this->authorize('update', $this->sprint);

        $this->sprint->capacities()->whereKey($capacityId)->update(['note' => $note ?: null]);
    }

    public function addEventOccurrence(string $teamEventTypeId): void
    {
        $this->authorize('update', $this->sprint);

        $eventType = $this->sprint->team->eventTypes()->findOrFail($teamEventTypeId);
        $today = now()->toDateString();

        $event = $this->sprint->events()
            ->where('team_event_type_id', $eventType->id)
            ->where('scheduled_date', $today)
            ->first();

        if ($event === null) {
            $event = SprintEvent::create([
                'sprint_id' => $this->sprint->id,
                'team_event_type_id' => $eventType->id,
                'scheduled_date' => $today,
                'duration_minutes' => $eventType->default_duration_minutes,
                'agenda' => $eventType->default_agenda,
                'status' => SprintEventStatus::Scheduled->value,
            ]);
        }

        $this->redirectRoute('sprint-events.show', $event, navigate: true);
    }

    public function deleteEvent(string $eventId): void
    {
        $this->authorize('update', $this->sprint);

        $this->sprint->events()->whereKey($eventId)->delete();
    }

    public function render(): View
    {
        $this->sprint->load([
            'capacities.teamMember',
            'events' => fn ($query) => $query->orderBy('scheduled_date'),
            'events.teamEventType',
        ]);

        $averageCapacity = (int) round($this->sprint->capacities->avg('capacity_percent') ?? 0);

        /** @var Collection<int, TeamEventType> $availableEventTypes */
        $availableEventTypes = $this->sprint->team->eventTypes()->get();

        return view('livewire.sprints.show', [
            'averageCapacity' => $averageCapacity,
            'availableEventTypes' => $availableEventTypes,
        ]);
    }
}
