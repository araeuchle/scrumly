<?php

namespace App\Policies;

use App\Models\RetroActionItem;
use App\Models\Team;
use App\Models\User;

class RetroActionItemPolicy
{
    public function create(User $user, Team $team): bool
    {
        return $team->isOwnedBy($user);
    }

    public function update(User $user, RetroActionItem $retroActionItem): bool
    {
        return $retroActionItem->team->isOwnedBy($user);
    }

    public function delete(User $user, RetroActionItem $retroActionItem): bool
    {
        return $retroActionItem->team->isOwnedBy($user);
    }
}
