<?php

namespace App\Policies;

use App\Models\Impediment;
use App\Models\Team;
use App\Models\User;

class ImpedimentPolicy
{
    public function viewAny(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user);
    }

    public function view(User $user, Impediment $impediment): bool
    {
        return $impediment->team->isOwnedBy($user);
    }

    public function create(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user);
    }

    public function update(User $user, Impediment $impediment): bool
    {
        return $impediment->team->isOwnedBy($user);
    }

    public function delete(User $user, Impediment $impediment): bool
    {
        return $impediment->team->isOwnedBy($user);
    }
}
