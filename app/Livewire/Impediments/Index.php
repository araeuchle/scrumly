<?php

namespace App\Livewire\Impediments;

use App\Enums\ImpedimentPriority;
use App\Enums\ImpedimentStatus;
use App\Livewire\Forms\ImpedimentForm;
use App\Models\Impediment;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public Team $team;

    public bool $showForm = false;

    public ?int $editingId = null;

    public ImpedimentForm $form;

    public string $statusFilter = 'active';

    public function mount(Team $team): void
    {
        $this->authorize('viewAny', [Impediment::class, $team]);

        $this->team = $team;
    }

    public function startCreating(): void
    {
        $this->authorize('create', [Impediment::class, $this->team]);

        $this->form->reset();
        $this->editingId = null;
        $this->showForm = true;
    }

    public function startEditing(int $impedimentId): void
    {
        $impediment = $this->team->impediments()->findOrFail($impedimentId);

        $this->authorize('update', $impediment);

        $this->editingId = $impediment->id;
        $this->form->title = $impediment->title;
        $this->form->description = (string) $impediment->description;
        $this->form->priority = $impediment->priority->value;
        $this->form->sprintId = $impediment->sprint_id;
        $this->form->reportedBy = (string) $impediment->reported_by;
        $this->form->owner = (string) $impediment->owner;
        $this->form->resolutionNotes = (string) $impediment->resolution_notes;
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
        $impediment = $this->editingId !== null
            ? $this->team->impediments()->findOrFail($this->editingId)
            : null;

        if ($impediment !== null) {
            $this->authorize('update', $impediment);
        } else {
            $this->authorize('create', [Impediment::class, $this->team]);
        }

        $validated = $this->form->validate();

        if ($validated['sprintId'] !== null && ! $this->team->sprints()->whereKey($validated['sprintId'])->exists()) {
            $this->addError('form.sprintId', 'Der gewählte Sprint gehört nicht zu diesem Team.');

            return;
        }

        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?: null,
            'priority' => $validated['priority'],
            'sprint_id' => $validated['sprintId'],
            'reported_by' => $validated['reportedBy'] ?: null,
            'owner' => $validated['owner'] ?: null,
            'resolution_notes' => $validated['resolutionNotes'] ?: null,
        ];

        if ($impediment !== null) {
            $impediment->update($data);
        } else {
            $this->team->impediments()->create($data);
        }

        $this->form->reset();
        $this->editingId = null;
        $this->showForm = false;
    }

    public function delete(int $impedimentId): void
    {
        $impediment = $this->team->impediments()->findOrFail($impedimentId);

        $this->authorize('delete', $impediment);

        $impediment->delete();
    }

    public function escalate(int $impedimentId): void
    {
        $impediment = $this->team->impediments()->findOrFail($impedimentId);

        $this->authorize('update', $impediment);

        $impediment->escalate();
    }

    public function resolve(int $impedimentId): void
    {
        $impediment = $this->team->impediments()->findOrFail($impedimentId);

        $this->authorize('update', $impediment);

        $impediment->resolve();
    }

    public function reopen(int $impedimentId): void
    {
        $impediment = $this->team->impediments()->findOrFail($impedimentId);

        $this->authorize('update', $impediment);

        $impediment->reopen();
    }

    public function render(): View
    {
        $query = $this->team->impediments()->with('sprint')->latest();

        $impediments = match ($this->statusFilter) {
            'open' => $query->where('status', ImpedimentStatus::Open->value)->get(),
            'escalated' => $query->where('status', ImpedimentStatus::Escalated->value)->get(),
            'resolved' => $query->where('status', ImpedimentStatus::Resolved->value)->get(),
            'active' => $query->whereIn('status', [ImpedimentStatus::Open->value, ImpedimentStatus::Escalated->value])->get(),
            default => $query->get(),
        };

        return view('livewire.impediments.index', [
            'impediments' => $impediments,
            'sprints' => $this->team->sprints()->orderByDesc('starts_at')->get(),
            'priorityOptions' => ImpedimentPriority::cases(),
        ]);
    }
}
