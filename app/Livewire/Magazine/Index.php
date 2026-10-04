<?php

namespace App\Livewire\Magazine;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Index extends Component
{
    public function render(): View
    {
        $posts = Post::published()->latest('published_at')->get();

        return view('livewire.magazine.index', ['posts' => $posts]);
    }
}
