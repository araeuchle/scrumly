<div class="flex flex-col gap-10 py-16">
    <div class="mx-auto flex max-w-3xl flex-col items-center gap-3 text-center">
        <flux:badge color="blue" size="sm">Magazin</flux:badge>
        <flux:heading size="xl" class="text-4xl font-bold tracking-tight">Neuigkeiten &amp; Hintergründe</flux:heading>
        <flux:text class="text-lg text-zinc-500 dark:text-zinc-400">
            Artikel rund um Scrum, Teamführung und Scrumly selbst.
        </flux:text>
    </div>

    @if ($posts->isEmpty())
        <flux:card class="mx-auto flex w-full max-w-2xl flex-col items-center gap-3 p-12 text-center">
            <flux:icon icon="newspaper" variant="outline" class="size-10 text-zinc-400" />
            <flux:heading size="lg">Noch keine Artikel</flux:heading>
            <flux:text class="text-zinc-500 dark:text-zinc-400">Schau bald wieder vorbei.</flux:text>
        </flux:card>
    @else
        <div class="mx-auto grid w-full max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $post)
                <flux:link :href="route('magazine.show', $post)" wire:navigate class="block">
                    <flux:card class="flex h-full flex-col gap-3">
                        @if ($post->cover_image_url)
                            <img src="{{ $post->cover_image_url }}" alt="" class="aspect-video w-full rounded-lg object-cover">
                        @endif

                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $post->published_at->translatedFormat('d. F Y') }}
                            @if ($post->editor)
                                &middot; {{ $post->editor->name }}
                            @endif
                        </flux:text>

                        <flux:heading size="lg">{{ $post->title }}</flux:heading>

                        @if ($post->excerpt)
                            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ $post->excerpt }}</flux:text>
                        @endif
                    </flux:card>
                </flux:link>
            @endforeach
        </div>
    @endif
</div>
