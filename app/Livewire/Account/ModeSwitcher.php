<?php

namespace App\Livewire\Account;

use App\Enums\UserStatus;
use App\Models\HostProfile;
use App\Models\UserSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ModeSwitcher extends Component
{
    public string $mode = UserSetting::MODE_GUEST;

    public function mount(): void
    {
        $this->mode = request()->routeIs('host.*')
            ? UserSetting::MODE_HOST
            : (session('account_mode') ?: UserSetting::MODE_GUEST);
    }

    public function switchMode(string $mode)
    {
        if (! in_array($mode, [UserSetting::MODE_GUEST, UserSetting::MODE_HOST], true)) {
            return null;
        }

        if (! auth()->check()) {
            return $this->redirect(route('auth.login'), navigate: true);
        }

        $user = auth()->user();

        if ($mode === UserSetting::MODE_HOST) {
            $user->update(['is_host' => true]);
            HostProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'display_name' => $user->profile?->display_name ?: $user->name,
                    'status' => UserStatus::Active,
                ],
            );
        }

        $accountRole = $user->setting?->account_role ?: ($user->is_host ? UserSetting::ROLE_BOTH : UserSetting::ROLE_GUEST);

        if ($mode === UserSetting::MODE_HOST && $accountRole === UserSetting::ROLE_GUEST) {
            $accountRole = UserSetting::ROLE_BOTH;
        }

        if ($mode === UserSetting::MODE_GUEST && $accountRole === UserSetting::ROLE_HOST) {
            $accountRole = UserSetting::ROLE_BOTH;
        }

        $user->setting()->updateOrCreate([], [
            'active_mode' => $mode,
            'account_role' => $accountRole,
        ]);
        session()->put('account_mode', $mode);
        $this->mode = $mode;

        return $this->redirect(route($mode === UserSetting::MODE_HOST ? 'host.dashboard' : 'home', [
            'locale' => app()->getLocale(),
        ]), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.account.mode-switcher');
    }
}
