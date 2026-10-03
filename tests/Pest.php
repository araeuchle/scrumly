<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/**
 * Create a team owned by a freshly created scrum master user, returned as [$team, $owner].
 *
 * @return array{0: Team, 1: User}
 */
function createTeamForOwner(): array
{
    $owner = User::factory()->create();
    $team = Team::factory()->for($owner, 'owner')->create();

    return [$team, $owner];
}
