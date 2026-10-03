<?php

use App\Models\Team;
use App\Models\User;

test('resolveCurrentTeam returns the stored current team when it still belongs to the user', function () {
    $user = User::factory()->create();
    $firstTeam = Team::factory()->for($user, 'owner')->create();
    $secondTeam = Team::factory()->for($user, 'owner')->create();

    $user->forceFill(['current_team_id' => $secondTeam->id])->save();

    expect($user->resolveCurrentTeam()->id)->toBe($secondTeam->id);
});

test('resolveCurrentTeam falls back to the first team when none is stored', function () {
    $user = User::factory()->create();
    $firstTeam = Team::factory()->for($user, 'owner')->create();
    Team::factory()->for($user, 'owner')->create();

    expect($user->current_team_id)->toBeNull();
    expect($user->resolveCurrentTeam()->id)->toBe($firstTeam->id);
    expect($user->fresh()->current_team_id)->toBe($firstTeam->id);
});

test('resolveCurrentTeam falls back to the first team when the stored one no longer belongs to the user', function () {
    $user = User::factory()->create();
    $ownTeam = Team::factory()->for($user, 'owner')->create();
    $otherTeam = Team::factory()->create();

    $user->forceFill(['current_team_id' => $otherTeam->id])->save();

    expect($user->resolveCurrentTeam()->id)->toBe($ownTeam->id);
});

test('resolveCurrentTeam returns null for a user with no teams', function () {
    $user = User::factory()->create();

    expect($user->resolveCurrentTeam())->toBeNull();
});
