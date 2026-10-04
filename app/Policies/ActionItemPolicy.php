<?php

namespace App\Policies;

use App\Models\ActionItem;
use App\Models\Team;
use App\Models\User;

class ActionItemPolicy
{
    public function create(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user);
    }

    public function update(User $user, ActionItem $actionItem): bool
    {
        return $actionItem->team->isOwnedBy($user);
    }

    public function delete(User $user, ActionItem $actionItem): bool
    {
        return $actionItem->team->isOwnedBy($user);
    }
}
