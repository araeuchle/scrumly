<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-900">
    <flux:header container class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <a href="{{ route('home') }}" wire:navigate>
            <x-app-logo />
        </a>

        <flux:spacer />

        <flux:navbar class="gap-2">
            @auth
                <flux:button :href="route('dashboard')" variant="primary" wire:navigate>Zum Dashboard</flux:button>
            @else
                <flux:button :href="route('login')" variant="ghost" wire:navigate>Anmelden</flux:button>
                <flux:button :href="route('register')" variant="primary" wire:navigate>Kostenlos starten</flux:button>
            @endauth
        </flux:navbar>
    </flux:header>

    <flux:main container>
        {{ $slot }}
    </flux:main>

    <footer class="[grid-area:footer] border-t border-zinc-200 py-8 dark:border-zinc-700">
        <div class="mx-auto max-w-7xl px-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
            &copy; {{ now()->year }} {{ config('app.name') }}. Gebaut für Scrum Master.
        </div>
    </footer>

    @fluxScripts
</body>
</html>
