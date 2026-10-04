<?php

use App\Mail\DailyReminderDigest;
use App\Models\JournalEntry;
use App\Models\OneOnOne;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Mail;

test('a user without a journal entry for today receives the digest', function () {
    [, $owner] = createTeamForOwner();

    Mail::fake();

    $this->artisan('reminders:send-daily');

    Mail::assertQueued(DailyReminderDigest::class, fn ($mail) => $mail->user->is($owner));
});

test('a user with a journal entry today and no overdue one-on-ones does not receive the digest', function () {
    [$team, $owner] = createTeamForOwner();
    JournalEntry::factory()->for($owner)->create(['entry_date' => now()->toDateString()]);
    $member = TeamMember::factory()->for($team)->create();
    OneOnOne::factory()->for($member, 'teamMember')->create(['held_at' => now()->subDays(2)]);

    Mail::fake();

    $this->artisan('reminders:send-daily');

    Mail::assertNotQueued(DailyReminderDigest::class);
});

test('a user with a journal entry today but an overdue one-on-one still receives the digest', function () {
    [$team, $owner] = createTeamForOwner();
    JournalEntry::factory()->for($owner)->create(['entry_date' => now()->toDateString()]);
    TeamMember::factory()->for($team)->create();

    Mail::fake();

    $this->artisan('reminders:send-daily');

    Mail::assertQueued(DailyReminderDigest::class, fn ($mail) => $mail->user->is($owner));
});
