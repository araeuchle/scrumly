<?php

use App\Livewire\Sprints\Show;
use App\Models\Sprint;
use App\Models\SprintCapacity;
use App\Models\SprintEvent;
use App\Models\User;
use Livewire\Livewire;

test('the owner can view the sprint', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();

    $this->actingAs($owner)
        ->get(route('sprints.show', $sprint))
        ->assertOk();
});

test('a user who does not own the team cannot view the sprint', function () {
    [$team] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprints.show', $sprint))
        ->assertForbidden();
});

test('the owner can update a member capacity', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);
    $capacity = SprintCapacity::factory()->for($sprint)->for($member, 'teamMember')->create(['capacity_percent' => 100]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprint' => $sprint])
        ->call('updateCapacity', $capacity->id, 50);

    expect($capacity->fresh()->capacity_percent)->toBe(50);
});

test('capacity updates are clamped between 0 and 100', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);
    $capacity = SprintCapacity::factory()->for($sprint)->for($member, 'teamMember')->create(['capacity_percent' => 100]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprint' => $sprint])
        ->call('updateCapacity', $capacity->id, 150);

    expect($capacity->fresh()->capacity_percent)->toBe(100);

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprint' => $sprint])
        ->call('updateCapacity', $capacity->id, -20);

    expect($capacity->fresh()->capacity_percent)->toBe(0);
});

test('the owner can update a member capacity note', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);
    $capacity = SprintCapacity::factory()->for($sprint)->for($member, 'teamMember')->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprint' => $sprint])
        ->call('updateCapacityNote', $capacity->id, '3 Tage Urlaub');

    expect($capacity->fresh()->note)->toBe('3 Tage Urlaub');
});

test('a user who does not own the team cannot open the sprint to change its capacities', function () {
    [$team] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);
    $capacity = SprintCapacity::factory()->for($sprint)->for($member, 'teamMember')->create(['capacity_percent' => 100]);
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprints.show', $sprint))
        ->assertForbidden();

    expect($capacity->fresh()->capacity_percent)->toBe(100);
});

test('adding a recurring event occurrence twice for the same day creates only one event', function () {
    [$team, $owner] = createTeamForOwner();
    $daily = $team->eventTypes()->create([
        'name' => 'Daily', 'icon' => 'sun', 'default_duration_minutes' => 15,
        'default_agenda' => 'Sync up', 'is_recurring' => true, 'timing' => 'start', 'sort_order' => 0,
    ]);
    $sprint = Sprint::factory()->for($team)->create([
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addWeek(),
    ]);

    $component = Livewire::actingAs($owner)->test(Show::class, ['sprint' => $sprint]);
    $component->call('addEventOccurrence', $daily->id);
    $component->call('addEventOccurrence', $daily->id);

    expect($sprint->events()->where('team_event_type_id', $daily->id)->count())->toBe(1);
});

test('a non-recurring event type can also be added on demand from the sprint page', function () {
    [$team, $owner] = createTeamForOwner();
    $planning = $team->eventTypes()->create([
        'name' => 'Planning', 'icon' => 'clipboard-document-list', 'default_duration_minutes' => 120,
        'default_agenda' => 'Plan the sprint', 'is_recurring' => false, 'timing' => 'start', 'sort_order' => 0,
    ]);
    $sprint = Sprint::factory()->for($team)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprint' => $sprint])
        ->call('addEventOccurrence', $planning->id)
        ->assertRedirect();

    expect($sprint->events()->where('team_event_type_id', $planning->id)->exists())->toBeTrue();
});

test('the owner can remove an event from the sprint', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprint' => $sprint])
        ->call('deleteEvent', $event->id);

    expect(SprintEvent::find($event->id))->toBeNull();
});

test('a user who does not own the team cannot remove an event', function () {
    [$team] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprints.show', $sprint))
        ->assertForbidden();

    expect(SprintEvent::find($event->id))->not->toBeNull();
});
