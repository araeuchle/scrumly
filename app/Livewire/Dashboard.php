<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        $teams = $this->currentUser()->teams()
            ->with([
                'actionItems' => fn ($query) => $query->whereNull('one_on_one_id')->where('is_done', false)->latest(),
                'teamMembers.oneOnOnes',
            ])
            ->get();

        return view('livewire.dashboard', ['teams' => $teams]);
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
