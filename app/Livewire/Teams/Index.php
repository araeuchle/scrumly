<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Illuminate\Contracts\View\View;
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

        $team = auth()->user()->teams()->create($validated);

        $this->redirectRoute('sprints.index', $team, navigate: true);
    }

    public function render(): View
    {
        $teams = auth()->user()->teams()->withCount('sprints')->get();

        return view('livewire.teams.index', ['teams' => $teams]);
    }
}
