<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;

class NotificationDeduplicationService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function buildDeduplicationKey(string $templateKey, User $recipient, array $context = []): string
    {
        $booking = $context['booking'] ?? null;
        $bookingId = $booking instanceof Booking ? $booking->id : ($context['booking_id'] ?? 'global');

        return 'notification:'.$templateKey.':'.$recipient->id.':booking:'.$bookingId;
    }

    public function findRecentDuplicate(string $deduplicationKey, int $minutes = 10): ?Notification
    {
        return Notification::query()
            ->where('deduplication_key', $deduplicationKey)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->latest('created_at')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function mergeIntoExisting(Notification $existing, array $context = []): Notification
    {
        $params = $existing->translationParams();
        $params['count'] = ((int) ($params['count'] ?? 1)) + 1;

        $existing->forceFill([
            'translation_params_json' => $params,
            'status' => $existing->is_read ? 'read' : 'created',
            'read_at' => null,
            'is_read' => false,
        ])->save();

        return $existing->refresh();
    }

    public function shouldCreateNew(string $deduplicationKey, int $minutes = 10): bool
    {
        return ! $this->findRecentDuplicate($deduplicationKey, $minutes) instanceof Notification;
    }
}
