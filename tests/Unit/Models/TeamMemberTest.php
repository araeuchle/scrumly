<?php

use App\Models\TeamMember;

test('fullName combines first and last name', function () {
    $member = TeamMember::factory()->make([
        'first_name' => 'Ada',
        'last_name' => 'Lovelace',
    ]);

    expect($member->fullName())->toBe('Ada Lovelace');
});
