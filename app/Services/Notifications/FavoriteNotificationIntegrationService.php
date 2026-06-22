<?php

namespace App\Services\Notifications;

use App\Models\User;

class FavoriteNotificationIntegrationService
{
    public function notifyFavoriteAvailable(User $user): void
    {
        app(NotificationService::class)->createForUser($user, 'favorite_available');
    }

    public function notifyFavoritePriceDropped(User $user): void
    {
        app(NotificationService::class)->createForUser($user, 'favorite_price_dropped');
    }
}
