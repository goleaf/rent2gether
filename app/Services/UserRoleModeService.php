<?php

namespace App\Services;

use App\Enums\UserRoleMode;
use App\Models\User;

class UserRoleModeService
{
    public function setMode(User $user, UserRoleMode|string $mode): User
    {
        $mode = $mode instanceof UserRoleMode ? $mode : UserRoleMode::from($mode);

        $user->forceFill([
            'role_mode' => $mode->value,
            'is_guest' => $mode->canGuest(),
            'is_host' => $mode->canHost(),
        ])->save();

        $user->setting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'active_mode' => $mode === UserRoleMode::Host ? 'host' : 'guest',
                'account_role' => $mode === UserRoleMode::GuestHost ? 'both' : $mode->value,
            ],
        );

        return $user->refresh();
    }

    public function canCreateHostObjects(User $user): bool
    {
        return $user->isHost();
    }

    /**
     * @return list<string>
     */
    public function allowedModes(): array
    {
        return UserRoleMode::values();
    }
}
