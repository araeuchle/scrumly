<?php

use App\Livewire\Teams\Metrics;
use App\Models\DailySpeakingTurn;
use App\Models\Impediment;
use App\Models\Sprint;
use App\Models\SprintCapacity;
use App\Models\SprintEvent;
use App\Models\TeamEventType;
use App\Models\TeamMember;
use App\Models\User;
use Livewire\Livewire;

test('the owner can view team metrics', function () {
    [$team, $owner] = createTeamForOwner();

    $this->actingAs($owner)
        ->get(route('teams.metrics', $team))
        ->assertOk();
});

test('a user who does not own the team cannot view team metrics', function () {
    [$team] = createTeamForOwner();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('teams.metrics', $team))
        ->assertForbidden();
});

test('it measures how often an event type overruns its planned duration', function () {
    [$team, $owner] = createTeamForOwner();
    $eventType = TeamEventType::factory()->for($team)->create(['default_duration_minutes' => 15]);
    $sprint = Sprint::factory()->for($team)->create();

    SprintEvent::factory()->for($sprint)->for($eventType, 'teamEventType')->create([
        'duration_minutes' => 15,
        'status' => 'completed',
        'started_at' => now()->subMinutes(30),
        'ended_at' => now(),
    ]);

    $overruns = Livewire::actingAs($owner)
        ->test(Metrics::class, ['team' => $team])
        ->viewData('eventOverruns');

    expect($overruns)->toHaveCount(1);
    expect($overruns[0]['total'])->toBe(1);
    expect($overruns[0]['overrunCount'])->toBe(1);
    expect($overruns[0]['overrunRate'])->toBe(100.0);
});

test('it summarizes impediment counts and average resolution time', function () {
    [$team, $owner] = createTeamForOwner();

    Impediment::factory()->for($team)->create(['status' => 'open']);
    Impediment::factory()->for($team)->create(['status' => 'open']);
    Impediment::factory()->for($team)->create(['status' => 'escalated', 'escalated_at' => now()]);
    Impediment::factory()->for($team)->create([
        'status' => 'resolved',
        'created_at' => now()->subDays(5),
        'resolved_at' => now(),
    ]);

    $stats = Livewire::actingAs($owner)
        ->test(Metrics::class, ['team' => $team])
        ->viewData('impedimentStats');

    expect($stats)->toBe([
        'open' => 2,
        'escalated' => 1,
        'resolvedCount' => 1,
        'avgResolutionDays' => 5.0,
    ]);
});

test('it computes the sprint event completion rate and average planned capacity', function () {
    [$team, $owner] = createTeamForOwner();
    $sprint = Sprint::factory()->for($team)->create();
    $member = TeamMember::factory()->for($team)->create();

    SprintCapacity::factory()->for($sprint)->for($member)->create(['capacity_percent' => 80]);
    SprintEvent::factory()->for($sprint)->create(['status' => 'completed']);
    SprintEvent::factory()->for($sprint)->create(['status' => 'scheduled']);

    $recentSprints = Livewire::actingAs($owner)
        ->test(Metrics::class, ['team' => $team])
        ->viewData('recentSprints');

    expect($recentSprints)->toHaveCount(1);
    expect($recentSprints[0]['eventTotal'])->toBe(2);
    expect($recentSprints[0]['eventCompleted'])->toBe(1);
    expect($recentSprints[0]['completionRate'])->toBe(50.0);
    expect($recentSprints[0]['avgCapacity'])->toBe(80.0);
});

test('it measures speaking time balance across team members for the latest sprint', function () {
    [$team, $owner] = createTeamForOwner();
    $eventType = TeamEventType::factory()->for($team)->create(['track_speaking_time' => true]);
    $sprint = Sprint::factory()->for($team)->create();
    $event = SprintEvent::factory()->for($sprint)->for($eventType, 'teamEventType')->create();

    $quietMember = TeamMember::factory()->for($team)->create();
    $talkativeMember = TeamMember::factory()->for($team)->create();

    DailySpeakingTurn::factory()->for($event, 'sprintEvent')->for($quietMember, 'teamMember')->create(['seconds' => 30]);
    DailySpeakingTurn::factory()->for($event, 'sprintEvent')->for($talkativeMember, 'teamMember')->create(['seconds' => 90]);

    $speakingBalance = Livewire::actingAs($owner)
        ->test(Metrics::class, ['team' => $team])
        ->viewData('speakingBalance');

    expect($speakingBalance['maxSeconds'])->toBe(90);
    expect($speakingBalance['totals'][0]['totalSeconds'])->toBe(90);
    expect($speakingBalance['totals'][1]['totalSeconds'])->toBe(30);
});

test('it lowers the team health score when impediments pile up', function () {
    [$team, $owner] = createTeamForOwner();

    Impediment::factory()->for($team)->create(['status' => 'open']);
    Impediment::factory()->for($team)->create(['status' => 'open']);

    $teamHealth = Livewire::actingAs($owner)
        ->test(Metrics::class, ['team' => $team])
        ->viewData('teamHealth');

    expect($teamHealth['score'])->toBe(70.0);
    expect($teamHealth['label'])->toBe('Mittel');
});

test('a team with no activity has a perfect team health score', function () {
    [$team, $owner] = createTeamForOwner();

    $teamHealth = Livewire::actingAs($owner)
        ->test(Metrics::class, ['team' => $team])
        ->viewData('teamHealth');

    expect($teamHealth['score'])->toBe(100.0);
    expect($teamHealth['label'])->toBe('Gut');
});
