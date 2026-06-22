<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationAction;
use App\Models\User;

class NotificationActionService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function createAction(Notification $notification, string $actionType, array $context = []): NotificationAction
    {
        return NotificationAction::query()->firstOrCreate([
            'notification_id' => $notification->id,
            'user_id' => $notification->recipient_user_id,
            'action_type' => $actionType,
        ], [
            'status' => 'available',
            'source_type' => $context['source_type'] ?? $notification->source_type,
            'source_id' => $context['source_id'] ?? $notification->source_id,
        ]);
    }

    public function performAction(User $user, Notification $notification): NotificationAction
    {
        $action = NotificationAction::query()->firstOrCreate([
            'notification_id' => $notification->id,
            'user_id' => $user->id,
            'action_type' => $notification->action_type ?: 'open_booking',
        ], [
            'status' => 'available',
            'source_type' => $notification->source_type,
            'source_id' => $notification->source_id,
        ]);

        if ($notification->expired_at || $notification->status === 'expired') {
            return $this->markExpired($action);
        }

        $action->update([
            'status' => 'performed',
            'performed_at' => now(),
            'result_message_key' => 'notifications.messages.action_completed',
        ]);

        $notification->forceFill([
            'status' => 'action_taken',
            'action_taken_at' => now(),
        ])->save();

        return $action->refresh();
    }

    public function markExpired(NotificationAction $action): NotificationAction
    {
        $action->update(['status' => 'expired']);

        return $action->refresh();
    }

    public function getActionUrl(Notification $notification): ?string
    {
        return $notification->expired_at ? null : $notification->action_url;
    }
}
