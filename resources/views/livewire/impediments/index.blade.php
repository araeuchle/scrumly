<div class="flex flex-col gap-8 p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Impediments &middot; {{ $team->name }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Blocker zentral erfassen, eskalieren und auflösen.
            </flux:text>
        </div>

        @unless ($showForm)
            <flux:button variant="primary" icon="plus" wire:click="startCreating">
                Impediment melden
            </flux:button>
        @endunless
    </div>

    @if ($showForm)
        <flux:card class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $editingId ? 'Impediment bearbeiten' : 'Neues Impediment' }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input wire:model="form.title" label="Titel" placeholder="z. B. Staging-Umgebung nicht erreichbar" autofocus />

                <flux:textarea wire:model="form.description" label="Beschreibung" rows="4" placeholder="Was genau blockiert das Team?" />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:select wire:model="form.priority" label="Priorität">
                        @foreach ($priorityOptions as $option)
                            <option value="{{ $option->value }}">{{ $option->label() }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="form.sprintId" label="Sprint (optional)">
                        <option value="">Kein Sprint</option>
                        @foreach ($sprints as $sprint)
                            <option value="{{ $sprint->id }}">{{ $sprint->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input wire:model="form.reportedBy" label="Gemeldet von" placeholder="Optional" />
                    <flux:input wire:model="form.owner" label="Verantwortlich" placeholder="Optional" />
                </div>

                <flux:textarea wire:model="form.resolutionNotes" label="Notizen zur Lösung" rows="3" placeholder="Optional &mdash; was wurde unternommen?" />

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Speichern' : 'Anlegen' }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancelForm">Abbrechen</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="flex flex-wrap gap-2">
        @foreach ([
            'active' => 'Aktiv',
            'open' => 'Offen',
            'escalated' => 'Eskaliert',
            'resolved' => 'Gelöst',
            'all' => 'Alle',
        ] as $value => $label)
            <flux:button
                size="sm"
                :variant="$statusFilter === $value ? 'primary' : 'ghost'"
                wire:click="$set('statusFilter', '{{ $value }}')"
            >
                {{ $label }}
            </flux:button>
        @endforeach
    </div>

    @if ($impediments->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="exclamation-triangle" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Keine Impediments</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                In dieser Ansicht gibt es aktuell nichts zu tun &mdash; gut so.
            </flux:text>
        </flux:card>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($impediments as $impediment)
                <flux:card class="flex flex-col gap-3" wire:key="impediment-{{ $impediment->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <flux:heading size="lg">{{ $impediment->title }}</flux:heading>
                                <flux:badge
                                    size="sm"
                                    :color="match ($impediment->status->value) {
                                        'escalated' => 'red',
                                        'resolved' => 'zinc',
                                        default => 'blue',
                                    }"
                                >
                                    {{ $impediment->status->label() }}
                                </flux:badge>
                                <flux:badge
                                    size="sm"
                                    :color="match ($impediment->priority->value) {
                                        'critical' => 'red',
                                        'high' => 'orange',
                                        'low' => 'zinc',
                                        default => 'blue',
                                    }"
                                >
                                    {{ $impediment->priority->label() }}
                                </flux:badge>
                            </div>

                            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                @if ($impediment->sprint)
                                    Sprint: {{ $impediment->sprint->name }}
                                @endif
                                @if ($impediment->reported_by)
                                    &middot; Gemeldet von {{ $impediment->reported_by }}
                                @endif
                                @if ($impediment->owner)
                                    &middot; Verantwortlich: {{ $impediment->owner }}
                                @endif
                            </flux:text>
                        </div>

                        <div class="flex items-center gap-1">
                            @if ($impediment->status->value === 'open')
                                <flux:button size="sm" variant="ghost" icon="arrow-trending-up" wire:click="escalate({{ $impediment->id }})">
                                    Eskalieren
                                </flux:button>
                            @endif

                            @if (in_array($impediment->status->value, ['open', 'escalated'], true))
                                <flux:button size="sm" variant="ghost" icon="check" wire:click="resolve({{ $impediment->id }})">
                                    Lösen
                                </flux:button>
                            @else
                                <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="reopen({{ $impediment->id }})">
                                    Wieder öffnen
                                </flux:button>
                            @endif

                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="startEditing({{ $impediment->id }})" />

                            <flux:modal.trigger name="delete-impediment-{{ $impediment->id }}">
                                <flux:button size="sm" variant="ghost" icon="trash" />
                            </flux:modal.trigger>

                            <flux:modal name="delete-impediment-{{ $impediment->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <flux:heading size="lg">Impediment löschen?</flux:heading>
                                        <flux:text class="mt-2">
                                            "{{ $impediment->title }}" wird endgültig gelöscht. Diese Aktion kann nicht rückgängig gemacht werden.
                                        </flux:text>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Abbrechen</flux:button>
                                        </flux:modal.close>
                                        <flux:modal.close>
                                            <flux:button variant="danger" wire:click="delete({{ $impediment->id }})">
                                                Löschen
                                            </flux:button>
                                        </flux:modal.close>
                                    </div>
                                </div>
                            </flux:modal>
                        </div>
                    </div>

                    @if ($impediment->description)
                        <flux:text class="whitespace-pre-line text-zinc-600 dark:text-zinc-300">{{ $impediment->description }}</flux:text>
                    @endif

                    @if ($impediment->resolution_notes)
                        <div class="rounded-lg border border-zinc-100 p-3 dark:border-zinc-700">
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Notizen zur Lösung</flux:text>
                            <flux:text class="whitespace-pre-line">{{ $impediment->resolution_notes }}</flux:text>
                        </div>
                    @endif
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
