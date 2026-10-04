<?php

namespace App\Livewire\SprintEvents;

use App\Enums\SprintEventStatus;
use App\Livewire\Forms\RetroActionItemForm;
use App\Models\DailySpeakingTurn;
use App\Models\RetroActionItem;
use App\Models\SprintEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public SprintEvent $sprintEvent;

    public string $agenda = '';

    public string $notes = '';

    public string $boardUrl = '';

    public RetroActionItemForm $actionItemForm;

    public function mount(SprintEvent $sprintEvent): void
    {
        $this->authorize('view', $sprintEvent);

        $this->sprintEvent = $sprintEvent;
        $this->agenda = (string) $sprintEvent->agenda;
        $this->notes = (string) $sprintEvent->notes;
        $this->boardUrl = (string) $sprintEvent->board_url;
    }

    public function updatedAgenda(string $value): void
    {
        $this->authorize('update', $this->sprintEvent);

        $this->sprintEvent->update(['agenda' => $value]);
    }

    public function updatedNotes(string $value): void
    {
        $this->authorize('update', $this->sprintEvent);

        $this->sprintEvent->update(['notes' => $value]);
    }

    public function updatedBoardUrl(string $value): void
    {
        $this->authorize('update', $this->sprintEvent);

        $this->sprintEvent->update(['board_url' => $value ?: null]);
    }

    public function addActionItem(): void
    {
        $this->authorize('create', [RetroActionItem::class, $this->sprintEvent->sprint->team]);

        $validated = $this->actionItemForm->validate();

        $this->sprintEvent->sprint->team->retroActionItems()->create([
            'sprint_event_id' => $this->sprintEvent->id,
            'description' => $validated['description'],
        ]);

        $this->actionItemForm->reset();
    }

    public function completeActionItem(string $actionItemId): void
    {
        $actionItem = $this->sprintEvent->sprint->team->retroActionItems()->findOrFail($actionItemId);

        $this->authorize('update', $actionItem);

        $actionItem->complete();
    }

    public function reopenActionItem(string $actionItemId): void
    {
        $actionItem = $this->sprintEvent->sprint->team->retroActionItems()->findOrFail($actionItemId);

        $this->authorize('update', $actionItem);

        $actionItem->reopen();
    }

    public function deleteActionItem(string $actionItemId): void
    {
        $actionItem = $this->sprintEvent->sprint->team->retroActionItems()->findOrFail($actionItemId);

        $this->authorize('delete', $actionItem);

        $actionItem->delete();
    }

    public function start(): void
    {
        $this->authorize('update', $this->sprintEvent);

        $this->sprintEvent->update([
            'status' => SprintEventStatus::InProgress->value,
            'started_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->authorize('update', $this->sprintEvent);

        foreach ($this->activeSpeakingTurns() as $turn) {
            $this->stopTurn($turn);
        }

        $this->sprintEvent->update([
            'status' => SprintEventStatus::Completed->value,
            'ended_at' => now(),
        ]);
    }

    public function toggleSpeaking(string $teamMemberId): void
    {
        $this->authorize('update', $this->sprintEvent);

        $turn = DailySpeakingTurn::firstOrCreate(
            ['sprint_event_id' => $this->sprintEvent->id, 'team_member_id' => $teamMemberId],
            ['seconds' => 0]
        );

        if ($turn->isActive()) {
            $this->stopTurn($turn);

            return;
        }

        foreach ($this->activeSpeakingTurns() as $activeTurn) {
            $this->stopTurn($activeTurn);
        }

        $turn->update(['started_at' => now()]);
    }

    /**
     * @return Collection<int, DailySpeakingTurn>
     */
    private function activeSpeakingTurns(): Collection
    {
        return DailySpeakingTurn::where('sprint_event_id', $this->sprintEvent->id)
            ->whereNotNull('started_at')
            ->get();
    }

    private function stopTurn(DailySpeakingTurn $turn): void
    {
        $turn->update([
            'seconds' => $turn->currentSeconds(),
            'started_at' => null,
        ]);
    }

    public function render(): View
    {
        $this->sprintEvent->load('sprint.team.teamMembers', 'teamEventType');

        $speakingTurns = [];

        if ($this->sprintEvent->teamEventType->track_speaking_time) {
            $existingTurns = $this->sprintEvent->speakingTurns()->get()->keyBy('team_member_id');

            foreach ($this->sprintEvent->sprint->team->teamMembers as $teamMember) {
                $speakingTurns[$teamMember->id] = [
                    'teamMember' => $teamMember,
                    'turn' => $existingTurns->get($teamMember->id),
                ];
            }
        }

        $retroActionItems = $this->sprintEvent->teamEventType->is_retrospective
            ? $this->sprintEvent->sprint->team->retroActionItems()->where('is_done', false)->latest()->get()
            : null;

        return view('livewire.sprint-events.show', [
            'speakingTurns' => $speakingTurns,
            'retroActionItems' => $retroActionItems,
        ]);
    }
}
