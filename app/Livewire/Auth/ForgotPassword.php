<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\ForgotPasswordForm;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class ForgotPassword extends Component
{
    public ForgotPasswordForm $form;

    public ?string $status = null;

    public function sendResetLink(): void
    {
        $this->form->validate();

        $status = Password::sendResetLink(['email' => $this->form->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'form.email' => __($status),
            ]);
        }

        $this->form->reset('email');
        $this->status = __($status);
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password');
    }
}
