<?php

use App\Livewire\Teams\Switcher;
use App\Models\Team;
use Livewire\Livewire;

test('the switcher defaults to the current team or the first team', function () {
    [$team, $owner] = createTeamForOwner();
    Team::factory()->for($owner, 'owner')->create();

    $component = Livewire::actingAs($owner)->test(Switcher::class);

    expect($component->get('currentTeamId'))->toBe($team->id);
});

test('switching teams updates the user\'s current team and dispatches a navigation event', function () {
    [$firstTeam, $owner] = createTeamForOwner();
    $secondTeam = Team::factory()->for($owner, 'owner')->create();

    Livewire::actingAs($owner)
        ->test(Switcher::class)
        ->set('currentTeamId', $secondTeam->id)
        ->assertDispatched('team-switched', teamId: $secondTeam->id);

    expect($owner->fresh()->current_team_id)->toBe($secondTeam->id);
});

test('a user cannot switch to a team they do not own', function () {
    [$team, $owner] = createTeamForOwner();
    $otherTeam = Team::factory()->create();

    Livewire::actingAs($owner)
        ->test(Switcher::class)
        ->set('currentTeamId', $otherTeam->id)
        ->assertStatus(404);

    expect($owner->fresh()->current_team_id)->toBe($team->id);
});
