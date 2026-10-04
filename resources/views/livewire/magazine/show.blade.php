<div class="mx-auto flex max-w-3xl flex-col gap-8 py-16">
    <div class="flex flex-col gap-3">
        <flux:link :href="route('magazine.index')" wire:navigate class="text-sm">
            &larr; Zurück zum Magazin
        </flux:link>

        <flux:heading size="xl" class="text-4xl font-bold tracking-tight">{{ $post->title }}</flux:heading>

        <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ $post->published_at->translatedFormat('d. F Y') }}
            @if ($post->editor)
                &middot; {{ $post->editor->name }}
            @endif
        </flux:text>
    </div>

    @if ($post->cover_image_url)
        <img src="{{ $post->cover_image_url }}" alt="" class="aspect-video w-full rounded-xl object-cover">
    @endif

    <div class="prose prose-zinc dark:prose-invert max-w-none">
        {!! Str::markdown($post->body) !!}
    </div>
</div>
