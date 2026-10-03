<?php

namespace App\Livewire\Teams;

use App\Livewire\Forms\TeamForm;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public TeamForm $form;

    public bool $showCreateForm = false;

    public function createTeam(): void
    {
        $this->authorize('create', Team::class);

        $validated = $this->form->validate();

        $user = $this->currentUser();
        $team = $user->teams()->create($validated);

        $user->forceFill(['current_team_id' => $team->id])->save();

        $this->redirectRoute('sprints.index', $team, navigate: true);
    }

    public function selectTeam(int $teamId): void
    {
        $user = $this->currentUser();
        $team = $user->teams()->findOrFail($teamId);

        $user->forceFill(['current_team_id' => $team->id])->save();

        $this->redirectRoute('sprints.index', $team, navigate: true);
    }

    public function render(): View
    {
        $teams = $this->currentUser()->teams()->withCount('sprints')->get();

        return view('livewire.teams.index', ['teams' => $teams]);
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
