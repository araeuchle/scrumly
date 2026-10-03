<div class="mx-auto flex w-full max-w-sm flex-col gap-6 py-16">
    <x-auth-header
        title="Passwort vergessen?"
        description="Kein Problem. Gib deine E-Mail-Adresse ein, wir senden dir einen Link zum Zurücksetzen."
    />

    @if ($status)
        <flux:callout variant="success" icon="check-circle" :text="$status" />
    @endif

    <form wire:submit="sendResetLink" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            label="E-Mail-Adresse"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="du@firma.de"
        />

        <flux:button variant="primary" type="submit" class="w-full">Link zum Zurücksetzen senden</flux:button>
    </form>

    <flux:text class="text-center">
        <flux:link :href="route('login')" wire:navigate>Zurück zur Anmeldung</flux:link>
    </flux:text>
</div>
