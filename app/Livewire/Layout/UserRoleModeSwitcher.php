<?php

namespace App\Livewire\Layout;

use App\Enums\UserRoleMode;
use App\Services\Users\UserRoleModeService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserRoleModeSwitcher extends Component
{
    public string $mode = 'guest';

    public function mount(): void
    {
        $mode = auth()->user()?->role_mode;
        $this->mode = $mode instanceof UserRoleMode ? $mode->value : (string) ($mode ?: UserRoleMode::Guest->value);
    }

    public function switchMode(string $mode, UserRoleModeService $roles): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->mode = $roles->setMode($user, $mode)->role_mode->value;
    }

    public function render(): View
    {
        return view('livewire.layout.user-role-mode-switcher', [
            'modes' => UserRoleMode::cases(),
        ]);
    }
}
