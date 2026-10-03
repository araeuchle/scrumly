<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\ResetPasswordForm;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ResetPassword extends Component
{
    public ResetPasswordForm $form;

    public function mount(string $token): void
    {
        $this->form->token = $token;
        $this->form->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->form->validate();

        $status = Password::reset(
            [
                'token' => $this->form->token,
                'email' => $this->form->email,
                'password' => $this->form->password,
                'password_confirmation' => $this->form->password_confirmation,
            ],
            function (User $user): void {
                $user->forceFill([
                    'password' => Hash::make($this->form->password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if (! is_string($status) || $status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'form.email' => is_string($status) ? __($status) : __('passwords.throttled'),
            ]);
        }

        session()->flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.reset-password');
    }
}
