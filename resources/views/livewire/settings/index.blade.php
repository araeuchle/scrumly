<div class="flex flex-col gap-8 p-6">
    <div>
        <flux:heading size="xl">Profil & Sicherheit</flux:heading>
        <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
            Deine Kontodaten, Passwort und Zwei-Faktor-Authentifizierung.
        </flux:text>
    </div>

    <flux:card class="flex flex-col gap-4">
        <flux:heading size="lg">Profil</flux:heading>

        <form wire:submit="updateProfile" class="flex flex-col gap-4">
            <flux:input wire:model="profileForm.name" label="Name" autocomplete="name" />
            <flux:input wire:model="profileForm.email" label="E-Mail-Adresse" type="email" autocomplete="email" />

            <div>
                <flux:button type="submit" variant="primary">Speichern</flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <flux:heading size="lg">Passwort ändern</flux:heading>

        <form wire:submit="updatePassword" class="flex flex-col gap-4">
            <flux:input wire:model="passwordForm.currentPassword" label="Aktuelles Passwort" type="password" autocomplete="current-password" viewable />
            <flux:input wire:model="passwordForm.password" label="Neues Passwort" type="password" autocomplete="new-password" viewable />
            <flux:input wire:model="passwordForm.password_confirmation" label="Neues Passwort bestätigen" type="password" autocomplete="new-password" viewable />

            <div>
                <flux:button type="submit" variant="primary">Passwort ändern</flux:button>
            </div>
        </form>
    </flux:card>

    <flux:card class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <flux:heading size="lg">Zwei-Faktor-Authentifizierung</flux:heading>
            @if (auth()->user()->hasTwoFactorEnabled())
                <flux:badge color="lime" size="sm">Aktiv</flux:badge>
            @else
                <flux:badge color="zinc" size="sm">Inaktiv</flux:badge>
            @endif
        </div>

        @if (auth()->user()->hasTwoFactorEnabled())
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Zwei-Faktor-Authentifizierung ist aktiv. Bei der Anmeldung wird zusätzlich zum
                Passwort ein Code aus deiner Authenticator-App abgefragt.
            </flux:text>

            @if (! empty($freshRecoveryCodes))
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-900/20">
                    <flux:text class="text-sm font-medium">Neue Wiederherstellungscodes &mdash; jetzt sicher aufbewahren</flux:text>
                    <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        Jeder Code kann einmal verwendet werden, falls du keinen Zugriff mehr auf deine
                        Authenticator-App hast. Sie werden nur jetzt angezeigt.
                    </flux:text>
                    <div class="mt-3 grid grid-cols-2 gap-2 font-mono text-sm">
                        @foreach ($freshRecoveryCodes as $recoveryCode)
                            <div>{{ $recoveryCode }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex gap-2">
                <flux:button variant="ghost" wire:click="regenerateRecoveryCodes">
                    Wiederherstellungscodes neu erzeugen
                </flux:button>

                <flux:modal.trigger name="disable-two-factor">
                    <flux:button variant="danger">Deaktivieren</flux:button>
                </flux:modal.trigger>

                <flux:modal name="disable-two-factor" class="min-w-[22rem]">
                    <div class="flex flex-col gap-6">
                        <div>
                            <flux:heading size="lg">Zwei-Faktor-Authentifizierung deaktivieren?</flux:heading>
                            <flux:text class="mt-2">
                                Bei der nächsten Anmeldung reicht dann wieder nur das Passwort.
                            </flux:text>
                        </div>

                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button variant="ghost">Abbrechen</flux:button>
                            </flux:modal.close>
                            <flux:modal.close>
                                <flux:button variant="danger" wire:click="disableTwoFactor">Deaktivieren</flux:button>
                            </flux:modal.close>
                        </div>
                    </div>
                </flux:modal>
            </div>
        @elseif ($showingQrCode)
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Scanne den QR-Code mit deiner Authenticator-App (z. B. Google Authenticator, Authy) und
                gib den angezeigten 6-stelligen Code zur Bestätigung ein.
            </flux:text>

            @if ($qrCodeSvg)
                <div class="w-fit rounded-lg bg-white p-4">
                    {!! $qrCodeSvg !!}
                </div>
            @endif

            <form wire:submit="confirmTwoFactor" class="flex flex-col gap-4 sm:max-w-xs">
                <flux:input wire:model="twoFactorConfirmationForm.code" label="Bestätigungscode" placeholder="123456" autocomplete="one-time-code" autofocus />

                <div class="flex gap-2">
                    <flux:button type="submit" variant="primary">Bestätigen</flux:button>
                    <flux:button type="button" variant="ghost" wire:click="disableTwoFactor">Abbrechen</flux:button>
                </div>
            </form>
        @else
            <flux:text class="text-zinc-500 dark:text-zinc-400">
                Schütze dein Konto zusätzlich mit einem Code aus einer Authenticator-App.
            </flux:text>

            <div>
                <flux:button variant="primary" wire:click="enableTwoFactor">Aktivieren</flux:button>
            </div>
        @endif
    </flux:card>
</div>
