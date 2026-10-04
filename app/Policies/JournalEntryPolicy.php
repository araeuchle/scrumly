<?php

namespace App\Policies;

use App\Models\JournalEntry;
use App\Models\User;

class JournalEntryPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, JournalEntry $journalEntry): bool
    {
        return $journalEntry->user_id === $user->id;
    }

    public function update(User $user, JournalEntry $journalEntry): bool
    {
        return $journalEntry->user_id === $user->id;
    }

    public function delete(User $user, JournalEntry $journalEntry): bool
    {
        return $journalEntry->user_id === $user->id;
    }
}
