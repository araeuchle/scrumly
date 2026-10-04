<?php

use App\Models\ActionItem;

test('completing an action item sets is_done and completed_at', function () {
    $this->travelTo(now());

    $actionItem = ActionItem::factory()->create();

    $actionItem->complete();

    expect($actionItem->is_done)->toBeTrue();
    expect($actionItem->completed_at)->not->toBeNull();
});

test('reopening an action item clears is_done and completed_at', function () {
    $actionItem = ActionItem::factory()->done()->create();

    $actionItem->reopen();

    expect($actionItem->is_done)->toBeFalse();
    expect($actionItem->completed_at)->toBeNull();
});
