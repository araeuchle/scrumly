<div class="flex flex-col gap-8 p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Event-Typen &middot; {{ $team->name }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Definiere den Scrum-Event-Flow für dieses Team &mdash; jedes Team kann eigene Events, Agenden und Zeiten haben.
            </flux:text>
        </div>

        @unless ($showForm)
            <flux:button variant="primary" icon="plus" wire:click="startCreating">
                Event-Typ hinzufügen
            </flux:button>
        @endunless
    </div>

    @if ($showForm)
        <flux:card class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $editingId ? 'Event-Typ bearbeiten' : 'Neuer Event-Typ' }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input wire:model="name" label="Name" placeholder="z. B. Daily, Backlog Refinement" autofocus />

                    <flux:select wire:model="icon" label="Icon">
                        @foreach ($iconOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:textarea wire:model="defaultAgenda" label="Standard-Agenda" rows="5" placeholder="Eine Zeile pro Punkt" />

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:input wire:model="defaultDurationMinutes" type="number" min="1" max="600" label="Standarddauer (Minuten)" />

                    @if (! $isRecurring)
                        <flux:select wire:model="timing" label="Zeitpunkt im Sprint">
                            <option value="start">Am Sprint-Start</option>
                            <option value="end">Am Sprint-Ende</option>
                        </flux:select>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    <flux:checkbox wire:model="isRecurring" label="Wiederkehrend (z. B. mehrmals pro Sprint, wie ein Daily)" />
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Nicht wiederkehrende Events werden beim Anlegen eines Sprints automatisch einmal erzeugt.
                        Wiederkehrende Events fügst du im Sprint bei Bedarf selbst hinzu.
                    </flux:text>
                </div>

                <flux:checkbox wire:model="trackSpeakingTime" label="Redezeit pro Teammitglied erfassen" />

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Speichern' : 'Anlegen' }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancelForm">Abbrechen</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    @if ($eventTypes->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="calendar-days" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Noch keine Event-Typen</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Leg fest, welche Events dieses Team durchführt &mdash; z. B. Daily, Planning, Review, Retro oder eigene.
            </flux:text>
        </flux:card>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($eventTypes as $index => $eventType)
                <flux:card class="flex items-center justify-between gap-4" wire:key="event-type-{{ $eventType->id }}">
                    <div class="flex items-center gap-3">
                        <span class="flex size-10 items-center justify-center rounded-lg bg-zinc-900/5 dark:bg-white/10">
                            <flux:icon :icon="$eventType->icon" variant="outline" class="size-5" />
                        </span>
                        <div>
                            <flux:heading size="base">{{ $eventType->name }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $eventType->default_duration_minutes }} Min.
                                &middot;
                                {{ $eventType->is_recurring ? 'Wiederkehrend' : $eventType->timingLabel() }}
                                @if ($eventType->track_speaking_time)
                                    &middot; Redezeit-Tracking
                                @endif
                            </flux:text>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        <flux:button size="sm" variant="ghost" icon="chevron-up" wire:click="moveUp({{ $eventType->id }})" :disabled="$index === 0" />
                        <flux:button size="sm" variant="ghost" icon="chevron-down" wire:click="moveDown({{ $eventType->id }})" :disabled="$index === $eventTypes->count() - 1" />
                        <flux:button size="sm" variant="ghost" icon="pencil" wire:click="startEditing({{ $eventType->id }})" />

                        <flux:modal.trigger name="delete-event-type-{{ $eventType->id }}">
                            <flux:button size="sm" variant="ghost" icon="trash" />
                        </flux:modal.trigger>

                        <flux:modal name="delete-event-type-{{ $eventType->id }}" class="min-w-[22rem]">
                            <div class="flex flex-col gap-6">
                                <div>
                                    <flux:heading size="lg">Event-Typ löschen?</flux:heading>
                                    <flux:text class="mt-2">
                                        "{{ $eventType->name }}" wird gelöscht, inklusive aller bisherigen Vorkommen in Sprints. Diese Aktion kann nicht rückgängig gemacht werden.
                                    </flux:text>
                                </div>

                                <div class="flex justify-end gap-2">
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Abbrechen</flux:button>
                                    </flux:modal.close>
                                    <flux:modal.close>
                                        <flux:button variant="danger" wire:click="delete({{ $eventType->id }})">
                                            Löschen
                                        </flux:button>
                                    </flux:modal.close>
                                </div>
                            </div>
                        </flux:modal>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
