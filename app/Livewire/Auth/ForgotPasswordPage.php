<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\UsesAccountValidationAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Password;
use Livewire\Component;

class ForgotPasswordPage extends Component
{
    use UsesAccountValidationAttributes;

    public string $email = '';

    public ?string $statusMessage = null;

    public function sendResetLink(): void
    {
        $validated = $this->validate([
            'email' => ['required', 'email'],
        ], attributes: $this->accountValidationAttributes());

        $status = Password::sendResetLink(['email' => $validated['email']]);

        if ($status === Password::ResetLinkSent) {
            $this->statusMessage = __($status);

            return;
        }

        $this->addError('email', __($status));
    }

    public function render(): View
    {
        return view('livewire.auth.forgot-password-page')
            ->layout('layouts.guest', ['title' => __('auth.forgot.title')]);
    }
}
