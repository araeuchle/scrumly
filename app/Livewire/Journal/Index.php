<?php

namespace App\Livewire\Journal;

use App\Enums\JournalEmotion;
use App\Livewire\Forms\JournalEntryForm;
use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public bool $showForm = false;

    public ?string $editingId = null;

    public JournalEntryForm $form;

    public string $search = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    /** @var array<int, string> */
    public array $emotionFilter = [];

    public function startCreating(): void
    {
        $this->authorize('create', JournalEntry::class);

        $this->form->reset();
        $this->form->entryDate = now()->toDateString();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function startEditing(string $journalEntryId): void
    {
        $entry = JournalEntry::findOrFail($journalEntryId);

        $this->authorize('update', $entry);

        $this->editingId = $entry->id;
        $this->form->entryDate = $entry->entry_date->toDateString();
        $this->form->wentWell = (string) $entry->went_well;
        $this->form->onMyMind = (string) $entry->on_my_mind;
        $this->form->shouldIntervene = (string) $entry->should_intervene;
        $this->form->emotions = $entry->emotions ?? [];
        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->form->reset();
        $this->editingId = null;
        $this->showForm = false;
    }

    public function toggleFormEmotion(string $emotion): void
    {
        if (in_array($emotion, $this->form->emotions, true)) {
            $this->form->emotions = array_values(array_diff($this->form->emotions, [$emotion]));
        } else {
            $this->form->emotions[] = $emotion;
        }
    }

    public function save(): void
    {
        $entry = $this->editingId !== null
            ? JournalEntry::findOrFail($this->editingId)
            : null;

        if ($entry !== null) {
            $this->authorize('update', $entry);
        } else {
            $this->authorize('create', JournalEntry::class);
        }

        $validated = $this->form->validate();

        $entryId = $entry?->id;

        $duplicate = $this->currentUser()->journalEntries()
            ->where('entry_date', $validated['entryDate'])
            ->when($entryId !== null, fn ($query) => $query->whereKeyNot($entryId))
            ->exists();

        if ($duplicate) {
            $this->addError('form.entryDate', 'Für diesen Tag existiert bereits ein Eintrag.');

            return;
        }

        $data = [
            'entry_date' => $validated['entryDate'],
            'went_well' => $validated['wentWell'] ?: null,
            'on_my_mind' => $validated['onMyMind'] ?: null,
            'should_intervene' => $validated['shouldIntervene'] ?: null,
            'emotions' => $validated['emotions'],
        ];

        if ($entry !== null) {
            $entry->update($data);
        } else {
            $this->currentUser()->journalEntries()->create($data);
        }

        $this->form->reset();
        $this->editingId = null;
        $this->showForm = false;
    }

    public function delete(string $journalEntryId): void
    {
        $entry = JournalEntry::findOrFail($journalEntryId);

        $this->authorize('delete', $entry);

        $entry->delete();
    }

    public function toggleEmotionFilter(string $emotion): void
    {
        if (in_array($emotion, $this->emotionFilter, true)) {
            $this->emotionFilter = array_values(array_diff($this->emotionFilter, [$emotion]));
        } else {
            $this->emotionFilter[] = $emotion;
        }
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->emotionFilter = [];
    }

    public function render(): View
    {
        $query = $this->currentUser()->journalEntries()->latest('entry_date');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($query) use ($term) {
                $query->where('went_well', 'like', $term)
                    ->orWhere('on_my_mind', 'like', $term)
                    ->orWhere('should_intervene', 'like', $term);
            });
        }

        if ($this->dateFrom !== '') {
            $query->whereDate('entry_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo !== '') {
            $query->whereDate('entry_date', '<=', $this->dateTo);
        }

        foreach ($this->emotionFilter as $emotion) {
            $query->whereJsonContains('emotions', $emotion);
        }

        return view('livewire.journal.index', [
            'entries' => $query->get(),
            'emotionOptions' => JournalEmotion::cases(),
        ]);
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
