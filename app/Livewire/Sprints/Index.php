<?php

namespace App\Livewire\Sprints;

use App\Models\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public Team $team;

    public function mount(Team $team): void
    {
        $this->authorize('view', $team);

        $this->team = $team;
    }

    public function render(): View
    {
        $sprints = $this->team->sprints()->latest('starts_at')->get();
        $hasEventTypes = $this->team->eventTypes()->exists();

        return view('livewire.sprints.index', ['sprints' => $sprints, 'hasEventTypes' => $hasEventTypes]);
    }
}
