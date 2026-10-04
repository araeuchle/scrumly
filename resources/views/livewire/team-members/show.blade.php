<div class="flex flex-col gap-8 p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                <flux:link :href="route('teams.members', $teamMember->team)" wire:navigate>{{ $teamMember->team->name }}</flux:link>
            </flux:text>
            <flux:heading size="xl">{{ $teamMember->fullName() }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Standardkapazität {{ $teamMember->default_capacity_percent }}%
            </flux:text>
        </div>

        @unless ($showForm)
            <flux:button variant="primary" icon="plus" wire:click="startCreating">
                1:1 festhalten
            </flux:button>
        @endunless
    </div>

    @if ($showForm)
        <flux:card class="flex flex-col gap-4">
            <flux:heading size="lg">{{ $editingId ? '1:1 bearbeiten' : 'Neues 1:1' }}</flux:heading>

            <form wire:submit="save" class="flex flex-col gap-4">
                <div class="w-full sm:w-60">
                    <flux:input type="date" wire:model="form.heldAt" label="Datum" />
                </div>

                <flux:textarea wire:model="form.notes" label="Notizen" rows="5" placeholder="Worüber habt ihr gesprochen, was wurde besprochen?" />

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">{{ $editingId ? 'Speichern' : 'Anlegen' }}</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="cancelForm">Abbrechen</flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <div class="flex flex-col gap-4">
        <flux:heading size="lg">Zeitstrahl</flux:heading>

        @if ($oneOnOnes->isEmpty())
            <flux:text class="text-zinc-500 dark:text-zinc-400">Noch keine 1:1-Gespräche festgehalten.</flux:text>
        @else
            @foreach ($oneOnOnes as $oneOnOne)
                <flux:card class="flex flex-col gap-3" wire:key="one-on-one-{{ $oneOnOne->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <flux:heading size="sm">{{ $oneOnOne->held_at->format('d.m.Y') }}</flux:heading>

                        <div class="flex shrink-0 items-center gap-1">
                            <flux:button size="sm" variant="ghost" icon="pencil" wire:click="startEditing('{{ $oneOnOne->id }}')" />

                            <flux:modal.trigger name="delete-one-on-one-{{ $oneOnOne->id }}">
                                <flux:button size="sm" variant="ghost" icon="trash" />
                            </flux:modal.trigger>

                            <flux:modal name="delete-one-on-one-{{ $oneOnOne->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <flux:heading size="lg">1:1 löschen?</flux:heading>
                                        <flux:text class="mt-2">
                                            Der Eintrag vom {{ $oneOnOne->held_at->format('d.m.Y') }} wird endgültig gelöscht. Diese Aktion kann nicht rückgängig gemacht werden.
                                        </flux:text>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Abbrechen</flux:button>
                                        </flux:modal.close>
                                        <flux:modal.close>
                                            <flux:button variant="danger" wire:click="delete('{{ $oneOnOne->id }}')">
                                                Löschen
                                            </flux:button>
                                        </flux:modal.close>
                                    </div>
                                </div>
                            </flux:modal>
                        </div>
                    </div>

                    @if ($oneOnOne->notes)
                        <flux:text class="whitespace-pre-line">{{ $oneOnOne->notes }}</flux:text>
                    @endif

                    <div class="flex flex-col gap-2">
                        @foreach ($oneOnOne->actionItems as $actionItem)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-100 p-3 dark:border-zinc-700" wire:key="action-item-{{ $actionItem->id }}">
                                <flux:text class="{{ $actionItem->is_done ? 'text-zinc-400 line-through dark:text-zinc-500' : '' }}">
                                    {{ $actionItem->description }}
                                </flux:text>

                                <div class="flex shrink-0 items-center gap-1">
                                    @if ($actionItem->is_done)
                                        <flux:button size="sm" variant="ghost" icon="arrow-path" wire:click="reopenActionItem('{{ $actionItem->id }}')">
                                            Wieder öffnen
                                        </flux:button>
                                    @else
                                        <flux:button size="sm" variant="ghost" icon="check" wire:click="completeActionItem('{{ $actionItem->id }}')">
                                            Erledigt
                                        </flux:button>
                                    @endif
                                    <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteActionItem('{{ $actionItem->id }}')" />
                                </div>
                            </div>
                        @endforeach

                        @if ($addingActionItemFor === $oneOnOne->id)
                            <form wire:submit="addActionItem" class="flex items-end gap-2">
                                <div class="flex-1">
                                    <flux:input wire:model="actionItemForm.description" placeholder="Neues Action Item" autofocus />
                                </div>
                                <flux:button type="submit" variant="primary" size="sm">Hinzufügen</flux:button>
                                <flux:button type="button" variant="ghost" size="sm" wire:click="cancelAddingActionItem">Abbrechen</flux:button>
                            </form>
                        @else
                            <flux:button size="sm" variant="ghost" icon="plus" wire:click="startAddingActionItem('{{ $oneOnOne->id }}')" class="w-fit">
                                Action Item hinzufügen
                            </flux:button>
                        @endif
                    </div>
                </flux:card>
            @endforeach
        @endif
    </div>
</div>
