<?php

namespace App\Livewire\Account;

use App\Livewire\Concerns\UsesAccountValidationAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class SecuritySettingsPage extends Component
{
    use UsesAccountValidationAttributes;

    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function save(): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ], attributes: $this->accountValidationAttributes());

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
        session()->flash('success', __('notifications.flash.security_updated'));
    }

    public function render(): View
    {
        return view('livewire.account.security-settings-page')
            ->layout('layouts.app', ['title' => __('account.security.title')]);
    }
}
