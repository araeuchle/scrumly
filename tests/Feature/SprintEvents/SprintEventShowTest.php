<?php

use App\Enums\SprintEventStatus;
use App\Livewire\SprintEvents\Show;
use App\Models\DailySpeakingTurn;
use App\Models\Sprint;
use App\Models\SprintEvent;
use App\Models\User;
use Livewire\Livewire;

test('the owner can view a sprint event', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();

    $this->actingAs($owner)
        ->get(route('sprint-events.show', $event))
        ->assertOk();
});

test('a user who does not own the team cannot view a sprint event', function () {
    [$team] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprint-events.show', $event))
        ->assertForbidden();
});

test('the owner can start an event', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->call('start');

    $event->refresh();

    expect($event->status)->toBe(SprintEventStatus::InProgress);
    expect($event->started_at)->not->toBeNull();
});

test('the owner can complete an event and running speaking turns stop', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->inProgress()->create();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);

    $turn = DailySpeakingTurn::factory()->for($event, 'sprintEvent')->for($member, 'teamMember')->create([
        'started_at' => now()->subSeconds(10),
        'seconds' => 0,
    ]);

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->call('complete');

    $event->refresh();
    $turn->refresh();

    expect($event->status)->toBe(SprintEventStatus::Completed);
    expect($event->ended_at)->not->toBeNull();
    expect($turn->started_at)->toBeNull();
    expect($turn->seconds)->toBeGreaterThanOrEqual(10);
});

test('editing the agenda persists the change', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->set('agenda', 'Updated agenda text');

    expect($event->fresh()->agenda)->toBe('Updated agenda text');
});

test('a user who does not own the team cannot start or edit an event', function () {
    [$team] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprint-events.show', $event))
        ->assertForbidden();

    expect($event->fresh()->status)->toBe(SprintEventStatus::Scheduled);
});

test('the owner can start and stop a team member speaking turn', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->inProgress()->create();
    $member = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);

    $component = Livewire::actingAs($owner)->test(Show::class, ['sprintEvent' => $event]);

    $component->call('toggleSpeaking', $member->id);

    $turn = DailySpeakingTurn::where('sprint_event_id', $event->id)->where('team_member_id', $member->id)->first();
    expect($turn->isActive())->toBeTrue();

    $component->call('toggleSpeaking', $member->id);

    expect($turn->fresh()->isActive())->toBeFalse();
    expect($turn->fresh()->seconds)->toBeGreaterThanOrEqual(0);
});

test('starting a new speaking turn stops any other active turn', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->inProgress()->create();
    $memberOne = $team->teamMembers()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace', 'default_capacity_percent' => 100]);
    $memberTwo = $team->teamMembers()->create(['first_name' => 'Grace', 'last_name' => 'Hopper', 'default_capacity_percent' => 100]);

    $component = Livewire::actingAs($owner)->test(Show::class, ['sprintEvent' => $event]);

    $component->call('toggleSpeaking', $memberOne->id);
    $component->call('toggleSpeaking', $memberTwo->id);

    $turnOne = DailySpeakingTurn::where('sprint_event_id', $event->id)->where('team_member_id', $memberOne->id)->first();
    $turnTwo = DailySpeakingTurn::where('sprint_event_id', $event->id)->where('team_member_id', $memberTwo->id)->first();

    expect($turnOne->isActive())->toBeFalse();
    expect($turnTwo->isActive())->toBeTrue();
});
