<div class="flex flex-col gap-8 p-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Teams</flux:heading>
            <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                Wähle ein Team aus oder leg ein neues an.
            </flux:text>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="$set('showCreateForm', true)">
            Neues Team
        </flux:button>
    </div>

    @if ($showCreateForm)
        <flux:card class="flex flex-col gap-4">
            <flux:heading size="lg">Neues Team anlegen</flux:heading>

            <form wire:submit="createTeam" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <flux:input wire:model="form.name" label="Teamname" placeholder="z. B. Team Phoenix" autofocus />
                </div>
                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">Anlegen</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="$set('showCreateForm', false)">
                        Abbrechen
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    @if ($teams->isEmpty())
        <flux:card class="flex flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="user-group" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Noch kein Team</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Leg dein erstes Team an, um Sprints zu planen.
            </flux:text>
        </flux:card>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($teams as $team)
                <button type="button" wire:click="selectTeam('{{ $team->id }}')" class="block w-full text-left">
                    <flux:card class="flex flex-col gap-3 transition hover:border-zinc-300 dark:hover:border-zinc-600">
                        <flux:heading size="lg">{{ $team->name }}</flux:heading>
                        <flux:text class="text-zinc-500 dark:text-zinc-400">
                            {{ $team->sprints_count }} {{ $team->sprints_count === 1 ? 'Sprint' : 'Sprints' }}
                        </flux:text>
                    </flux:card>
                </button>
            @endforeach
        </div>
    @endif
</div>
