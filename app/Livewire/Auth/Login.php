<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->form->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->form->email, 'password' => $this->form->password], $this->form->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        session()->regenerate();

        $this->redirectRoute('dashboard', navigate: true);
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

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->form->email).'|'.request()->ip());
    }

    public function render(): View
    {
        return view('livewire.auth.login');
    }
}
