<?php

namespace App\Livewire\TeamMembers;

use App\Livewire\Forms\ActionItemForm;
use App\Livewire\Forms\OneOnOneForm;
use App\Models\ActionItem;
use App\Models\OneOnOne;
use App\Models\TeamMember;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Show extends Component
{
    public TeamMember $teamMember;

    public bool $showForm = false;

    public ?string $editingId = null;

    public OneOnOneForm $form;

    public ActionItemForm $actionItemForm;

    public ?string $addingActionItemFor = null;

    public function mount(TeamMember $teamMember): void
    {
        $this->authorize('view', $teamMember->team);

        $this->teamMember = $teamMember;
    }

    public function startCreating(): void
    {
        $this->authorize('update', $this->teamMember->team);

        $this->form->reset();
        $this->form->heldAt = now()->toDateString();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function startEditing(string $oneOnOneId): void
    {
        $this->authorize('update', $this->teamMember->team);

        $oneOnOne = $this->teamMember->oneOnOnes()->findOrFail($oneOnOneId);

        $this->editingId = $oneOnOne->id;
        $this->form->heldAt = $oneOnOne->held_at->toDateString();
        $this->form->notes = (string) $oneOnOne->notes;
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->form->reset();
        $this->editingId = null;
        $this->showForm = false;
    }

    public function save(): void
    {
        $this->authorize('update', $this->teamMember->team);

        $validated = $this->form->validate();

        $data = [
            'held_at' => $validated['heldAt'],
            'notes' => $validated['notes'] ?: null,
        ];

        if ($this->editingId !== null) {
            $this->teamMember->oneOnOnes()->whereKey($this->editingId)->update($data);
        } else {
            $this->teamMember->oneOnOnes()->create($data);
        }

        $this->form->reset();
        $this->editingId = null;
        $this->showForm = false;
    }

    public function delete(string $oneOnOneId): void
    {
        $this->authorize('update', $this->teamMember->team);

        $this->teamMember->oneOnOnes()->whereKey($oneOnOneId)->delete();
    }

    public function startAddingActionItem(string $oneOnOneId): void
    {
        $this->authorize('update', $this->teamMember->team);

        $this->actionItemForm->reset();
        $this->addingActionItemFor = $oneOnOneId;
    }

    public function cancelAddingActionItem(): void
    {
        $this->actionItemForm->reset();
        $this->addingActionItemFor = null;
    }

    public function addActionItem(): void
    {
        $this->authorize('create', [ActionItem::class, $this->teamMember->team]);

        $oneOnOne = $this->teamMember->oneOnOnes()->findOrFail($this->addingActionItemFor);

        $validated = $this->actionItemForm->validate();

        $this->teamMember->team->actionItems()->create([
            'one_on_one_id' => $oneOnOne->id,
            'description' => $validated['description'],
        ]);

        $this->actionItemForm->reset();
        $this->addingActionItemFor = null;
    }

    public function completeActionItem(string $actionItemId): void
    {
        $actionItem = $this->actionItemsQuery()->findOrFail($actionItemId);

        $this->authorize('update', $actionItem);

        $actionItem->complete();
    }

    public function reopenActionItem(string $actionItemId): void
    {
        $actionItem = $this->actionItemsQuery()->findOrFail($actionItemId);

        $this->authorize('update', $actionItem);

        $actionItem->reopen();
    }

    public function deleteActionItem(string $actionItemId): void
    {
        $actionItem = $this->actionItemsQuery()->findOrFail($actionItemId);

        $this->authorize('delete', $actionItem);

        $actionItem->delete();
    }

    /**
     * @return HasManyThrough<ActionItem, OneOnOne, TeamMember>
     */
    private function actionItemsQuery(): HasManyThrough
    {
        return $this->teamMember->actionItems();
    }

    public function render(): View
    {
        $oneOnOnes = $this->teamMember->oneOnOnes()
            ->with('actionItems')
            ->latest('held_at')
            ->get();

        return view('livewire.team-members.show', [
            'oneOnOnes' => $oneOnOnes,
        ]);
    }
}
