<div class="flex flex-col gap-8 p-6">
    <div>
        <flux:heading size="xl">Team-Metriken &middot; {{ $team->name }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
            Velocity-Trend, Event-Overruns und ein Team-Health-Signal auf einen Blick.
        </flux:text>
    </div>

    <flux:card class="flex flex-col gap-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg">Team Health</flux:heading>
                <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Proxy aus Impediment-Last, Event-Overruns und Redezeit-Balance &mdash; ersetzt keine echten Team-Gespräche.
                </flux:text>
            </div>
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $teamHealth['score'] }}%</flux:heading>
                <flux:badge
                    size="lg"
                    :color="match ($teamHealth['label']) {
                        'Gut' => 'lime',
                        'Mittel' => 'orange',
                        default => 'red',
                    }"
                >
                    {{ $teamHealth['label'] }}
                </flux:badge>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            @foreach ($teamHealth['signals'] as $signal)
                <div>
                    <div class="flex justify-between text-sm text-zinc-500 dark:text-zinc-400">
                        <span>{{ $signal['label'] }}</span>
                        <span>{{ $signal['score'] !== null ? round($signal['score']).'%' : 'Keine Daten' }}</span>
                    </div>
                    <div class="mt-1 h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div class="h-2 rounded-full bg-zinc-900 dark:bg-white" style="width: {{ max(0, min(100, $signal['score'] ?? 0)) }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <div>
            <flux:heading size="lg">Velocity-Trend</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Anteil abgeschlossener Events je Sprint &mdash; Proxy, da Scrumly keine Story Points trackt &mdash; sowie die &Oslash; geplante Kapazität.
            </flux:text>
        </div>

        @if (empty($recentSprints))
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Noch keine Sprints vorhanden.</flux:text>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($recentSprints as $entry)
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="w-40 shrink-0">
                            <flux:text class="font-medium">{{ $entry['sprint']->name }}</flux:text>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $entry['sprint']->starts_at->format('d.m.Y') }}</flux:text>
                        </div>
                        <div class="min-w-[8rem] flex-1">
                            <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div class="h-2 rounded-full bg-blue-500" style="width: {{ $entry['completionRate'] ?? 0 }}%"></div>
                            </div>
                        </div>
                        <div class="w-28 shrink-0 text-right">
                            <flux:text class="text-sm">{{ $entry['completionRate'] !== null ? $entry['completionRate'].'%' : '–' }}</flux:text>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ $entry['eventCompleted'] }}/{{ $entry['eventTotal'] }} Events</flux:text>
                        </div>
                        <div class="w-24 shrink-0 text-right">
                            <flux:text class="text-sm">{{ $entry['avgCapacity'] !== null ? $entry['avgCapacity'].'%' : '–' }}</flux:text>
                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">&Oslash; Kapazität</flux:text>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <div>
            <flux:heading size="lg">Event-Overruns</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Welche Event-Typen laufen am häufigsten über die geplante Dauer?
            </flux:text>
        </div>

        @if (empty($eventOverruns))
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Noch keine Event-Typen definiert.</flux:text>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($eventOverruns as $entry)
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex w-40 shrink-0 items-center gap-2">
                            <flux:icon :icon="$entry['eventType']->icon" variant="outline" class="size-5 text-zinc-400" />
                            <flux:text class="font-medium">{{ $entry['eventType']->name }}</flux:text>
                        </div>

                        @if ($entry['total'] === 0)
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Noch keine abgeschlossenen Termine mit Zeiterfassung.</flux:text>
                        @else
                            <div class="min-w-[8rem] flex-1">
                                <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                                    <div class="h-2 rounded-full {{ $entry['overrunRate'] >= 50 ? 'bg-red-500' : 'bg-orange-400' }}" style="width: {{ $entry['overrunRate'] }}%"></div>
                                </div>
                            </div>
                            <div class="w-48 shrink-0 text-right">
                                <flux:text class="text-sm">{{ $entry['overrunCount'] }}/{{ $entry['total'] }} überzogen ({{ $entry['overrunRate'] }}%)</flux:text>
                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                    &Oslash; {{ $entry['avgActualMinutes'] }} Min. (geplant {{ $entry['eventType']->default_duration_minutes }} Min.)
                                </flux:text>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <flux:heading size="lg">Impediments</flux:heading>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div>
                <flux:heading size="xl">{{ $impedimentStats['open'] }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Offen</flux:text>
            </div>
            <div>
                <flux:heading size="xl">{{ $impedimentStats['escalated'] }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Eskaliert</flux:text>
            </div>
            <div>
                <flux:heading size="xl">{{ $impedimentStats['resolvedCount'] }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Gelöst</flux:text>
            </div>
            <div>
                <flux:heading size="xl">{{ $impedimentStats['avgResolutionDays'] !== null ? $impedimentStats['avgResolutionDays'].' Tage' : '–' }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">&Oslash; Lösungsdauer</flux:text>
            </div>
        </div>
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <div>
            <flux:heading size="lg">Redezeit-Balance</flux:heading>
            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Gesamt-Sprechzeit pro Mitglied im letzten Sprint mit Redezeit-Tracking.
            </flux:text>
        </div>

        @if ($speakingBalance === null)
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">Noch keine Redezeit-Daten vorhanden.</flux:text>
        @else
            <div class="flex flex-col gap-3">
                @foreach ($speakingBalance['totals'] as $entry)
                    <div class="flex items-center gap-4">
                        <div class="w-32 shrink-0">
                            <flux:text class="font-medium">{{ $entry['teamMember']?->fullName() ?? 'Unbekannt' }}</flux:text>
                        </div>
                        <div class="min-w-[8rem] flex-1">
                            <div class="h-2 w-full rounded-full bg-zinc-100 dark:bg-zinc-700">
                                <div class="h-2 rounded-full bg-blue-500" style="width: {{ $speakingBalance['maxSeconds'] > 0 ? round($entry['totalSeconds'] / $speakingBalance['maxSeconds'] * 100) : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="w-16 shrink-0 text-right">
                            <flux:text class="text-sm">{{ intdiv($entry['totalSeconds'], 60) }} Min.</flux:text>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </flux:card>
</div>
