<?php

use App\Models\Team;
use App\Models\User;

test('isOwnedBy returns true for the owner and false for another user', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();

    expect($team->isOwnedBy($owner))->toBeTrue();
    expect($team->isOwnedBy($otherUser))->toBeFalse();
});
