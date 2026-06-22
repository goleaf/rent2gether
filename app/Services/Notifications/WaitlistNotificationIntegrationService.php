<?php

namespace App\Services\Notifications;

use App\Models\User;

class WaitlistNotificationIntegrationService
{
    public function notifyPlaceAvailable(User $user): void
    {
        app(NotificationService::class)->createForUser($user, 'waitlist_place_available', ['priority' => 'high']);
    }
}
