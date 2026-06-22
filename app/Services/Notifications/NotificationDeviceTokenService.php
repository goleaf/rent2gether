<?php

namespace App\Services\Notifications;

use App\Models\NotificationDeviceToken;
use App\Models\User;

class NotificationDeviceTokenService
{
    public function registerFutureToken(User $user, string $platform, string $tokenHash, ?string $deviceName = null): NotificationDeviceToken
    {
        return NotificationDeviceToken::query()->updateOrCreate([
            'user_id' => $user->id,
            'platform' => $platform,
            'token_hash' => $tokenHash,
        ], [
            'device_name' => $deviceName,
            'active' => true,
            'last_used_at' => now(),
        ]);
    }
}
