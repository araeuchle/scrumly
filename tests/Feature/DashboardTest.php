<?php

use App\Livewire\Dashboard;
use App\Models\ActionItem;
use App\Models\OneOnOne;
use App\Models\TeamMember;
use Livewire\Livewire;

test('the dashboard shows open retro action items for the users teams', function () {
    [$team, $owner] = createTeamForOwner();
    ActionItem::factory()->for($team)->create(['description' => 'CI-Pipeline beschleunigen']);
    ActionItem::factory()->for($team)->done()->create(['description' => 'Bereits erledigt']);

    Livewire::actingAs($owner)
        ->test(Dashboard::class)
        ->assertSee('CI-Pipeline beschleunigen')
        ->assertDontSee('Bereits erledigt');
});

test('the dashboard reminds about a member without a recent one-on-one', function () {
    [$team, $owner] = createTeamForOwner();
    $overdueMember = TeamMember::factory()->for($team)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
    $upToDateMember = TeamMember::factory()->for($team)->create(['first_name' => 'Grace', 'last_name' => 'Hopper']);
    OneOnOne::factory()->for($upToDateMember, 'teamMember')->create(['held_at' => now()->subDays(3)]);

    Livewire::actingAs($owner)
        ->test(Dashboard::class)
        ->assertSee('Ada Lovelace')
        ->assertDontSee('Grace Hopper');
});

test('one-on-one action items do not count as open retro action items on the dashboard', function () {
    [$team, $owner] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();
    $oneOnOne = OneOnOne::factory()->for($member, 'teamMember')->create();
    ActionItem::factory()->for($team)->create([
        'one_on_one_id' => $oneOnOne->id,
        'description' => 'Persönliches 1:1-Thema',
    ]);

    Livewire::actingAs($owner)
        ->test(Dashboard::class)
        ->assertDontSee('Persönliches 1:1-Thema');
});
