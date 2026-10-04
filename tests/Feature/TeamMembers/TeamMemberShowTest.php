<?php

use App\Livewire\SprintEvents\Show as SprintEventShow;
use App\Livewire\TeamMembers\Show;
use App\Models\ActionItem;
use App\Models\OneOnOne;
use App\Models\Sprint;
use App\Models\SprintEvent;
use App\Models\TeamEventType;
use App\Models\TeamMember;
use App\Models\User;
use Livewire\Livewire;

test('the owner can view a team member', function () {
    [$team, $owner] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();

    $this->actingAs($owner)
        ->get(route('team-members.show', $member))
        ->assertOk();
});

test('a user who does not own the team cannot view a team member', function () {
    [$team] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get(route('team-members.show', $member))
        ->assertForbidden();
});

test('the owner can log a new one-on-one', function () {
    [$team, $owner] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();

    Livewire::actingAs($owner)
        ->test(Show::class, ['teamMember' => $member])
        ->set('form.heldAt', now()->toDateString())
        ->set('form.notes', 'Karriereentwicklung besprochen')
        ->call('save')
        ->assertHasNoErrors();

    $oneOnOne = $member->fresh()->oneOnOnes->first();

    expect($oneOnOne)->not->toBeNull();
    expect($oneOnOne->notes)->toBe('Karriereentwicklung besprochen');
});

test('the owner can edit and delete a one-on-one', function () {
    [$team, $owner] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();
    $oneOnOne = OneOnOne::factory()->for($member, 'teamMember')->create(['notes' => 'Alte Notiz']);

    Livewire::actingAs($owner)
        ->test(Show::class, ['teamMember' => $member])
        ->call('startEditing', $oneOnOne->id)
        ->set('form.notes', 'Aktualisierte Notiz')
        ->call('save')
        ->assertHasNoErrors();

    expect($oneOnOne->fresh()->notes)->toBe('Aktualisierte Notiz');

    Livewire::actingAs($owner)
        ->test(Show::class, ['teamMember' => $member])
        ->call('delete', $oneOnOne->id);

    expect($member->fresh()->oneOnOnes)->toBeEmpty();
});

test('the owner can add, complete and delete an action item on a one-on-one', function () {
    [$team, $owner] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();
    $oneOnOne = OneOnOne::factory()->for($member, 'teamMember')->create();

    $component = Livewire::actingAs($owner)->test(Show::class, ['teamMember' => $member]);

    $component->call('startAddingActionItem', $oneOnOne->id)
        ->set('actionItemForm.description', 'Feedback zu Code-Reviews geben')
        ->call('addActionItem')
        ->assertHasNoErrors();

    $actionItem = $oneOnOne->fresh()->actionItems->first();
    expect($actionItem)->not->toBeNull();
    expect($actionItem->description)->toBe('Feedback zu Code-Reviews geben');

    $component->call('completeActionItem', $actionItem->id);
    expect($actionItem->fresh()->is_done)->toBeTrue();

    $component->call('deleteActionItem', $actionItem->id);
    expect($oneOnOne->fresh()->actionItems)->toBeEmpty();
});

test('action items from a one-on-one do not appear on a retro page', function () {
    [$team, $owner] = createTeamForOwner();
    $member = TeamMember::factory()->for($team)->create();
    $oneOnOne = OneOnOne::factory()->for($member, 'teamMember')->create();
    ActionItem::factory()->for($team)->create([
        'one_on_one_id' => $oneOnOne->id,
        'description' => 'Persönliches 1:1-Thema',
    ]);

    $sprint = Sprint::factory()->for($team)->create();
    $retroType = TeamEventType::factory()->for($team)->retrospective()->create();
    $retro = SprintEvent::factory()->for($sprint)->for($retroType, 'teamEventType')->create();

    Livewire::actingAs($owner)
        ->test(SprintEventShow::class, ['sprintEvent' => $retro])
        ->assertDontSee('Persönliches 1:1-Thema');
});
