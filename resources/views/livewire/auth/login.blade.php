<div class="mx-auto flex w-full max-w-sm flex-col gap-6 py-16">
    <x-auth-header
        title="Bei Scrumly anmelden"
        description="Schön, dich wiederzusehen. Gib deine Zugangsdaten ein."
    />

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle" :text="session('status')" />
    @endif

    <form wire:submit="login" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            label="E-Mail-Adresse"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="du@firma.de"
        />

        <div class="flex flex-col gap-2">
            <flux:input
                wire:model="password"
                label="Passwort"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Passwort"
                viewable
            />

            @if (Route::has('password.request'))
                <flux:link :href="route('password.request')" class="text-sm" wire:navigate>
                    Passwort vergessen?
                </flux:link>
            @endif
        </div>

        <flux:checkbox wire:model="remember" label="Angemeldet bleiben" />

        <flux:button variant="primary" type="submit" class="w-full">Anmelden</flux:button>
    </form>

    <flux:text class="text-center">
        Noch kein Konto?
        <flux:link :href="route('register')" wire:navigate>Jetzt registrieren</flux:link>
    </flux:text>
</div>
