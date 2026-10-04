<?php

namespace App\Policies;

use App\Models\Editor;
use App\Models\Post;

class PostPolicy
{
    public function create(Editor $editor): bool
    {
        return true;
    }

    public function update(Editor $editor, Post $post): bool
    {
        return $post->editor_id === $editor->id;
    }

    public function delete(Editor $editor, Post $post): bool
    {
        return $post->editor_id === $editor->id;
    }
}
