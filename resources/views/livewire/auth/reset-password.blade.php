<div class="mx-auto flex w-full max-w-sm flex-col gap-6 py-16">
    <x-auth-header
        title="Neues Passwort festlegen"
        description="Lege ein neues, sicheres Passwort für dein Konto fest."
    />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <flux:input
            wire:model="form.email"
            label="E-Mail-Adresse"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="du@firma.de"
        />

        <flux:input
            wire:model="form.password"
            label="Neues Passwort"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Passwort"
            viewable
        />

        <flux:input
            wire:model="form.password_confirmation"
            label="Passwort bestätigen"
            type="password"
            required
            autocomplete="new-password"
            placeholder="Passwort wiederholen"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">Passwort zurücksetzen</flux:button>
    </form>
</div>
