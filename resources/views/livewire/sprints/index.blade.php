<div class="flex flex-col gap-8 p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ $team->name }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">Sprints und Scrum-Events dieses Teams.</flux:text>
        </div>

        <div class="flex gap-2">
            @can('update', $team)
                <flux:button variant="ghost" icon="calendar-days" :href="route('teams.event-types', $team)" wire:navigate>
                    Event-Typen
                </flux:button>
                <flux:button variant="ghost" icon="user-group" :href="route('teams.members', $team)" wire:navigate>
                    Mitglieder
                </flux:button>
            @endcan

            @can('create', [\App\Models\Sprint::class, $team])
                @if ($hasEventTypes)
                    <flux:button variant="primary" icon="plus" :href="route('sprints.create', $team)" wire:navigate>
                        Neuer Sprint
                    </flux:button>
                @endif
            @endcan
        </div>
    </div>

    @unless ($hasEventTypes)
        @php
            $eventTypesHint = "Bevor du einen Sprint anlegen kannst, definiere unter 'Event-Typen' den Scrum-Flow für {$team->name} (z. B. Daily, Planning, Review, Retro oder eigene Events).";
        @endphp
        <flux:callout icon="information-circle" heading="Noch keine Event-Typen definiert" :text="$eventTypesHint" />
    @endunless

    @if ($sprints->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="calendar-days" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Noch kein Sprint</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">Leg den ersten Sprint für dieses Team an.</flux:text>
        </flux:card>
    @else
        <div class="flex flex-col gap-3">
            @foreach ($sprints as $sprint)
                <a href="{{ route('sprints.show', $sprint) }}" wire:navigate class="block">
                    <flux:card class="flex flex-wrap items-center justify-between gap-4 transition hover:border-zinc-300 dark:hover:border-zinc-600">
                        <div>
                            <flux:heading size="lg">{{ $sprint->name }}</flux:heading>
                            <flux:text class="text-zinc-500 dark:text-zinc-400">
                                {{ $sprint->starts_at->format('d.m.Y') }} &ndash; {{ $sprint->ends_at->format('d.m.Y') }}
                            </flux:text>
                        </div>

                        <flux:badge
                            size="sm"
                            :color="match ($sprint->status->value) {
                                'active' => 'lime',
                                'completed' => 'zinc',
                                default => 'blue',
                            }"
                        >
                            {{ $sprint->status->label() }}
                        </flux:badge>
                    </flux:card>
                </a>
            @endforeach
        </div>
    @endif
</div>
