<div class="flex flex-col gap-8 p-6">
    <div>
        <flux:heading size="xl">Willkommen zurück, {{ auth()->user()->name }} 👋</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
            Das ist dein Scrumly-Dashboard. Die folgenden Bereiche entstehen als Nächstes.
        </flux:text>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['icon' => 'calendar-days', 'title' => 'Sprint- & Event-Management', 'description' => 'Sprint-Planung, Kapazitäten und automatische Meeting-Agenden.'],
            ['icon' => 'chat-bubble-left-right', 'title' => 'Retrospektiven', 'description' => 'Templates, anonymes Feedback und nachverfolgte Action Items.'],
            ['icon' => 'exclamation-triangle', 'title' => 'Impediment-Tracker', 'description' => 'Blocker zentral erfassen, eskalieren und auflösen.'],
            ['icon' => 'chart-bar', 'title' => 'Team-Metriken', 'description' => 'Velocity, Burndown und Team-Health auf einen Blick.'],
            ['icon' => 'academic-cap', 'title' => 'Coaching-Assistent', 'description' => 'Checklisten und Hinweise für deine Scrum-Events.'],
            ['icon' => 'user-group', 'title' => 'Mehrere Teams', 'description' => 'Scrum-of-Scrums-Ansicht für mehrere Teams gleichzeitig.'],
        ] as $feature)
            <flux:card class="flex flex-col gap-3">
                <flux:icon :icon="$feature['icon']" variant="outline" class="size-8 text-zinc-400" />
                <flux:heading size="lg">{{ $feature['title'] }}</flux:heading>
                <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $feature['description'] }}</flux:text>
                <flux:badge color="zinc" size="sm" class="w-fit">In Entwicklung</flux:badge>
            </flux:card>
        @endforeach
    </div>
</div>
