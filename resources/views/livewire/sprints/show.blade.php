<div class="flex flex-col gap-8 p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                <flux:link :href="route('sprints.index', $sprint->team)" wire:navigate>{{ $sprint->team->name }}</flux:link>
            </flux:text>
            <flux:heading size="xl">{{ $sprint->name }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ $sprint->starts_at->format('d.m.Y') }} &ndash; {{ $sprint->ends_at->format('d.m.Y') }}
            </flux:text>
        </div>

        <flux:badge
            size="lg"
            :color="match ($sprint->status->value) {
                'active' => 'lime',
                'completed' => 'zinc',
                default => 'blue',
            }"
        >
            {{ $sprint->status->label() }}
        </flux:badge>
    </div>

    @if ($sprint->goal)
        <flux:callout icon="flag" :text="$sprint->goal" heading="Sprint-Ziel" />
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-4 lg:col-span-2">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Events</flux:heading>

                @can('update', $sprint)
                    @if ($availableEventTypes->isNotEmpty())
                        <flux:modal.trigger name="add-event">
                            <flux:button size="sm" variant="primary" icon="plus">Event hinzufügen</flux:button>
                        </flux:modal.trigger>

                        <flux:modal name="add-event" class="min-w-[26rem]">
                            <div class="flex flex-col gap-6">
                                <div>
                                    <flux:heading size="lg">Event hinzufügen</flux:heading>
                                    <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                                        Wähle einen der für {{ $sprint->team->name }} definierten Event-Typen.
                                    </flux:text>
                                </div>

                                <div class="flex flex-col gap-2">
                                    @foreach ($availableEventTypes as $eventType)
                                        <flux:modal.close>
                                            <button
                                                type="button"
                                                wire:click="addEventOccurrence('{{ $eventType->id }}')"
                                                class="flex w-full items-center gap-3 rounded-lg border border-zinc-200 p-3 text-left transition hover:border-zinc-300 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:border-zinc-600 dark:hover:bg-zinc-800"
                                            >
                                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-zinc-900/5 dark:bg-white/10">
                                                    <flux:icon :icon="$eventType->icon" variant="outline" class="size-5" />
                                                </span>
                                                <span class="flex flex-col">
                                                    <flux:text class="font-medium text-zinc-900 dark:text-white">{{ $eventType->name }}</flux:text>
                                                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                                        {{ $eventType->default_duration_minutes }} Min.
                                                        @if ($eventType->is_recurring)
                                                            &middot; Wiederkehrend
                                                        @endif
                                                    </flux:text>
                                                </span>
                                            </button>
                                        </flux:modal.close>
                                    @endforeach
                                </div>

                                <div class="flex justify-end">
                                    <flux:modal.close>
                                        <flux:button variant="ghost">Abbrechen</flux:button>
                                    </flux:modal.close>
                                </div>
                            </div>
                        </flux:modal>
                    @endif
                @endcan
            </div>

            <div class="flex flex-col gap-3">
                @forelse ($sprint->events as $event)
                    <flux:card class="flex items-center justify-between gap-4" wire:key="event-{{ $event->id }}">
                        <a href="{{ route('sprint-events.show', $event) }}" wire:navigate class="flex flex-1 items-center gap-3 transition hover:opacity-75">
                            <flux:icon :icon="$event->teamEventType->icon" variant="outline" class="size-6 text-zinc-400" />
                            <div>
                                <flux:heading size="base">{{ $event->teamEventType->name }}</flux:heading>
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ $event->scheduled_date->format('d.m.Y') }} &middot; {{ $event->duration_minutes }} Min.
                                </flux:text>
                            </div>
                        </a>

                        <flux:badge
                            size="sm"
                            :color="match ($event->status->value) {
                                'in_progress' => 'lime',
                                'completed' => 'zinc',
                                default => 'blue',
                            }"
                        >
                            {{ $event->status->label() }}
                        </flux:badge>

                        @can('update', $sprint)
                            <flux:modal.trigger name="delete-event-{{ $event->id }}">
                                <flux:button size="sm" variant="ghost" icon="trash" />
                            </flux:modal.trigger>

                            <flux:modal name="delete-event-{{ $event->id }}" class="min-w-[22rem]">
                                <div class="flex flex-col gap-6">
                                    <div>
                                        <flux:heading size="lg">Event entfernen?</flux:heading>
                                        <flux:text class="mt-2">
                                            "{{ $event->teamEventType->name }}" vom {{ $event->scheduled_date->format('d.m.Y') }} wird aus diesem Sprint entfernt. Diese Aktion kann nicht rückgängig gemacht werden.
                                        </flux:text>
                                    </div>

                                    <div class="flex justify-end gap-2">
                                        <flux:modal.close>
                                            <flux:button variant="ghost">Abbrechen</flux:button>
                                        </flux:modal.close>
                                        <flux:modal.close>
                                            <flux:button variant="danger" wire:click="deleteEvent('{{ $event->id }}')">
                                                Entfernen
                                            </flux:button>
                                        </flux:modal.close>
                                    </div>
                                </div>
                            </flux:modal>
                        @endcan
                    </flux:card>
                @empty
                    <flux:card class="p-8 text-center">
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Noch keine Events geplant.</flux:text>
                    </flux:card>
                @endforelse
            </div>
        </div>

        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">Kapazität</flux:heading>
                <flux:badge size="sm" color="zinc">&oslash; {{ $averageCapacity }}%</flux:badge>
            </div>

            <flux:card class="flex flex-col gap-4">
                @forelse ($sprint->capacities as $capacity)
                    <div class="flex flex-col gap-2 border-b border-zinc-100 pb-4 last:border-0 last:pb-0 dark:border-zinc-700" wire:key="capacity-{{ $capacity->id }}">
                        <flux:text class="font-medium">{{ $capacity->teamMember->fullName() }}</flux:text>

                        @can('update', $sprint)
                            <div class="flex items-center gap-2">
                                <flux:input
                                    type="number"
                                    min="0"
                                    max="100"
                                    size="sm"
                                    value="{{ $capacity->capacity_percent }}"
                                    wire:change="updateCapacity('{{ $capacity->id }}', $event.target.value)"
                                    class="w-20"
                                />
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">%</flux:text>
                            </div>
                            <flux:input
                                size="sm"
                                placeholder="Notiz, z. B. 3 Tage Urlaub"
                                value="{{ $capacity->note }}"
                                wire:change="updateCapacityNote('{{ $capacity->id }}', $event.target.value)"
                            />
                        @else
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $capacity->capacity_percent }}%
                                @if ($capacity->note)
                                    &middot; {{ $capacity->note }}
                                @endif
                            </flux:text>
                        @endcan
                    </div>
                @empty
                    <flux:text class="text-zinc-500 dark:text-zinc-400">Keine Teammitglieder.</flux:text>
                @endforelse
            </flux:card>
        </div>
    </div>
</div>
