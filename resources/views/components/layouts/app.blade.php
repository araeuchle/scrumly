<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2" wire:navigate>
            <x-app-logo />
        </a>

        @php
            $currentTeam = auth()->user()?->resolveCurrentTeam();
        @endphp

        @if ($currentTeam)
            <div class="mt-4">
                <livewire:teams.switcher :key="'team-switcher-'.$currentTeam->id" />
            </div>
        @endif

        <flux:navlist variant="outline" class="mt-4">
            <flux:navlist.group heading="Übersicht">
                <flux:navlist.item icon="squares-2x2" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    Dashboard
                </flux:navlist.item>
                <flux:navlist.item icon="user-group" :href="route('teams.index')" :current="request()->routeIs('teams.index')" wire:navigate>
                    Teams
                </flux:navlist.item>
                <flux:navlist.item icon="book-open" :href="route('journal.index')" :current="request()->routeIs('journal.index')" wire:navigate>
                    Tagebuch
                </flux:navlist.item>
            </flux:navlist.group>

            @if ($currentTeam)
                <flux:navlist.group heading="{{ $currentTeam->name }}" class="mt-6">
                    <flux:navlist.item icon="users" :href="route('teams.members', $currentTeam)" :current="request()->routeIs('teams.members')" wire:navigate>
                        Mitglieder
                    </flux:navlist.item>
                    <flux:navlist.item icon="calendar-days" :href="route('sprints.index', $currentTeam)" :current="request()->routeIs('sprints.*', 'sprint-events.*')" wire:navigate>
                        Sprints
                    </flux:navlist.item>
                    <flux:navlist.item icon="adjustments-horizontal" :href="route('teams.event-types', $currentTeam)" :current="request()->routeIs('teams.event-types')" wire:navigate>
                        Event-Typen
                    </flux:navlist.item>
                    <flux:navlist.item icon="exclamation-triangle" :href="route('teams.impediments', $currentTeam)" :current="request()->routeIs('teams.impediments')" wire:navigate>
                        Impediments
                    </flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" :href="route('teams.metrics', $currentTeam)" :current="request()->routeIs('teams.metrics')" wire:navigate>
                        Team-Metriken
                    </flux:navlist.item>
                </flux:navlist.group>
            @endif
        </flux:navlist>

        <flux:spacer />

        <flux:dropdown position="bottom" align="start">
            <flux:sidebar.profile :name="auth()->user()->name" />

            <flux:menu>
                <flux:menu.item disabled>{{ auth()->user()->email }}</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        Abmelden
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:sidebar.profile :name="auth()->user()->name" />

            <flux:menu>
                <flux:menu.item disabled>{{ auth()->user()->email }}</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        Abmelden
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>
</html>
