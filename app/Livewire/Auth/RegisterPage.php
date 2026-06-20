<?php

namespace App\Livewire\Auth;

use App\Enums\UserStatus;
use App\Livewire\Concerns\UsesAccountValidationAttributes;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Services\Privacy\PrivacyPreferences;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RegisterPage extends Component
{
    use UsesAccountValidationAttributes;

    public string $displayName = '';

    public string $email = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public string $accountRole = 'guest';

    public function register()
    {
        $validated = $this->validate([
            'displayName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
            'accountRole' => ['required', Rule::in(['guest', 'host', 'both'])],
        ], attributes: $this->accountValidationAttributes());

        $isHost = in_array($validated['accountRole'], ['host', 'both'], true);
        $user = User::query()->create([
            'name' => $validated['displayName'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_host' => $isHost,
            'status' => UserStatus::Active,
        ]);

        UserProfile::query()->create([
            'user_id' => $user->id,
            'display_name' => $validated['displayName'],
            'email_verified_at' => $user->email_verified_at,
            'status' => UserStatus::Active,
        ]);
        GuestPreference::query()->create(['user_id' => $user->id]);
        $user->setting()->create([
            'locale' => app()->getLocale(),
            'currency' => 'EUR',
            'active_mode' => $isHost ? UserSetting::MODE_HOST : UserSetting::MODE_GUEST,
            'account_role' => $validated['accountRole'],
            'notification_preferences_json' => [
                'email_messages' => true,
                'email_bookings' => true,
            ],
            'privacy_preferences_json' => PrivacyPreferences::defaults(),
        ]);

        if ($isHost) {
            HostProfile::query()->create([
                'user_id' => $user->id,
                'display_name' => $validated['displayName'],
                'status' => UserStatus::Active,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);
        session()->regenerate();
        session()->put('account_mode', $isHost ? UserSetting::MODE_HOST : UserSetting::MODE_GUEST);

        return $this->redirect(route('profile.setup', ['locale' => app()->getLocale()]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.register-page')
            ->layout('layouts.guest', ['title' => __('auth.register.title')]);
    }
}
