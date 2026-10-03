<?php

namespace App\Livewire\Sprints;

use App\Enums\SprintEventStatus;
use App\Enums\SprintStatus;
use App\Livewire\Forms\SprintForm;
use App\Models\Sprint;
use App\Models\SprintCapacity;
use App\Models\SprintEvent;
use App\Models\Team;
use App\Models\TeamEventType;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Create extends Component
{
    public Team $team;

    public SprintForm $form;

    public function mount(Team $team): void
    {
        $this->authorize('create', [Sprint::class, $team]);

        $this->team = $team;
        $this->form->startsAt = now()->toDateString();
        $this->form->endsAt = now()->addWeeks(2)->toDateString();
    }

    public function save(): void
    {
        $this->authorize('create', [Sprint::class, $this->team]);

        $validated = $this->form->validate();

        $sprint = $this->team->sprints()->create([
            'name' => $validated['name'],
            'goal' => $validated['goal'] ?: null,
            'starts_at' => $validated['startsAt'],
            'ends_at' => $validated['endsAt'],
            'status' => SprintStatus::Planned->value,
        ]);

        foreach ($this->team->teamMembers as $teamMember) {
            SprintCapacity::create([
                'sprint_id' => $sprint->id,
                'team_member_id' => $teamMember->id,
                'capacity_percent' => $teamMember->default_capacity_percent,
            ]);
        }

        $onceEventTypes = $this->team->eventTypes()->where('is_recurring', false)->get();

        foreach ($onceEventTypes as $eventType) {
            $this->seedEvent($sprint, $eventType, $eventType->timing === 'end' ? $validated['endsAt'] : $validated['startsAt']);
        }

        $this->redirectRoute('sprints.show', $sprint, navigate: true);
    }

    private function seedEvent(Sprint $sprint, TeamEventType $eventType, string $date): void
    {
        SprintEvent::create([
            'sprint_id' => $sprint->id,
            'team_event_type_id' => $eventType->id,
            'scheduled_date' => $date,
            'duration_minutes' => $eventType->default_duration_minutes,
            'agenda' => $eventType->default_agenda,
            'status' => SprintEventStatus::Scheduled->value,
        ]);
    }

    public function render(): View
    {
        return view('livewire.sprints.create');
    }
}
