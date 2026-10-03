<?php

namespace App\Policies;

use App\Models\Sprint;
use App\Models\Team;
use App\Models\User;

class SprintPolicy
{
    public function view(User $user, Sprint $sprint): bool
    {
        return $sprint->team->isOwnedBy($user);
    }

    public function create(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user);
    }

    public function update(User $user, Sprint $sprint): bool
    {
        return $sprint->team->isOwnedBy($user);
    }

    public function delete(User $user, Sprint $sprint): bool
    {
        return $sprint->team->isOwnedBy($user);
    }
}
