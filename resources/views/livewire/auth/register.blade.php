<div class="mx-auto flex w-full max-w-sm flex-col gap-6 py-16">
    <x-auth-header
        title="Konto erstellen"
        description="Richte Scrumly für dein Team ein."
    />

    <form wire:submit="register" class="flex flex-col gap-6">
        <flux:input
            wire:model="name"
            label="Name"
            type="text"
            required
            autofocus
            autocomplete="name"
            placeholder="Dein vollständiger Name"
        />

        <flux:input
            wire:model="email"
            label="E-Mail-Adresse"
            type="email"
            required
            autocomplete="email"
            placeholder="du@firma.de"
        />

        <flux:input
            wire:model="password"
            label="Passwort"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Passwort"
            viewable
        />

        <flux:input
            wire:model="password_confirmation"
            label="Passwort bestätigen"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Passwort wiederholen"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">Konto erstellen</flux:button>
    </form>

    <flux:text class="text-center">
        Bereits ein Konto?
        <flux:link :href="route('login')" wire:navigate>Jetzt anmelden</flux:link>
    </flux:text>
</div>
