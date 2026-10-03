<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Members extends Component
{
    public Team $team;

    public string $firstName = '';

    public string $lastName = '';

    public int $defaultCapacityPercent = 100;

    public function mount(Team $team): void
    {
        $this->authorize('update', $team);

        $this->team = $team;
    }

    public function addMember(): void
    {
        $this->authorize('update', $this->team);

        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'defaultCapacityPercent' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $this->team->teamMembers()->create([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'default_capacity_percent' => $validated['defaultCapacityPercent'],
        ]);

        $this->reset('firstName', 'lastName');
        $this->defaultCapacityPercent = 100;
    }

    public function updateCapacity(int $teamMemberId, int $value): void
    {
        $this->authorize('update', $this->team);

        $value = max(0, min(100, $value));

        $this->team->teamMembers()->whereKey($teamMemberId)->update(['default_capacity_percent' => $value]);
    }

    public function removeMember(int $teamMemberId): void
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
