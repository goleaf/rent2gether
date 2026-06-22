<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationDigest;
use App\Models\NotificationDigestItem;
use App\Models\User;
use Carbon\CarbonInterface;

class NotificationDigestService
{
    public function __construct(private readonly NotificationNumberService $numbers) {}

    public function createDigest(User $user, string $digestType, CarbonInterface $from, CarbonInterface $to): NotificationDigest
    {
        return NotificationDigest::query()->create([
            'digest_number' => $this->numbers->generateDigestNumber(),
            'user_id' => $user->id,
            'digest_type' => $digestType,
            'status' => 'created',
            'period_start' => $from,
            'period_end' => $to,
            'notification_count' => 0,
            'urgent_count' => 0,
            'important_count' => 0,
            'title_translation_key' => 'notifications.digest.title',
            'body_translation_key' => 'notifications.digest.body',
            'translation_params_json' => ['count' => 0],
        ]);
    }

    public function addNotification(NotificationDigest $digest, Notification $notification): NotificationDigestItem
    {
        $item = NotificationDigestItem::query()->firstOrCreate([
            'notification_digest_id' => $digest->id,
            'notification_id' => $notification->id,
        ], [
            'sort_order' => $digest->items()->count(),
        ]);

        $digest->update([
            'notification_count' => $digest->items()->count(),
            'urgent_count' => $digest->items()->whereHas('notification', fn ($query) => $query->where('is_urgent', true)->orWhere('is_critical', true))->count(),
            'important_count' => $digest->items()->whereHas('notification', fn ($query) => $query->where('priority', 'high'))->count(),
        ]);

        return $item;
    }

    public function sendDigest(NotificationDigest $digest): NotificationDigest
    {
        $digest->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $digest->refresh();
    }

    public function markRead(User $user, NotificationDigest $digest): NotificationDigest
    {
        if ((int) $digest->user_id !== (int) $user->id) {
            return $digest;
        }

        $digest->update([
            'status' => 'read',
            'read_at' => now(),
        ]);

        return $digest->refresh();
    }
}
