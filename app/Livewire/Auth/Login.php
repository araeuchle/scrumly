<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
use App\Livewire\Forms\TwoFactorChallengeForm;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public LoginForm $form;

    public TwoFactorChallengeForm $twoFactorForm;

    public bool $needsTwoFactor = false;

    public function login(): void
    {
        $this->form->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::validate(['email' => $this->form->email, 'password' => $this->form->password])) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $user = User::where('email', $this->form->email)->firstOrFail();

        if ($user->hasTwoFactorEnabled()) {
            session(['login.2fa.user_id' => $user->id, 'login.2fa.remember' => $this->form->remember]);
            $this->needsTwoFactor = true;

            return;
        }

        Auth::login($user, $this->form->remember);
        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function confirmTwoFactorChallenge(): void
    {
        $this->twoFactorForm->validate();

        $this->ensureTwoFactorIsNotRateLimited();

        $userId = session('login.2fa.user_id');
        $user = is_string($userId) ? User::find($userId) : null;

        if (! $user instanceof User) {
            $this->needsTwoFactor = false;

            throw ValidationException::withMessages(['twoFactorForm.code' => __('auth.failed')]);
        }

        if (! $this->verifyTwoFactorCode($user, $this->twoFactorForm->code)) {
            RateLimiter::hit($this->twoFactorThrottleKey());

            throw ValidationException::withMessages(['twoFactorForm.code' => 'Der Code ist ungültig.']);
        }

        RateLimiter::clear($this->twoFactorThrottleKey());

        $remember = (bool) session('login.2fa.remember', false);
        session()->forget(['login.2fa.user_id', 'login.2fa.remember']);

        Auth::login($user, $remember);
        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
    }

    protected function verifyTwoFactorCode(User $user, string $code): bool
    {
        if ($user->two_factor_secret !== null && (new Google2FA())->verifyKey($user->two_factor_secret, $code)) {
            return true;
        }

        $recoveryCodes = $user->two_factor_recovery_codes ?? [];
        $matchedIndex = array_search($code, $recoveryCodes, true);

        if ($matchedIndex === false) {
            return false;
        }

        unset($recoveryCodes[$matchedIndex]);
        $user->update(['two_factor_recovery_codes' => array_values($recoveryCodes)]);

        return true;
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function ensureTwoFactorIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->twoFactorThrottleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->twoFactorThrottleKey());

        throw ValidationException::withMessages([
            'twoFactorForm.code' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->form->email).'|'.request()->ip());
    }

    protected function twoFactorThrottleKey(): string
    {
        return 'two-factor|'.$this->throttleKey();
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
