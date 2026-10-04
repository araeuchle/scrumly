<div class="flex flex-col gap-8 p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Scrum-Master-Tagebuch</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Tägliche Selbstreflexion &mdash; durchsuchbar und nach Zeitraum oder Emotion filterbar.
            </flux:text>
        </div>

        @unless ($showForm)
            <flux:button variant="primary" icon="plus" wire:click="startCreating">
                Neuer Eintrag
            </flux:button>
        @endunless
    </div>

    @if ($showForm)
        <flux:card class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $editingId ? 'Eintrag bearbeiten' : 'Neuer Eintrag' }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <flux:input type="date" wire:model="form.entryDate" label="Datum" />

                <flux:textarea wire:model="form.wentWell" label="Was lief heute gut?" rows="3" />
                <flux:textarea wire:model="form.onMyMind" label="Was beschäftigt mich?" rows="3" />
                <flux:textarea wire:model="form.shouldIntervene" label="Wo sollte ich eingreifen?" rows="3" />

                <div>
                    <flux:text class="mb-2 text-sm font-medium text-zinc-600 dark:text-zinc-300">Emotionen</flux:text>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($emotionOptions as $option)
                            <flux:badge
                                as="button"
                                type="button"
                                size="sm"
                                :color="$option->color()"
                                :variant="in_array($option->value, $form->emotions, true) ? 'solid' : 'outline'"
                                wire:click="toggleFormEmotion('{{ $option->value }}')"
                            >
                                {{ $option->label() }}
                            </flux:badge>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Speichern' : 'Anlegen' }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancelForm">Abbrechen</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <flux:card class="flex flex-col gap-4">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <flux:input wire:model.live.debounce.750ms="search" label="Suche" placeholder="Stichwort in den Antworten" icon="magnifying-glass" />
            <flux:input type="date" wire:model.live="dateFrom" label="Von" />
            <flux:input type="date" wire:model.live="dateTo" label="Bis" />
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @foreach ($emotionOptions as $option)
                <flux:badge
                    as="button"
                    type="button"
                    size="sm"
                    :color="$option->color()"
                    :variant="in_array($option->value, $emotionFilter, true) ? 'solid' : 'outline'"
                    wire:click="toggleEmotionFilter('{{ $option->value }}')"
                >
                    {{ $option->label() }}
                </flux:badge>
            @endforeach

            @if ($search !== '' || $dateFrom !== '' || $dateTo !== '' || ! empty($emotionFilter))
                <flux:button size="sm" variant="ghost" wire:click="clearFilters">
                    Filter zurücksetzen
                </flux:button>
            @endif
        </div>
    </flux:card>

    @if ($entries->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="book-open" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Keine Eintr&auml;ge</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Für die aktuelle Filterung gibt es noch keine Tagebucheinträge.
            </flux:text>
        </flux:card>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($entries as $entry)
                <flux:card class="flex flex-col gap-3" wire:key="journal-entry-{{ $entry->id }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="lg">{{ $entry->entry_date->translatedFormat('d. F Y') }}</flux:heading>
                            @foreach ($entry->emotions ?? [] as $emotionValue)
                                @php $emotion = \App\Enums\JournalEmotion::from($emotionValue) @endphp
                                <flux:badge size="sm" :color="$emotion->color()">{{ $emotion->label() }}</flux:badge>
                            @endforeach
                        </div>

                        <div class="flex items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="startEditing('{{ $entry->id }}')" />

                            <flux:modal.trigger name="delete-journal-entry-{{ $entry->id }}">
                                <flux:button size="sm" variant="ghost" icon="trash" />
                            </flux:modal.trigger>

                            <flux:modal name="delete-journal-entry-{{ $entry->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <flux:heading size="lg">Eintrag löschen?</flux:heading>
                                        <flux:text class="mt-2">
                                            Der Eintrag vom {{ $entry->entry_date->translatedFormat('d. F Y') }} wird endgültig gelöscht.
                                            Diese Aktion kann nicht rückgängig gemacht werden.
                                        </flux:text>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Abbrechen</flux:button>
                                        </flux:modal.close>
                                        <flux:modal.close>
                                            <flux:button variant="danger" wire:click="delete('{{ $entry->id }}')">
                                                Löschen
                                            </flux:button>
                                        </flux:modal.close>
                                    </div>
                                </div>
                            </flux:modal>
                        </div>
                    </div>

                    @if ($entry->went_well)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Was lief heute gut?</flux:text>
                            <flux:text class="whitespace-pre-line">{{ $entry->went_well }}</flux:text>
                        </div>
                    @endif

                    @if ($entry->on_my_mind)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Was beschäftigt mich?</flux:text>
                            <flux:text class="whitespace-pre-line">{{ $entry->on_my_mind }}</flux:text>
                        </div>
                    @endif

                    @if ($entry->should_intervene)
                        <div>
                            <flux:text class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Wo sollte ich eingreifen?</flux:text>
                            <flux:text class="whitespace-pre-line">{{ $entry->should_intervene }}</flux:text>
                        </div>
                    @endif
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
