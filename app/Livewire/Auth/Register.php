<?php

namespace App\Livewire\Auth;

use App\Livewire\Forms\RegisterForm;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Register extends Component
{
    public RegisterForm $form;

    public function register(): void
    {
        $validated = $this->form->validate();

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        Auth::login($user);

        $this->redirectRoute('dashboard', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register');
    }
}
