<div class="mx-auto flex w-full max-w-sm flex-col gap-6 py-16">
    @if ($needsTwoFactor)
        <x-auth-header
            title="Zwei-Faktor-Authentifizierung"
            description="Gib den 6-stelligen Code aus deiner Authenticator-App ein, oder nutze einen Wiederherstellungscode."
        />

        <form wire:submit="confirmTwoFactorChallenge" class="flex flex-col gap-6">
            <flux:input
                wire:model="twoFactorForm.code"
                label="Code"
                placeholder="123456"
                required
                autofocus
                autocomplete="one-time-code"
            />

            <flux:button variant="primary" type="submit" class="w-full">Bestätigen</flux:button>
        </form>
    @else
        <x-auth-header
            title="Bei Scrumly anmelden"
            description="Schön, dich wiederzusehen. Gib deine Zugangsdaten ein."
        />

        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :text="session('status')" />
        @endif

        <form wire:submit="login" class="flex flex-col gap-6">
            <flux:input
                wire:model="form.email"
                label="E-Mail-Adresse"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="du@firma.de"
            />

            <div class="flex flex-col gap-2">
                <flux:input
                    wire:model="form.password"
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

            <flux:checkbox wire:model="form.remember" label="Angemeldet bleiben" />

            <flux:button variant="primary" type="submit" class="w-full">Anmelden</flux:button>
        </form>

        <flux:text class="text-center">
            Noch kein Konto?
            <flux:link :href="route('register')" wire:navigate>Jetzt registrieren</flux:link>
        </flux:text>
    @endif
</div>
