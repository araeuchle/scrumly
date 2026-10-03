<?php

use App\Livewire\Sprints\Create;
use App\Models\Sprint;
use App\Models\User;
use Livewire\Livewire;

test('the owner can create a sprint with auto-seeded capacities and non-recurring events', function () {
    [$team, $owner] = createTeamForOwner();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 60]);

    $planning = $team->eventTypes()->create([
        'name' => 'Planning', 'icon' => 'clipboard-document-list', 'default_duration_minutes' => 120,
        'default_agenda' => 'Plan the sprint', 'is_recurring' => false, 'timing' => 'start', 'sort_order' => 0,
    ]);
    $retro = $team->eventTypes()->create([
        'name' => 'Retro', 'icon' => 'arrow-path', 'default_duration_minutes' => 60,
        'default_agenda' => 'Reflect', 'is_recurring' => false, 'timing' => 'end', 'sort_order' => 1,
    ]);
    $daily = $team->eventTypes()->create([
        'name' => 'Daily', 'icon' => 'sun', 'default_duration_minutes' => 15,
        'default_agenda' => 'Sync up', 'is_recurring' => true, 'timing' => 'start', 'sort_order' => 2,
    ]);

    Livewire::actingAs($owner)
        ->test(Create::class, ['team' => $team])
        ->set('name', 'Sprint 1')
        ->set('goal', 'Ship the MVP')
        ->set('startsAt', '2026-01-01')
        ->set('endsAt', '2026-01-14')
        ->call('save')
        ->assertHasNoErrors();

    $sprint = Sprint::where('name', 'Sprint 1')->first();

    expect($sprint)->not->toBeNull();
    expect($sprint->goal)->toBe('Ship the MVP');

    expect($sprint->capacities()->count())->toBe(1);
    expect($sprint->capacities()->where('team_member_id', $member->id)->first()->capacity_percent)->toBe(60);

    $eventTypeIds = $sprint->events()->pluck('team_event_type_id')->all();

    expect($eventTypeIds)->toContain($planning->id, $retro->id)
        ->not->toContain($daily->id);

    $planningEvent = $sprint->events()->where('team_event_type_id', $planning->id)->first();
    $retroEvent = $sprint->events()->where('team_event_type_id', $retro->id)->first();

    expect($planningEvent->scheduled_date->toDateString())->toBe('2026-01-01');
    expect($retroEvent->scheduled_date->toDateString())->toBe('2026-01-14');
});

test('the sprint end date must be after or equal to the start date', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(Create::class, ['team' => $team])
        ->set('name', 'Sprint 1')
        ->set('startsAt', '2026-01-14')
        ->set('endsAt', '2026-01-01')
        ->call('save')
        ->assertHasErrors('endsAt');
});

test('a user who does not own the team cannot create a sprint', function () {
    [$team] = createTeamForOwner();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprints.create', $team))
        ->assertForbidden();
});
