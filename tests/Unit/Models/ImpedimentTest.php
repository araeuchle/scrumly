<?php

use App\Enums\ImpedimentStatus;
use App\Models\Impediment;

test('escalating an open impediment sets its status and escalated_at timestamp', function () {
    $this->travelTo(now());

    $impediment = Impediment::factory()->create(['status' => ImpedimentStatus::Open->value]);

    $impediment->escalate();

    expect($impediment->status)->toBe(ImpedimentStatus::Escalated);
    expect($impediment->escalated_at)->not->toBeNull();
});

test('resolving an impediment sets its status and resolved_at timestamp', function () {
    $this->travelTo(now());

    $impediment = Impediment::factory()->escalated()->create();

    $impediment->resolve();

    expect($impediment->status)->toBe(ImpedimentStatus::Resolved);
    expect($impediment->resolved_at)->not->toBeNull();
});

test('reopening a resolved impediment resets it back to open and clears both timestamps', function () {
    $impediment = Impediment::factory()->resolved()->create();

    $impediment->reopen();

    expect($impediment->status)->toBe(ImpedimentStatus::Open);
    expect($impediment->escalated_at)->toBeNull();
    expect($impediment->resolved_at)->toBeNull();
});
