<?php

namespace App\Livewire\Teams;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Switcher extends Component
{
    public ?int $currentTeamId = null;

    public function mount(): void
    {
        $this->currentTeamId = $this->currentUser()->resolveCurrentTeam()?->id;
    }

    public function updatedCurrentTeamId(): void
    {
        if ($this->currentTeamId === null) {
            return;
        }

        $user = $this->currentUser();
        $team = $user->teams()->findOrFail($this->currentTeamId);

        $user->forceFill(['current_team_id' => $team->id])->save();

        $this->dispatch('team-switched', teamId: $team->id);
    }

    public function render(): View
    {
        return view('livewire.teams.switcher', [
            'teams' => $this->currentUser()->teams()->orderBy('name')->get(),
        ]);
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
