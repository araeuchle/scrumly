<?php

namespace App\Console\Commands;

use App\Mail\DailyReminderDigest;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyReminderDigest extends Command
{
    protected $signature = 'reminders:send-daily';

    protected $description = 'Send the daily Scrum Master reminder digest to users who still have something to catch up on';

    public function handle(): void
    {
        User::query()->each(function (User $user): void {
            $teams = $user->teams()
                ->with([
                    'actionItems' => fn ($query) => $query->whereNull('one_on_one_id')->where('is_done', false)->latest(),
                    'teamMembers.oneOnOnes',
                ])
                ->get();

            $hasJournalEntryToday = $user->journalEntries()
                ->whereDate('entry_date', now()->toDateString())
                ->exists();

            $hasOverdueMember = $teams
                ->flatMap(fn ($team) => $team->teamMembers)
                ->contains(fn ($member) => $member->needsOneOnOneReminder());

            if (! $hasJournalEntryToday || $hasOverdueMember) {
                Mail::to($user)->queue(new DailyReminderDigest($user, $hasJournalEntryToday, $teams));
            }
        });
    }
}
