<?php

namespace App\Livewire\Magazine;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        if (! $post->isPublished()) {
            abort(404);
        }

        $this->post = $post;
    }

    public function render(): View
    {
        return view('livewire.magazine.show');
    }
}
