<?php

namespace App\Policies;

use App\Models\CoLivingVisibilitySetting;
use App\Models\User;

class CoLivingVisibilitySettingPolicy
{
    public function view(User $user, CoLivingVisibilitySetting $setting): bool
    {
        return (int) $setting->user_id === (int) $user->id;
    }

    public function update(User $user, CoLivingVisibilitySetting $setting): bool
    {
        return (int) $setting->user_id === (int) $user->id;
    }
}
