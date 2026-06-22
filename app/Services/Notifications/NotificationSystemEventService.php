<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationSystemEvent;

class NotificationSystemEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $eventKey, array $context = []): NotificationSystemEvent
    {
        $notification = $context['notification'] ?? null;

        $storedContext = collect($context)
            ->except('notification')
            ->all();

        return NotificationSystemEvent::query()->create([
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'notification_id' => $notification instanceof Notification ? $notification->id : ($context['notification_id'] ?? null),
            'notification_event_id' => $context['notification_event_id'] ?? $notification?->notification_event_id,
            'notification_delivery_id' => $context['notification_delivery_id'] ?? null,
            'notification_reminder_id' => $context['notification_reminder_id'] ?? null,
            'source_type' => $context['source_type'] ?? $notification?->source_type,
            'source_id' => $context['source_id'] ?? $notification?->source_id,
            'user_id' => $context['user_id'] ?? $notification?->recipient_user_id,
            'occurred_at' => now(),
            'context_json' => $storedContext,
        ]);
    }
}
