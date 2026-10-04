<x-layouts.guest>
    <div class="flex flex-col gap-24 py-16">
        {{-- Hero --}}
        <div class="mx-auto flex max-w-3xl flex-col items-center gap-6 text-center">
            <flux:badge color="blue" size="sm">Für Scrum Master gebaut</flux:badge>

            <flux:heading size="xl" class="text-4xl font-bold tracking-tight sm:text-5xl">
                Weniger Organisation. Mehr Coaching.
            </flux:heading>

            <flux:text class="text-lg text-zinc-500 dark:text-zinc-400">
                Scrumly nimmt dir die organisatorische Last von Sprint-Events, Retrospektiven, Impediments und
                1:1-Gesprächen ab, damit du dich auf das konzentrieren kannst, was wirklich zählt: dein Team.
            </flux:text>

            <div class="flex gap-3">
                <flux:button :href="route('register')" variant="primary" wire:navigate>
                    Kostenlos starten
                </flux:button>
                <flux:button :href="route('login')" variant="ghost" wire:navigate>
                    Anmelden
                </flux:button>
            </div>
        </div>

        {{-- Features --}}
        <div class="mx-auto grid w-full max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                [
                    'icon' => 'calendar-days',
                    'title' => 'Sprint- & Event-Management',
                    'description' => 'Individuelle Event-Typen pro Team, automatisch erzeugte Termine und Kapazitätsplanung für Daily, Planning, Review und Retro.',
                ],
                [
                    'icon' => 'chat-bubble-left-right',
                    'title' => 'Retrospektiven',
                    'description' => 'Board-Link eurer Wahl griffbereit, Vor- und Nachbereitung in Notizen und Action Items, die bis zur Erledigung sichtbar bleiben.',
                ],
                [
                    'icon' => 'exclamation-triangle',
                    'title' => 'Impediment-Tracker',
                    'description' => 'Blocker erfassen, eskalieren und auflösen — mit Priorität und Status statt in Zetteln oder Chat-Nachrichten zu verlieren.',
                ],
                [
                    'icon' => 'user-circle',
                    'title' => '1:1-Gespräche',
                    'description' => 'Ein Zeitstrahl pro Team-Mitglied für Notizen aus jedem Gespräch, inklusive eigener Action Items und Erinnerungen bei langer Funkstille.',
                ],
                [
                    'icon' => 'chart-bar',
                    'title' => 'Team-Metriken & Health',
                    'description' => 'Velocity-Proxy, Übersicht häufig überzogener Event-Typen und ein Team-Health-Indikator auf einen Blick.',
                ],
                [
                    'icon' => 'book-open',
                    'title' => 'Scrum-Master-Tagebuch',
                    'description' => 'Drei Fragen zur täglichen Selbstreflexion — durchsuchbar, nach Zeitraum filterbar und mit Emotionen taggbar.',
                ],
            ] as $feature)
                <flux:card class="flex flex-col gap-3">
                    <span class="flex size-10 items-center justify-center rounded-lg bg-zinc-900/5 dark:bg-white/10">
                        <flux:icon :icon="$feature['icon']" variant="outline" class="size-5" />
                    </span>
                    <flux:heading size="lg">{{ $feature['title'] }}</flux:heading>
                    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $feature['description'] }}</flux:text>
                </flux:card>
            @endforeach
        </div>

        {{-- CTA --}}
        <flux:card class="mx-auto flex w-full max-w-4xl flex-col items-center gap-4 p-12 text-center">
            <flux:heading size="xl">Bereit, deinem Team Zeit zurückzugeben?</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Leg jetzt los &mdash; kostenlos und in wenigen Minuten eingerichtet.
            </flux:text>
            <flux:button :href="route('register')" variant="primary" wire:navigate>
                Jetzt kostenlos registrieren
            </flux:button>
        </flux:card>
    </div>
</x-layouts.guest>
