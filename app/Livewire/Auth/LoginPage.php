<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Models\UserSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class LoginPage extends Component
{
    use UsesAccountValidationAttributes;

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], attributes: $this->accountValidationAttributes());

        if (! Auth::attempt($credentials, $this->remember)) {
            $this->addError('email', __('auth.failed'));

            return null;
        }

        session()->regenerate();

        $mode = Auth::user()?->setting?->active_mode ?: UserSetting::MODE_GUEST;
        session()->put('account_mode', $mode);

        $route = $mode === UserSetting::MODE_HOST && Auth::user()?->is_host
            ? 'host.dashboard'
            : 'home';

        return $this->redirectIntended(route($route, ['locale' => app()->getLocale()]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.login-page')
            ->layout('layouts.guest', ['title' => __('auth.login.title')]);
    }
}
