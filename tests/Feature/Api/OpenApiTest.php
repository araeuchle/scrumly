<?php

use App\Models\Editor;
use Laravel\Sanctum\Sanctum;

test('a request without a token cannot read the openapi spec', function () {
    $this->get('/api/openapi.yaml')->assertUnauthorized();
});

test('an authenticated editor can read the openapi spec', function () {
    Sanctum::actingAs(Editor::factory()->create());

    $response = $this->get('/api/openapi.yaml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/yaml');

    expect($response->getContent())->toContain('openapi:')
        ->toContain('/posts');
});
