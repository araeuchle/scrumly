<?php

use App\Models\TeamEventType;

test('timingLabel describes start and end timing', function () {
    $start = TeamEventType::factory()->make(['timing' => 'start']);
    $end = TeamEventType::factory()->make(['timing' => 'end']);

    expect($start->timingLabel())->toBe('Am Sprint-Start');
    expect($end->timingLabel())->toBe('Am Sprint-Ende');
});
