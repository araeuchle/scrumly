<?php

use App\Livewire\Magazine\Index;
use App\Livewire\Magazine\Show;
use App\Models\Post;
use Livewire\Livewire;

test('the magazine index only shows published posts', function () {
    $published = Post::factory()->published()->create(['title' => 'Veröffentlichter Artikel']);
    Post::factory()->create(['title' => 'Entwurf']);

    Livewire::test(Index::class)
        ->assertSee('Veröffentlichter Artikel')
        ->assertDontSee('Entwurf');
});

test('the magazine show page renders a published post', function () {
    $post = Post::factory()->published()->create(['title' => 'Mein Artikel', 'body' => 'Hallo Welt']);

    Livewire::test(Show::class, ['post' => $post])
        ->assertSee('Mein Artikel')
        ->assertSee('Hallo Welt');
});

test('visiting a draft post by slug returns a 404', function () {
    $post = Post::factory()->create();

    $this->get(route('magazine.show', $post))->assertNotFound();
});
