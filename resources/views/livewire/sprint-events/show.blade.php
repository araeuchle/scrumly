<div class="flex flex-col gap-8 p-6" @if ($sprintEvent->status->value === 'in_progress') wire:poll.1s @endif>
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                <flux:link :href="route('sprints.show', $sprintEvent->sprint)" wire:navigate>{{ $sprintEvent->sprint->name }}</flux:link>
            </flux:text>
            <flux:heading size="xl">{{ $sprintEvent->teamEventType->name }}</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                {{ $sprintEvent->scheduled_date->format('d.m.Y') }} &middot; {{ $sprintEvent->duration_minutes }} Minuten geplant
            </flux:text>
        </div>

        <flux:badge
            size="lg"
            :color="match ($sprintEvent->status->value) {
                'in_progress' => 'lime',
                'completed' => 'zinc',
                default => 'blue',
            }"
        >
            {{ $sprintEvent->status->label() }}
        </flux:badge>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <flux:card class="flex flex-col items-center gap-4 py-10 text-center">
                @php
                    $isCompleted = $sprintEvent->status->value === 'completed';
                    $remaining = $sprintEvent->remainingSeconds();
                    $overtime = $remaining < 0;
                    $display = $isCompleted ? ($sprintEvent->duration_minutes * 60) - $remaining : abs($remaining);
                    $minutes = intdiv($display, 60);
                    $seconds = $display % 60;
                @endphp

                <flux:text class="text-sm text-zinc-500 uppercase tracking-wide dark:text-zinc-400">
                    @if ($isCompleted)
                        Tatsächliche Dauer
                    @else
                        {{ $overtime ? 'Überzogen um' : 'Verbleibend' }}
                    @endif
                </flux:text>

                <flux:heading size="xl" class="text-6xl font-bold tabular-nums {{ $overtime ? 'text-red-500' : '' }}">
                    {{ (! $isCompleted && $overtime) ? '+' : '' }}{{ sprintf('%02d:%02d', $minutes, $seconds) }}
                </flux:heading>

                @if ($isCompleted && $overtime)
                    <flux:badge size="sm" color="red">{{ abs($remaining) >= 60 ? intdiv(abs($remaining), 60).' Min.' : abs($remaining).' Sek.' }} über geplanter Zeit</flux:badge>
                @endif

                @can('update', $sprintEvent)
                    <div class="flex gap-2">
                        @if ($sprintEvent->status->value === 'scheduled')
                            <flux:button variant="primary" icon="play" wire:click="start">Event starten</flux:button>
                        @elseif ($sprintEvent->status->value === 'in_progress')
                            <flux:button variant="primary" icon="stop" wire:click="complete">Event beenden</flux:button>
                        @endif
                    </div>
                @endcan
            </flux:card>

            @if ($sprintEvent->teamEventType->is_retrospective)
                <flux:card class="flex flex-col gap-4">
                    <flux:heading size="lg">Board</flux:heading>

                    @can('update', $sprintEvent)
                        <div class="flex items-end gap-2">
                            <div class="flex-1">
                                <flux:input wire:model.live.debounce.750ms="boardUrl" type="url" label="Link zum Board" placeholder="https://miro.com/app/board/…" />
                            </div>
                            @if ($boardUrl)
                                <flux:button variant="ghost" icon="arrow-top-right-on-square" :href="$boardUrl" target="_blank">
                                    Öffnen
                                </flux:button>
                            @endif
                        </div>
                    @else
                        @if ($sprintEvent->board_url)
                            <flux:link :href="$sprintEvent->board_url" target="_blank">{{ $sprintEvent->board_url }}</flux:link>
                        @else
                            <flux:text class="text-zinc-500 dark:text-zinc-400">Kein Board hinterlegt.</flux:text>
                        @endif
                    @endcan
                </flux:card>

                <flux:card class="flex flex-col gap-4">
                    <flux:heading size="lg">Offene Action Items</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        Auch aus vorherigen Retros &mdash; diese gehen beim Sprintwechsel nicht verloren.
                    </flux:text>

                    @can('update', $sprintEvent)
                        <form wire:submit="addActionItem" class="flex items-end gap-2">
                            <div class="flex-1">
                                <flux:input wire:model="actionItemForm.description" placeholder="Neues Action Item" />
                            </div>
                            <flux:button type="submit" variant="primary" icon="plus">Hinzufügen</flux:button>
                        </form>
                    @endcan

                    @if ($retroActionItems->isEmpty())
                        <flux:text class="text-zinc-500 dark:text-zinc-400">Keine offenen Action Items.</flux:text>
                    @else
                        <div class="flex flex-col gap-2">
                            @foreach ($retroActionItems as $actionItem)
                                <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-100 p-3 dark:border-zinc-700" wire:key="action-item-{{ $actionItem->id }}">
                                    <flux:text>{{ $actionItem->description }}</flux:text>

                                    @can('update', $sprintEvent)
                                        <div class="flex shrink-0 items-center gap-1">
                                            <flux:button size="sm" variant="ghost" icon="check" wire:click="completeActionItem({{ $actionItem->id }})">
                                                Erledigt
                                            </flux:button>
                                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteActionItem({{ $actionItem->id }})" />
                                        </div>
                                    @endcan
                                </div>
                            @endforeach
                        </div>
                    @endif
                </flux:card>
            @endif

            @if ($sprintEvent->teamEventType->track_speaking_time && count($speakingTurns) > 0)
                <flux:card class="flex flex-col gap-4">
                    <flux:heading size="lg">Redezeit</flux:heading>

                    <div class="flex flex-col gap-2">
                        @foreach ($speakingTurns as $data)
                            @php
                                $turn = $data['turn'];
                                $seconds = $turn?->currentSeconds() ?? 0;
                                $isActive = $turn?->isActive() ?? false;
                                $tMinutes = intdiv($seconds, 60);
                                $tSeconds = $seconds % 60;
                            @endphp

                            <div class="flex items-center justify-between rounded-lg border border-zinc-100 p-3 dark:border-zinc-700" wire:key="speaker-{{ $data['teamMember']->id }}">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$data['teamMember']->fullName()" />
                                    <flux:text class="font-medium">{{ $data['teamMember']->fullName() }}</flux:text>
                                </div>

                                <div class="flex items-center gap-3">
                                    <flux:text class="tabular-nums text-zinc-500 dark:text-zinc-400">
                                        {{ sprintf('%02d:%02d', $tMinutes, $tSeconds) }}
                                    </flux:text>

                                    <flux:button
                                        size="sm"
                                        :variant="$isActive ? 'primary' : 'ghost'"
                                        :icon="$isActive ? 'stop' : 'microphone'"
                                        wire:click="toggleSpeaking({{ $data['teamMember']->id }})"
                                    >
                                        {{ $isActive ? 'Stopp' : 'Sprechen' }}
                                    </flux:button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            @endif
        </div>

        <div class="flex flex-col gap-6">
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">Agenda</flux:heading>

                @can('update', $sprintEvent)
                    <flux:textarea wire:model.live.debounce.750ms="agenda" rows="8" />
                @else
                    <flux:text class="whitespace-pre-line">{{ $sprintEvent->agenda }}</flux:text>
                @endcan
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">Notizen</flux:heading>

                @can('update', $sprintEvent)
                    <flux:textarea wire:model.live.debounce.750ms="notes" rows="6" placeholder="Was ist während des Events passiert? Blocker, Entscheidungen, Ergebnisse …" />
                @else
                    <flux:text class="whitespace-pre-line">{{ $sprintEvent->notes }}</flux:text>
                @endcan
            </flux:card>
        </div>
    </div>
</div>
