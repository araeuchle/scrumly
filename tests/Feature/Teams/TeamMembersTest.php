<?php

use App\Livewire\Teams\Members;
use App\Models\User;
use Livewire\Livewire;

test('a team owner can add a team member entry', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(Members::class, ['team' => $team])
        ->set('firstName', 'Ada')
        ->set('lastName', 'Lovelace')
        ->set('defaultCapacityPercent', 80)
        ->call('addMember')
        ->assertHasNoErrors();

    $member = $team->fresh()->teamMembers->first();

    expect($member)->not->toBeNull();
    expect($member->fullName())->toBe('Ada Lovelace');
    expect($member->default_capacity_percent)->toBe(80);
});

test('first and last name are required to add a member', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(Members::class, ['team' => $team])
        ->set('firstName', '')
        ->set('lastName', '')
        ->call('addMember')
        ->assertHasErrors(['firstName', 'lastName']);

    expect($team->fresh()->teamMembers)->toBeEmpty();
});

test('a team owner can update a member default capacity', function () {
    [$team, $owner] = createTeamForOwner();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);

    Livewire::actingAs($owner)
        ->test(Members::class, ['team' => $team])
        ->call('updateCapacity', $member->id, 50);

    expect($member->fresh()->default_capacity_percent)->toBe(50);
});

test('capacity updates are clamped between 0 and 100', function () {
    [$team, $owner] = createTeamForOwner();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);

    Livewire::actingAs($owner)
        ->test(Members::class, ['team' => $team])
        ->call('updateCapacity', $member->id, 150);

    expect($member->fresh()->default_capacity_percent)->toBe(100);
});

test('a team owner can remove a member', function () {
    [$team, $owner] = createTeamForOwner();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);

    Livewire::actingAs($owner)
        ->test(Members::class, ['team' => $team])
        ->call('removeMember', $member->id);

    expect($team->fresh()->teamMembers)->toBeEmpty();
});

test('a user who does not own the team cannot manage its members', function () {
    [$team] = createTeamForOwner();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('teams.members', $team))
        ->assertForbidden();
});
