<?php

namespace App\Services\Notifications;

use App\Models\User;

class SavedSearchNotificationIntegrationService
{
    public function notifyNewResults(User $user): void
    {
        app(NotificationService::class)->createForUser($user, 'saved_search_new_results', ['notification_type' => 'digest']);
    }
}
