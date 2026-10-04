<?php

use App\Models\OneOnOne;
use App\Models\TeamMember;

test('a member with no one-on-one history needs a reminder', function () {
    $member = TeamMember::factory()->create();

    expect($member->needsOneOnOneReminder())->toBeTrue();
    expect($member->lastOneOnOneAt())->toBeNull();
});

test('a member needs a reminder once the last one-on-one is older than the threshold', function () {
    $member = TeamMember::factory()->create();
    OneOnOne::factory()->for($member, 'teamMember')->create([
        'held_at' => now()->subWeeks(TeamMember::ONE_ON_ONE_REMINDER_WEEKS + 1),
    ]);

    expect($member->needsOneOnOneReminder())->toBeTrue();
});

test('a member does not need a reminder shortly after a one-on-one', function () {
    $member = TeamMember::factory()->create();
    OneOnOne::factory()->for($member, 'teamMember')->create([
        'held_at' => now()->subDays(3),
    ]);

    expect($member->needsOneOnOneReminder())->toBeFalse();
});
