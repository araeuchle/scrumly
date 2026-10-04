<?php

namespace App\Livewire\Teams;

use App\Livewire\Forms\TeamMemberForm;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Members extends Component
{
    public Team $team;

    public TeamMemberForm $form;

    public function mount(Team $team): void
    {
        $this->authorize('update', $team);

        $this->team = $team;
    }

    public function addMember(): void
    {
        $this->authorize('update', $this->team);

        $validated = $this->form->validate();

        $this->team->teamMembers()->create([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'default_capacity_percent' => $validated['defaultCapacityPercent'],
        ]);

        $this->form->reset();
    }

    public function updateCapacity(string $teamMemberId, int $value): void
    {
        $this->authorize('update', $this->team);

        $value = max(0, min(100, $value));

        $this->team->teamMembers()->whereKey($teamMemberId)->update(['default_capacity_percent' => $value]);
    }

    public function removeMember(string $teamMemberId): void
    {
        $this->authorize('update', $this->team);

        $this->team->teamMembers()->whereKey($teamMemberId)->delete();
    }

    public function render(): View
    {
        return view('livewire.teams.members', [
            'teamMembers' => $this->team->teamMembers()->orderBy('first_name')->get(),
        ]);
    }
}
