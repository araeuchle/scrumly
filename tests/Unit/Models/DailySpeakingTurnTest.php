<?php

use App\Models\DailySpeakingTurn;

test('a turn is not active when it has no start time', function () {
    $turn = DailySpeakingTurn::factory()->make([
        'seconds' => 42,
        'started_at' => null,
    ]);

    expect($turn->isActive())->toBeFalse();
    expect($turn->currentSeconds())->toBe(42);
});

test('a turn is active when it has a start time', function () {
    $turn = DailySpeakingTurn::factory()->make([
        'seconds' => 0,
        'started_at' => now(),
    ]);

    expect($turn->isActive())->toBeTrue();
});

test('current seconds accumulates elapsed time while active', function () {
    $this->travelTo(now());

    $turn = DailySpeakingTurn::factory()->make([
        'seconds' => 10,
        'started_at' => now(),
    ]);

    $this->travel(5)->seconds();

    expect($turn->currentSeconds())->toBe(15);
});
