<?php

use App\Enums\ImpedimentPriority;
use App\Enums\ImpedimentStatus;
use App\Livewire\Impediments\Index;
use App\Models\Sprint;
use App\Models\User;
use Livewire\Livewire;

test('the owner can view the impediments page', function () {
    [$team, $owner] = createTeamForOwner();

    $this->actingAs($owner)
        ->get(route('teams.impediments', $team))
        ->assertOk();
});

test('a user who does not own the team cannot view its impediments', function () {
    [$team] = createTeamForOwner();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('teams.impediments', $team))
        ->assertForbidden();
});

test('the owner can report a new impediment', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(Index::class, ['team' => $team])
        ->set('form.title', 'CI-Pipeline ist down')
        ->set('form.description', 'Deploys sind seit heute Morgen blockiert.')
        ->set('form.priority', ImpedimentPriority::High->value)
        ->set('form.reportedBy', 'Ada Lovelace')
        ->call('save')
        ->assertHasNoErrors();

    $impediment = $team->fresh()->impediments->first();

    expect($impediment)->not->toBeNull();
    expect($impediment->title)->toBe('CI-Pipeline ist down');
    expect($impediment->priority)->toBe(ImpedimentPriority::High);
    expect($impediment->status)->toBe(ImpedimentStatus::Open);
    expect($impediment->reported_by)->toBe('Ada Lovelace');
});

test('a title is required to report an impediment', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(Index::class, ['team' => $team])
        ->set('form.title', '')
        ->call('save')
        ->assertHasErrors('form.title');

    expect($team->fresh()->impediments)->toBeEmpty();
});

test('an impediment can optionally be linked to a sprint of the same team', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();

    Livewire::actingAs($owner)
        ->test(Index::class, ['team' => $team])
        ->set('form.title', 'Blocker im Sprint')
        ->set('form.sprintId', $sprint->id)
        ->call('save')
        ->assertHasNoErrors();

    $impediment = $team->fresh()->impediments->first();

    expect($impediment->sprint_id)->toBe($sprint->id);
});

test('an impediment cannot be linked to a sprint of another team', function () {
    [$team, $owner] = createTeamForOwner();
    [$otherTeam] = createTeamForOwner();
    $foreignSprint = Sprint::factory()->for($otherTeam)->create();

    Livewire::actingAs($owner)
        ->test(Index::class, ['team' => $team])
        ->set('form.title', 'Blocker')
        ->set('form.sprintId', $foreignSprint->id)
        ->call('save')
        ->assertHasErrors('form.sprintId');
});

test('the owner can edit an existing impediment', function () {
    [$team, $owner] = createTeamForOwner();
    $impediment = $team->impediments()->create([
        'title' => 'Alter Titel',
        'priority' => ImpedimentPriority::Low->value,
    ]);

    Livewire::actingAs($owner)
        ->test(Index::class, ['team' => $team])
        ->call('startEditing', $impediment->id)
        ->set('form.title', 'Neuer Titel')
        ->set('form.priority', ImpedimentPriority::Critical->value)
        ->call('save')
        ->assertHasNoErrors();

    expect($impediment->fresh()->title)->toBe('Neuer Titel');
    expect($impediment->fresh()->priority)->toBe(ImpedimentPriority::Critical);
});

test('the owner can escalate, resolve and reopen an impediment', function () {
    [$team, $owner] = createTeamForOwner();
    $impediment = $team->impediments()->create(['title' => 'Blocker']);

    $component = Livewire::actingAs($owner)->test(Index::class, ['team' => $team]);

    $component->call('escalate', $impediment->id);
    expect($impediment->fresh()->status)->toBe(ImpedimentStatus::Escalated);
    expect($impediment->fresh()->escalated_at)->not->toBeNull();

    $component->call('resolve', $impediment->id);
    expect($impediment->fresh()->status)->toBe(ImpedimentStatus::Resolved);
    expect($impediment->fresh()->resolved_at)->not->toBeNull();

    $component->call('reopen', $impediment->id);
    expect($impediment->fresh()->status)->toBe(ImpedimentStatus::Open);
    expect($impediment->fresh()->escalated_at)->toBeNull();
    expect($impediment->fresh()->resolved_at)->toBeNull();
});

test('the owner can delete an impediment', function () {
    [$team, $owner] = createTeamForOwner();
    $impediment = $team->impediments()->create(['title' => 'Blocker']);

    Livewire::actingAs($owner)
        ->test(Index::class, ['team' => $team])
        ->call('delete', $impediment->id);

    expect($team->fresh()->impediments)->toBeEmpty();
});

test('the status filter narrows down the visible impediments', function () {
    [$team, $owner] = createTeamForOwner();
    $open = $team->impediments()->create(['title' => 'Datenbank-Migration blockiert']);
    $resolved = $team->impediments()->create(['title' => 'API-Key ausgelaufen', 'status' => ImpedimentStatus::Resolved->value, 'resolved_at' => now()]);

    $component = Livewire::actingAs($owner)->test(Index::class, ['team' => $team]);

    $component->assertSee('Datenbank-Migration blockiert')->assertDontSee('API-Key ausgelaufen');

    $component->set('statusFilter', 'resolved');
    $component->assertDontSee('Datenbank-Migration blockiert')->assertSee('API-Key ausgelaufen');

    $component->set('statusFilter', 'all');
    $component->assertSee('Datenbank-Migration blockiert')->assertSee('API-Key ausgelaufen');
});
