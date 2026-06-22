<?php

namespace App\Services\Notifications;

use App\Models\Booking;
use Illuminate\Support\Collection;

class CheckOutNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function scheduleCheckOutReminders(Booking $booking): Collection
    {
        $booking->loadMissing('guest', 'host');

        return collect([$booking->guest, $booking->host])
            ->filter()
            ->map(fn ($user) => app(NotificationReminderService::class)->scheduleReminder($user, 'checkout_soon', now()->addDay(), ['booking' => $booking]));
    }

    public function notifyCheckoutSoon(mixed $checkOut): void
    {
        $this->notifyGuest($this->bookingFrom($checkOut), 'checkout_soon');
    }

    public function notifyCheckoutToday(mixed $checkOut): void
    {
        $this->notifyGuest($this->bookingFrom($checkOut), 'checkout_today');
        $this->notifyHost($this->bookingFrom($checkOut), 'checkout_today');
    }

    public function notifyGuestCheckedOut(mixed $checkOut): void
    {
        $this->notifyHost($this->bookingFrom($checkOut), 'guest_checked_out');
    }

    public function notifyInspectionRequired(mixed $checkOut): void
    {
        $this->notifyHost($this->bookingFrom($checkOut), 'inspection_due');
    }
}
