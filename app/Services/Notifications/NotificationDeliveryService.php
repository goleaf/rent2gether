<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationDelivery;
use Illuminate\Support\Collection;

class NotificationDeliveryService
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences,
        private readonly NotificationQuietHoursService $quietHours,
    ) {}

    /**
     * @return Collection<int, NotificationDelivery>
     */
    public function createDeliveries(Notification $notification): Collection
    {
        $deliveries = collect();

        $inApp = $this->createInAppDelivery($notification);
        if ($inApp instanceof NotificationDelivery) {
            $deliveries->push($inApp);
        }

        foreach (['email', 'sms_future', 'push_future'] as $channel) {
            $delivery = match ($channel) {
                'email' => $this->createEmailDelivery($notification),
                'sms_future' => $this->createSmsFutureDelivery($notification),
                default => $this->createPushFutureDelivery($notification),
            };

            if ($delivery instanceof NotificationDelivery) {
                $deliveries->push($delivery);
            }
        }

        return $deliveries;
    }

    public function createInAppDelivery(Notification $notification): ?NotificationDelivery
    {
        $recipient = $notification->recipient;

        if (! $recipient) {
            return null;
        }

        $enabled = $this->preferences->isChannelEnabled($recipient, $notification->notification_category, 'in_app', $notification->priority)
            || $notification->is_critical
            || $notification->priority === 'critical';

        if (! $enabled) {
            return $this->delivery($notification, 'in_app', 'skipped_by_preferences');
        }

        if ($this->quietHours->shouldDelayForQuietHours($recipient, $notification)) {
            $notification->forceFill([
                'status' => 'scheduled',
                'scheduled_at' => $this->quietHours->nextAllowedTime($recipient),
            ])->save();

            return $this->delivery($notification, 'in_app', 'skipped_by_quiet_hours');
        }

        return $this->delivery($notification, 'in_app', 'ready');
    }

    public function createEmailDelivery(Notification $notification): ?NotificationDelivery
    {
        return $this->createOptionalDelivery($notification, 'email');
    }

    public function createSmsFutureDelivery(Notification $notification): ?NotificationDelivery
    {
        return $this->createOptionalDelivery($notification, 'sms_future');
    }

    public function createPushFutureDelivery(Notification $notification): ?NotificationDelivery
    {
        return $this->createOptionalDelivery($notification, 'push_future');
    }

    public function sendDelivery(NotificationDelivery $delivery): NotificationDelivery
    {
        if (in_array($delivery->channel, ['sms_future', 'push_future', 'phone_call_future'], true)) {
            return $delivery;
        }

        $delivery->update([
            'status' => 'sent',
            'sent_at' => now(),
            'attempt_count' => $delivery->attempt_count + 1,
        ]);

        return $delivery->refresh();
    }

    public function markDeliveredFuture(NotificationDelivery $delivery): NotificationDelivery
    {
        $delivery->update([
            'status' => 'delivered_future',
            'delivered_at' => now(),
        ]);

        return $delivery->refresh();
    }

    public function markFailed(NotificationDelivery $delivery, string $reason): NotificationDelivery
    {
        $delivery->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);

        return $delivery->refresh();
    }

    private function createOptionalDelivery(Notification $notification, string $channel): ?NotificationDelivery
    {
        $recipient = $notification->recipient;

        if (! $recipient || ! $this->preferences->isChannelEnabled($recipient, $notification->notification_category, $channel, $notification->priority)) {
            return null;
        }

        return $this->delivery($notification, $channel, 'ready');
    }

    private function delivery(Notification $notification, string $channel, string $status): NotificationDelivery
    {
        return NotificationDelivery::query()->create([
            'notification_id' => $notification->id,
            'recipient_user_id' => $notification->recipient_user_id,
            'channel' => $channel,
            'status' => $status,
            'scheduled_at' => $status === 'skipped_by_quiet_hours' ? $notification->scheduled_at : now(),
            'attempt_count' => 0,
        ]);
    }
}
