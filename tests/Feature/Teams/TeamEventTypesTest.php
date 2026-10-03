<?php

use App\Livewire\Teams\EventTypes;
use App\Models\User;
use Livewire\Livewire;

test('the owner can add an event type', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(EventTypes::class, ['team' => $team])
        ->set('name', 'Daily')
        ->set('icon', 'sun')
        ->set('defaultDurationMinutes', 15)
        ->set('defaultAgenda', 'Sync up')
        ->set('isRecurring', true)
        ->set('trackSpeakingTime', true)
        ->call('save')
        ->assertHasNoErrors();

    $eventType = $team->fresh()->eventTypes->first();

    expect($eventType)->not->toBeNull();
    expect($eventType->name)->toBe('Daily');
    expect($eventType->is_recurring)->toBeTrue();
    expect($eventType->track_speaking_time)->toBeTrue();
});

test('a name is required to add an event type', function () {
    [$team, $owner] = createTeamForOwner();

    Livewire::actingAs($owner)
        ->test(EventTypes::class, ['team' => $team])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors('name');

    expect($team->fresh()->eventTypes)->toBeEmpty();
});

test('the owner can edit an existing event type', function () {
    [$team, $owner] = createTeamForOwner();
    $eventType = $team->eventTypes()->create([
        'name' => 'Planning', 'icon' => 'calendar-days', 'default_duration_minutes' => 60,
        'is_recurring' => false, 'timing' => 'start', 'sort_order' => 0,
    ]);

    Livewire::actingAs($owner)
        ->test(EventTypes::class, ['team' => $team])
        ->call('startEditing', $eventType->id)
        ->set('name', 'Sprint Planning')
        ->set('defaultDurationMinutes', 120)
        ->call('save')
        ->assertHasNoErrors();

    expect($eventType->fresh()->name)->toBe('Sprint Planning');
    expect($eventType->fresh()->default_duration_minutes)->toBe(120);
});

test('the owner can delete an event type', function () {
    [$team, $owner] = createTeamForOwner();
    $eventType = $team->eventTypes()->create([
        'name' => 'Planning', 'icon' => 'calendar-days', 'default_duration_minutes' => 60,
        'is_recurring' => false, 'timing' => 'start', 'sort_order' => 0,
    ]);

    Livewire::actingAs($owner)
        ->test(EventTypes::class, ['team' => $team])
        ->call('delete', $eventType->id);

    expect($team->fresh()->eventTypes)->toBeEmpty();
});

test('the owner can reorder event types', function () {
    [$team, $owner] = createTeamForOwner();
    $first = $team->eventTypes()->create([
        'name' => 'Planning', 'icon' => 'calendar-days', 'default_duration_minutes' => 60,
        'is_recurring' => false, 'timing' => 'start', 'sort_order' => 0,
    ]);
    $second = $team->eventTypes()->create([
        'name' => 'Review', 'icon' => 'presentation-chart-line', 'default_duration_minutes' => 60,
        'is_recurring' => false, 'timing' => 'end', 'sort_order' => 1,
    ]);

    Livewire::actingAs($owner)
        ->test(EventTypes::class, ['team' => $team])
        ->call('moveDown', $first->id);

    expect($first->fresh()->sort_order)->toBe(1);
    expect($second->fresh()->sort_order)->toBe(0);
});

test('a user who does not own the team cannot manage its event types', function () {
    [$team] = createTeamForOwner();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('teams.event-types', $team))
        ->assertForbidden();
});
