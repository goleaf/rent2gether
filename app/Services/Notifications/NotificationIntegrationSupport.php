<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use App\Models\User;

trait NotificationIntegrationSupport
{
    protected function notifyGuest(?Booking $booking, string $templateKey, array $context = []): void
    {
        $booking?->loadMissing('guest');

        if ($booking?->guest instanceof User) {
            app(NotificationService::class)->createForUser($booking->guest, $templateKey, ['booking' => $booking, 'recipient_type' => 'guest'] + $context);
        }
    }

    protected function notifyHost(?Booking $booking, string $templateKey, array $context = []): void
    {
        $booking?->loadMissing('host');

        if ($booking?->host instanceof User) {
            app(NotificationService::class)->createForUser($booking->host, $templateKey, ['booking' => $booking, 'recipient_type' => 'host'] + $context);
        }
    }

    protected function bookingFrom(mixed $model): ?Booking
    {
        if ($model instanceof Booking) {
            return $model;
        }

        if (($model->booking ?? null) instanceof Booking) {
            return $model->booking;
        }

        if (($model->booking_id ?? null) !== null) {
            return Booking::query()->find($model->booking_id);
        }

        return null;
    }
}
