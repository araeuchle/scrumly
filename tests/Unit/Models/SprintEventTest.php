<?php

use App\Enums\SprintEventStatus;
use App\Models\SprintEvent;

test('remaining seconds equals the full duration before the event has started', function () {
    $event = SprintEvent::factory()->make([
        'duration_minutes' => 15,
        'started_at' => null,
    ]);

    expect($event->remainingSeconds())->toBe(15 * 60);
});

test('remaining seconds decreases as time passes after starting', function () {
    $this->travelTo(now());

    $event = SprintEvent::factory()->make([
        'duration_minutes' => 15,
        'started_at' => now(),
    ]);

    $this->travel(5)->minutes();

    expect($event->remainingSeconds())->toBe(10 * 60);
});

test('an event is overtime only when in progress and time has run out', function () {
    $this->travelTo(now());

    $event = SprintEvent::factory()->make([
        'duration_minutes' => 15,
        'started_at' => now()->subMinutes(20),
        'status' => SprintEventStatus::InProgress->value,
    ]);

    expect($event->remainingSeconds())->toBeLessThan(0);
    expect($event->isOvertime())->toBeTrue();

    $event->status = SprintEventStatus::Completed;
    expect($event->isOvertime())->toBeFalse();
});

test('an event within its duration is not overtime', function () {
    $this->travelTo(now());

    $event = SprintEvent::factory()->make([
        'duration_minutes' => 15,
        'started_at' => now()->subMinutes(5),
        'status' => SprintEventStatus::InProgress->value,
    ]);

    expect($event->isOvertime())->toBeFalse();
});
