<?php

use App\Models\Editor;
use App\Models\Post;
use Laravel\Sanctum\Sanctum;

test('a request without a token is rejected', function () {
    $this->postJson('/api/posts', ['title' => 'Test', 'body' => 'Body'])
        ->assertUnauthorized();
});

test('an editor can create a draft post with a generated slug', function () {
    $editor = Editor::factory()->create();
    Sanctum::actingAs($editor);

    $response = $this->postJson('/api/posts', [
        'title' => 'Wie Scrumly Teams hilft',
        'body' => 'Inhalt des Artikels.',
    ])->assertCreated();

    $response->assertJsonPath('data.slug', 'wie-scrumly-teams-hilft');
    $response->assertJsonPath('data.published_at', null);

    $post = Post::first();
    expect($post->editor_id)->toBe($editor->id);
    expect($post->isPublished())->toBeFalse();
});

test('creating a post with published true publishes it immediately', function () {
    $editor = Editor::factory()->create();
    Sanctum::actingAs($editor);

    $this->postJson('/api/posts', [
        'title' => 'Live-Artikel',
        'body' => 'Inhalt.',
        'published' => true,
    ])->assertCreated();

    expect(Post::first()->isPublished())->toBeTrue();
});

test('the generated slug is deduplicated on collision', function () {
    $editor = Editor::factory()->create();
    Post::factory()->for($editor)->create(['title' => 'Gleicher Titel', 'slug' => 'gleicher-titel']);
    Sanctum::actingAs($editor);

    $response = $this->postJson('/api/posts', [
        'title' => 'Gleicher Titel',
        'body' => 'Inhalt.',
    ])->assertCreated();

    $response->assertJsonPath('data.slug', 'gleicher-titel-2');
});

test('an editor cannot update or delete another editors post', function () {
    $owner = Editor::factory()->create();
    $post = Post::factory()->for($owner)->create();
    $outsider = Editor::factory()->create();
    Sanctum::actingAs($outsider);

    $this->patchJson("/api/posts/{$post->id}", ['title' => 'Gehackt'])->assertForbidden();
    $this->deleteJson("/api/posts/{$post->id}")->assertForbidden();

    expect($post->fresh()->title)->not->toBe('Gehackt');
});

test('an editor can publish and unpublish their own post', function () {
    $editor = Editor::factory()->create();
    $post = Post::factory()->for($editor)->create();
    Sanctum::actingAs($editor);

    $this->postJson("/api/posts/{$post->id}/publish")->assertOk();
    expect($post->fresh()->isPublished())->toBeTrue();

    $this->postJson("/api/posts/{$post->id}/unpublish")->assertOk();
    expect($post->fresh()->isPublished())->toBeFalse();
});

test('an editor can delete their own post', function () {
    $editor = Editor::factory()->create();
    $post = Post::factory()->for($editor)->create();
    Sanctum::actingAs($editor);

    $this->deleteJson("/api/posts/{$post->id}")->assertNoContent();

    expect(Post::find($post->id))->toBeNull();
});
