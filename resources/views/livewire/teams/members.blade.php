<div class="flex flex-col gap-8 p-6">
    <div>
        <flux:heading size="xl">Teammitglieder &middot; {{ $team->name }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
            Pflege die Mitglieder dieses Teams für deine Sprint- und Kapazitätsplanung.
        </flux:text>
    </div>

    <flux:card class="flex flex-col gap-4">
        <flux:heading size="lg">Mitglied hinzufügen</flux:heading>

        <form wire:submit="addMember" class="flex flex-col gap-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <flux:input wire:model="form.firstName" label="Vorname" placeholder="Max" />
            </div>
            <div class="flex-1">
                <flux:input wire:model="form.lastName" label="Nachname" placeholder="Mustermann" />
            </div>
            <div class="w-full sm:w-40">
                <flux:input wire:model="form.defaultCapacityPercent" label="Standardkapazität (%)" type="number" min="0" max="100" />
            </div>
            <flux:button type="submit" variant="primary">Hinzufügen</flux:button>
        </form>
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <flux:heading size="lg">Mitglieder</flux:heading>

        @if ($teamMembers->isEmpty())
            <flux:text class="text-zinc-500 dark:text-zinc-400">Noch keine Teammitglieder angelegt.</flux:text>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Standardkapazität</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($teamMembers as $member)
                        <flux:table.row wire:key="member-{{ $member->id }}">
                            <flux:table.cell>{{ $member->fullName() }}</flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:input
                                        type="number"
                                        min="0"
                                        max="100"
                                        size="sm"
                                        value="{{ $member->default_capacity_percent }}"
                                        wire:change="updateCapacity('{{ $member->id }}', $event.target.value)"
                                        class="w-20"
                                    />
                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">%</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:modal.trigger name="remove-member-{{ $member->id }}">
                                    <flux:button size="sm" variant="ghost" icon="trash" />
                                </flux:modal.trigger>

                                <flux:modal name="remove-member-{{ $member->id }}" class="min-w-[22rem]">
                                    <div class="flex flex-col gap-6">
                                        <div>
                                            <flux:heading size="lg">Mitglied entfernen?</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ $member->fullName() }} wird aus {{ $team->name }} entfernt. Diese Aktion kann nicht rückgängig gemacht werden.
                                            </flux:text>
                                        </div>

                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="ghost">Abbrechen</flux:button>
                                            </flux:modal.close>
                                            <flux:modal.close>
                                                <flux:button variant="danger" wire:click="removeMember('{{ $member->id }}')">
                                                    Entfernen
                                                </flux:button>
                                            </flux:modal.close>
                                        </div>
                                    </div>
                                </flux:modal>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </flux:card>
</div>
