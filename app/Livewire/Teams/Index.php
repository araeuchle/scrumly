<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public string $name = '';

    public bool $showCreateForm = false;

    public function createTeam(): void
    {
        $this->authorize('create', Team::class);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $team = $this->currentUser()->teams()->create($validated);

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
