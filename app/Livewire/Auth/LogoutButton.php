<?php

namespace App\Livewire\Auth;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LogoutButton extends Component
{
    public function logout(): void
    {
        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirectRoute('home', ['locale' => app()->getLocale()], navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.logout-button');
    }
}
