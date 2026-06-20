<?php

namespace App\Livewire\Profile;

use App\Models\User;
use App\Services\Users\UserRoleModeService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RoleModeSwitcher extends Component
{
    public function switchMode(string $mode, UserRoleModeService $roles): void
    {
        $user = Auth::user();

        if ($user instanceof User && in_array($mode, $roles->allowedModes(), true)) {
            $roles->setMode($user, $mode);
        }
    }

    public function render(): View
    {
        return view('livewire.profile.role-mode-switcher');
    }
}
