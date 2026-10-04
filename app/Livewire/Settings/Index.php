<?php

namespace App\Livewire\Settings;

use App\Livewire\Forms\PasswordForm;
use App\Livewire\Forms\ProfileForm;
use App\Livewire\Forms\TwoFactorConfirmationForm;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public ProfileForm $profileForm;

    public PasswordForm $passwordForm;

    public TwoFactorConfirmationForm $twoFactorConfirmationForm;

    public bool $showingQrCode = false;

    /** @var array<int, string> */
    public array $freshRecoveryCodes = [];

    public function mount(): void
    {
        $this->profileForm->name = $this->currentUser()->name;
        $this->profileForm->email = $this->currentUser()->email;
    }

    public function updateProfile(): void
    {
        $validated = $this->profileForm->validate();

        $emailTaken = User::query()
            ->where('email', $validated['email'])
            ->whereKeyNot($this->currentUser()->id)
            ->exists();

        if ($emailTaken) {
            $this->addError('profileForm.email', 'Diese E-Mail-Adresse wird bereits verwendet.');

            return;
        }

        $this->currentUser()->update($validated);
    }

    public function updatePassword(): void
    {
        $validated = $this->passwordForm->validate();

        $this->currentUser()->update(['password' => Hash::make($validated['password'])]);

        $this->passwordForm->reset();
    }

    public function enableTwoFactor(): void
    {
        $this->currentUser()->update([
            'two_factor_secret' => (new Google2FA())->generateSecretKey(),
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        $this->freshRecoveryCodes = [];
        $this->showingQrCode = true;
    }

    public function confirmTwoFactor(): void
    {
        $validated = $this->twoFactorConfirmationForm->validate();

        $user = $this->currentUser();
        $secret = $user->two_factor_secret;

        if ($secret === null || ! (new Google2FA())->verifyKey($secret, $validated['code'])) {
            $this->addError('twoFactorConfirmationForm.code', 'Der Code ist ungültig.');

            return;
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        $this->freshRecoveryCodes = $recoveryCodes;
        $this->showingQrCode = false;
        $this->twoFactorConfirmationForm->reset();
    }

    public function disableTwoFactor(): void
    {
        $this->currentUser()->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $this->showingQrCode = false;
        $this->freshRecoveryCodes = [];
    }

    public function regenerateRecoveryCodes(): void
    {
        $recoveryCodes = $this->generateRecoveryCodes();

        $this->currentUser()->update(['two_factor_recovery_codes' => $recoveryCodes]);

        $this->freshRecoveryCodes = $recoveryCodes;
    }

    public function render(): View
    {
        $qrCodeSvg = null;
        $secret = $this->currentUser()->two_factor_secret;

        if ($this->showingQrCode && $secret !== null) {
            $appName = config('app.name');

            $qrCodeUrl = (new Google2FA())->getQRCodeUrl(
                is_string($appName) ? $appName : 'Scrumly',
                $this->currentUser()->email,
                $secret,
            );

            $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
            $qrCodeSvg = (new Writer($renderer))->writeString($qrCodeUrl);
        }

        return view('livewire.settings.index', [
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function generateRecoveryCodes(): array
    {
        return array_map(
            fn () => Str::random(5).'-'.Str::random(5),
            range(1, 8),
        );
    }

    private function currentUser(): User
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }
}
