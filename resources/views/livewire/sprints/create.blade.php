<div class="flex flex-col gap-8 p-6">
    <div>
        <flux:heading size="xl">Neuer Sprint &middot; {{ $team->name }}</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
            Die einmaligen Events dieses Teams werden automatisch mit Standard-Agenda angelegt.
        </flux:text>
    </div>

    <flux:card class="flex max-w-2xl flex-col gap-4">
        <form wire:submit="save" class="flex flex-col gap-4">
            <flux:input wire:model="form.name" label="Sprintname" placeholder="z. B. Sprint 12" autofocus />

            <flux:textarea wire:model="form.goal" label="Sprint-Ziel" placeholder="Was soll in diesem Sprint erreicht werden?" rows="3" />

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <flux:input wire:model="form.startsAt" type="date" label="Start" />
                <flux:input wire:model="form.endsAt" type="date" label="Ende" />
            </div>

            <div class="flex gap-2">
                <flux:button type="submit" variant="primary">Sprint anlegen</flux:button>
                <flux:button variant="ghost" :href="route('sprints.index', $team)" wire:navigate>
                    Abbrechen
                </flux:button>
            </div>
        </form>
    </flux:card>
</div>
