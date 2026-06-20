<?php

namespace App\Policies;

use App\Models\CoLivingProfile;
use App\Models\User;

class CoLivingProfilePolicy
{
    public function view(User $user, CoLivingProfile $profile): bool
    {
        return (int) $profile->user_id === (int) $user->id;
    }

    public function update(User $user, CoLivingProfile $profile): bool
    {
        return (int) $profile->user_id === (int) $user->id;
    }

    public function delete(User $user, CoLivingProfile $profile): bool
    {
        return (int) $profile->user_id === (int) $user->id;
    }
}
