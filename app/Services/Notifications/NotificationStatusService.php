<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationStatusLog;
use App\Models\User;

class NotificationStatusService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(Notification $notification, string $newStatus, ?User $user = null, array $context = []): Notification
    {
        $oldStatus = $notification->status;
        $notification->update(['status' => $newStatus]);

        NotificationStatusLog::query()->create([
            'notification_id' => $notification->id,
            'user_id' => $user?->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => $context['reason_key'] ?? null,
            'note' => $context['note'] ?? null,
            'context_json' => $context,
        ]);

        return $notification->refresh();
    }
}
