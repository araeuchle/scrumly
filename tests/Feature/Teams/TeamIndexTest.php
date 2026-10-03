<?php

use App\Livewire\Teams\Index;
use App\Models\Team;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to the login page', function () {
    $this->get(route('teams.index'))->assertRedirect(route('login'));
});

test('authenticated users can create a team and become its owner', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('name', 'Team Phoenix')
        ->call('createTeam');

    $team = Team::where('name', 'Team Phoenix')->first();

    expect($team)->not->toBeNull();
    expect($team->isOwnedBy($user))->toBeTrue();
});

test('the team list only shows teams the user owns', function () {
    [$ownTeam, $user] = createTeamForOwner();
    $otherTeam = Team::factory()->create();

    $component = Livewire::actingAs($user)->test(Index::class);

    expect($component->viewData('teams')->pluck('id'))
        ->toContain($ownTeam->id)
        ->not->toContain($otherTeam->id);
});
