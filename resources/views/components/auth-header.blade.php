@props(['title', 'description'])

<div class="flex flex-col gap-2 text-center">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
</div>
