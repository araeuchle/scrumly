<?php

use App\Enums\SprintEventStatus;
use App\Livewire\SprintEvents\Show;
use App\Models\DailySpeakingTurn;
use App\Models\Sprint;
use App\Models\SprintEvent;
use App\Models\TeamEventType;
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

test('editing the notes persists the change', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->set('notes', 'Blocker: API war down');

    expect($event->fresh()->notes)->toBe('Blocker: API war down');
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

test('editing the board url persists the change', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->set('boardUrl', 'https://miro.com/app/board/abc123/');

    expect($event->fresh()->board_url)->toBe('https://miro.com/app/board/abc123/');
});

test('the retro board and action items only show for a retrospective event type', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $regularEvent = SprintEvent::factory()->for($sprint)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $regularEvent])
        ->assertDontSee('Offene Action Items');
});

test('the owner can add a retro action item that belongs to the team', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $retroType = TeamEventType::factory()->for($team)->retrospective()->create();
    $event = SprintEvent::factory()->for($sprint)->for($retroType, 'teamEventType')->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->set('actionItemForm.description', 'CI-Pipeline beschleunigen')
        ->call('addActionItem')
        ->assertHasNoErrors();

    $actionItem = $team->fresh()->actionItems->first();

    expect($actionItem)->not->toBeNull();
    expect($actionItem->description)->toBe('CI-Pipeline beschleunigen');
    expect($actionItem->is_done)->toBeFalse();
    expect($actionItem->sprint_event_id)->toBe($event->id);
});

test('a description is required to add a retro action item', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $retroType = TeamEventType::factory()->for($team)->retrospective()->create();
    $event = SprintEvent::factory()->for($sprint)->for($retroType, 'teamEventType')->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $event])
        ->set('actionItemForm.description', '')
        ->call('addActionItem')
        ->assertHasErrors('actionItemForm.description');

    expect($team->fresh()->actionItems)->toBeEmpty();
});

test('the owner can complete, reopen and delete a retro action item', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $retroType = TeamEventType::factory()->for($team)->retrospective()->create();
    $event = SprintEvent::factory()->for($sprint)->for($retroType, 'teamEventType')->create();
    $actionItem = $team->actionItems()->create(['description' => 'Pairing etablieren', 'sprint_event_id' => $event->id]);

    $component = Livewire::actingAs($owner)->test(Show::class, ['sprintEvent' => $event]);

    $component->call('completeActionItem', $actionItem->id);
    expect($actionItem->fresh()->is_done)->toBeTrue();
    expect($actionItem->fresh()->completed_at)->not->toBeNull();

    $component->call('reopenActionItem', $actionItem->id);
    expect($actionItem->fresh()->is_done)->toBeFalse();

    $component->call('deleteActionItem', $actionItem->id);
    expect($team->fresh()->actionItems)->toBeEmpty();
});

test('open action items from an earlier retro still appear on a later retro of a new sprint', function () {
    [$team, $owner] = createTeamForOwner();
    $retroType = TeamEventType::factory()->for($team)->retrospective()->create();

    $firstSprint = Sprint::factory()->for($team)->create();
    $firstRetro = SprintEvent::factory()->for($firstSprint, 'sprint')->for($retroType, 'teamEventType')->create();
    $team->actionItems()->create(['description' => 'Deploy-Prozess dokumentieren', 'sprint_event_id' => $firstRetro->id]);

    $secondSprint = Sprint::factory()->for($team)->create();
    $secondRetro = SprintEvent::factory()->for($secondSprint, 'sprint')->for($retroType, 'teamEventType')->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['sprintEvent' => $secondRetro])
        ->assertSee('Deploy-Prozess dokumentieren');
});

test('a user who does not own the team cannot manage retro action items', function () {
    [$team] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $retroType = TeamEventType::factory()->for($team)->retrospective()->create();
    $event = SprintEvent::factory()->for($sprint)->for($retroType, 'teamEventType')->create();
    $actionItem = $team->actionItems()->create(['description' => 'Pairing etablieren', 'sprint_event_id' => $event->id]);
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('sprint-events.show', $event))
        ->assertForbidden();

    expect($actionItem->fresh()->is_done)->toBeFalse();
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
