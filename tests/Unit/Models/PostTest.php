<?php

use App\Models\Post;

test('the published scope only returns posts with a past or present published_at', function () {
    $published = Post::factory()->published()->create();
    Post::factory()->create();
    Post::factory()->create(['published_at' => now()->addDay()]);

    $results = Post::published()->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($published->id);
});

test('isPublished reflects the published_at timestamp', function () {
    $draft = Post::factory()->create();
    $published = Post::factory()->published()->create();
    $scheduled = Post::factory()->create(['published_at' => now()->addDay()]);

    expect($draft->isPublished())->toBeFalse();
    expect($published->isPublished())->toBeTrue();
    expect($scheduled->isPublished())->toBeFalse();
});
