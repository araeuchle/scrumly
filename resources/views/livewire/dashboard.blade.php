<div class="flex flex-col gap-8 p-6">
    <div>
        <flux:heading size="xl">Willkommen zurück, {{ auth()->user()->name }} 👋</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
            Offene Action Items und 1:1-Erinnerungen für deine Teams.
        </flux:text>
    </div>

    @unless ($hasJournalEntryToday)
        <flux:callout color="blue" icon="book-open" heading="Noch kein Tagebucheintrag für heute">
            <flux:callout.text>Nimm dir kurz Zeit für die drei Reflexionsfragen des Tages.</flux:callout.text>

            <x-slot:actions>
                <flux:button variant="primary" :href="route('journal.index')" wire:navigate>
                    Jetzt eintragen
                </flux:button>
            </x-slot:actions>
        </flux:callout>
    @endunless

    @if ($teams->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="user-group" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Noch kein Team</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Leg dein erstes Team an, um loszulegen.
            </flux:text>
            <flux:button variant="primary" :href="route('teams.index')" wire:navigate>Zu den Teams</flux:button>
        </flux:card>
    @else
        <div class="flex flex-col gap-6">
            @foreach ($teams as $team)
                @php
                    $reminders = $team->teamMembers->filter(fn ($member) => $member->needsOneOnOneReminder());
                @endphp

                <flux:card class="flex flex-col gap-6">
                    <flux:heading size="lg">
                        <flux:link :href="route('sprints.index', $team)" wire:navigate>{{ $team->name }}</flux:link>
                    </flux:heading>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                        <div class="flex flex-col gap-3">
                            <flux:heading size="sm">Offene Action Items aus Retros</flux:heading>

                            @if ($team->actionItems->isEmpty())
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Keine offenen Action Items.</flux:text>
                            @else
                                <div class="flex flex-col gap-2">
                                    @foreach ($team->actionItems as $actionItem)
                                        <div class="rounded-lg border border-zinc-100 p-3 text-sm dark:border-zinc-700">
                                            @if ($actionItem->sprint_event_id)
                                                <flux:link :href="route('sprint-events.show', $actionItem->sprint_event_id)" wire:navigate>{{ $actionItem->description }}</flux:link>
                                            @else
                                                {{ $actionItem->description }}
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-3">
                            <flux:heading size="sm">1:1-Erinnerungen</flux:heading>

                            @if ($reminders->isEmpty())
                                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Alle 1:1s sind aktuell.</flux:text>
                            @else
                                <div class="flex flex-col gap-2">
                                    @foreach ($reminders as $member)
                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-100 p-3 text-sm dark:border-zinc-700">
                                            <flux:link :href="route('team-members.show', $member)" wire:navigate>{{ $member->fullName() }}</flux:link>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $member->lastOneOnOneAt() ? 'zuletzt am '.$member->lastOneOnOneAt()->format('d.m.Y') : 'noch nie' }}
                                            </flux:text>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </flux:card>
            @endforeach
        </div>
    @endif
</div>
