<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationPreview;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class BookingCancellationNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function notifyGuestCancellationPreviewCreated(BookingCancellationPreview $preview): void
    {
        $preview->loadMissing('guest');

        if ($preview->guest instanceof User) {
            $this->notifications->create($preview->guest, 'booking_cancellation_preview_created', data: ['booking_id' => $preview->booking_id, 'preview_id' => $preview->id], titleKey: 'cancellations.notifications.preview_created.title', bodyKey: 'cancellations.notifications.preview_created.body');
        }
    }

    public function notifyHostBookingCancelled(BookingCancellation $cancellation): void
    {
        $cancellation->loadMissing('host');

        if ($cancellation->host instanceof User) {
            $this->notifications->create($cancellation->host, 'booking_cancelled_host_notice', data: ['booking_id' => $cancellation->booking_id, 'cancellation_id' => $cancellation->id], titleKey: 'cancellations.notifications.host_cancelled.title', bodyKey: 'cancellations.notifications.host_cancelled.body');
        }
    }

    public function notifyGuestBookingCancelled(BookingCancellation $cancellation): void
    {
        $cancellation->loadMissing('guest');

        if ($cancellation->guest instanceof User) {
            $this->notifications->create($cancellation->guest, 'booking_cancelled_guest_notice', data: ['booking_id' => $cancellation->booking_id, 'cancellation_id' => $cancellation->id], titleKey: 'cancellations.notifications.guest_cancelled.title', bodyKey: 'cancellations.notifications.guest_cancelled.body');
        }
    }

    public function notifyGuestRefundCreated(BookingCancellation $cancellation): void
    {
        $this->notifyGuestBookingCancelled($cancellation);
    }

    public function notifyGuestRefundCompleted(BookingCancellation $cancellation): void
    {
        $this->notifyGuestBookingCancelled($cancellation);
    }

    public function notifyHostPayoutAdjusted(BookingCancellation $cancellation): void
    {
        $this->notifyHostBookingCancelled($cancellation);
    }

    public function notifyWaitlistPlaceAvailable(BookingCancellation $cancellation): void
    {
        $cancellation->events()->create([
            'booking_id' => $cancellation->booking_id,
            'event_key' => 'waitlist_notified',
            'event_type' => 'system',
            'occurred_at' => now(),
        ]);
    }
}
