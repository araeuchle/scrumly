<?php

use App\Livewire\Journal\Index;
use App\Models\JournalEntry;
use App\Models\User;
use Livewire\Livewire;

test('a user can create a journal entry for today', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('startCreating')
        ->set('form.wentWell', 'Gutes Daily gehabt')
        ->set('form.onMyMind', 'Sorge um Deadline')
        ->set('form.shouldIntervene', 'Mit Team X sprechen')
        ->call('toggleFormEmotion', 'motivated')
        ->call('toggleFormEmotion', 'stressed')
        ->call('save')
        ->assertHasNoErrors();

    $entry = $user->journalEntries()->first();

    expect($entry)->not->toBeNull();
    expect($entry->entry_date->toDateString())->toBe(now()->toDateString());
    expect($entry->went_well)->toBe('Gutes Daily gehabt');
    expect($entry->emotions)->toEqualCanonicalizing(['motivated', 'stressed']);
});

test('only one entry per day is allowed', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->for($user)->create(['entry_date' => now()->toDateString()]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('startCreating')
        ->set('form.wentWell', 'Noch ein Eintrag')
        ->call('save')
        ->assertHasErrors('form.entryDate');

    expect($user->journalEntries()->count())->toBe(1);
});

test('a user can edit and delete their own journal entry', function () {
    $user = User::factory()->create();
    $entry = JournalEntry::factory()->for($user)->create(['went_well' => 'Alt']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('startEditing', $entry->id)
        ->set('form.wentWell', 'Neu')
        ->call('save')
        ->assertHasNoErrors();

    expect($entry->fresh()->went_well)->toBe('Neu');

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('delete', $entry->id);

    expect($user->journalEntries()->count())->toBe(0);
});

test('a user cannot edit or delete another users journal entry', function () {
    $owner = User::factory()->create();
    $entry = JournalEntry::factory()->for($owner)->create();
    $outsider = User::factory()->create();

    Livewire::actingAs($outsider)
        ->test(Index::class)
        ->call('startEditing', $entry->id)
        ->assertForbidden();

    Livewire::actingAs($outsider)
        ->test(Index::class)
        ->call('delete', $entry->id)
        ->assertForbidden();

    expect($entry->fresh())->not->toBeNull();
});

test('the search filter matches text across all three answers', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->for($user)->create(['went_well' => 'CI-Pipeline lief stabil', 'on_my_mind' => '', 'should_intervene' => '']);
    JournalEntry::factory()->for($user)->create(['went_well' => '', 'on_my_mind' => 'Unklare Prioritäten', 'should_intervene' => '']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('search', 'Prioritäten')
        ->assertSee('Unklare Prioritäten')
        ->assertDontSee('CI-Pipeline lief stabil');
});

test('the date range filter excludes entries outside the range', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->for($user)->create(['entry_date' => '2026-01-10', 'went_well' => 'Im Januar']);
    JournalEntry::factory()->for($user)->create(['entry_date' => '2026-03-10', 'went_well' => 'Im März']);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-01-31')
        ->assertSee('Im Januar')
        ->assertDontSee('Im März');
});

test('the emotion filter only shows entries tagged with the selected emotion', function () {
    $user = User::factory()->create();
    JournalEntry::factory()->for($user)->create(['went_well' => 'Stressiger Tag', 'emotions' => ['stressed']]);
    JournalEntry::factory()->for($user)->create(['went_well' => 'Entspannter Tag', 'emotions' => ['satisfied']]);

    Livewire::actingAs($user)
        ->test(Index::class)
        ->call('toggleEmotionFilter', 'stressed')
        ->assertSee('Stressiger Tag')
        ->assertDontSee('Entspannter Tag');
});
