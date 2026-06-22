<?php

namespace App\Services\Notifications;

use App\Models\NotificationDelivery;
use App\Models\NotificationDeliveryAttempt;

class NotificationDeliveryAttemptService
{
    public function recordAttempt(NotificationDelivery $delivery, string $status, ?string $failureReason = null): NotificationDeliveryAttempt
    {
        return NotificationDeliveryAttempt::query()->create([
            'notification_delivery_id' => $delivery->id,
            'notification_id' => $delivery->notification_id,
            'channel' => $delivery->channel,
            'attempt_number' => $delivery->attempt_count + 1,
            'status' => $status,
            'attempted_at' => now(),
            'provider' => $delivery->provider,
            'failure_reason' => $failureReason,
        ]);
    }
}
