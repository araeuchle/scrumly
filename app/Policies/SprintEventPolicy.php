<?php

namespace App\Policies;

use App\Models\SprintEvent;
use App\Models\User;

class SprintEventPolicy
{
    public function view(User $user, SprintEvent $sprintEvent): bool
    {
        return $sprintEvent->sprint->team->isOwnedBy($user);
    }

    public function update(User $user, SprintEvent $sprintEvent): bool
    {
        return $sprintEvent->sprint->team->isOwnedBy($user);
    }
}
