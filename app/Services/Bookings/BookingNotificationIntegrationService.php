<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\Notification;
use Illuminate\Support\Str;

class BookingNotificationIntegrationService
{
    public function notifyBookingCreated(Booking $booking): void
    {
        $this->create($booking, (int) $booking->guest_user_id, 'booking_created', 'bookings.notifications.created.title', 'bookings.notifications.created.body');
        $this->create($booking, (int) $booking->host_user_id, 'booking_created_host', 'bookings.notifications.host_created.title', 'bookings.notifications.host_created.body');
    }

    public function notifyHostNewRequest(Booking $booking): void
    {
        $this->create($booking, (int) $booking->host_user_id, 'booking_host_request', 'bookings.notifications.host_request.title', 'bookings.notifications.host_request.body');
    }

    public function notifyGuestConfirmed(Booking $booking): void
    {
        $this->create($booking, (int) $booking->guest_user_id, 'booking_confirmed', 'bookings.notifications.confirmed.title', 'bookings.notifications.confirmed.body');
    }

    public function notifyGuestRejected(Booking $booking): void
    {
        $this->create($booking, (int) $booking->guest_user_id, 'booking_rejected', 'bookings.notifications.rejected.title', 'bookings.notifications.rejected.body');
    }

    public function notifyPaymentRequired(Booking $booking): void
    {
        $this->create($booking, (int) $booking->guest_user_id, 'booking_payment_required', 'bookings.notifications.payment_required.title', 'bookings.notifications.payment_required.body');
    }

    public function notifyReadyForCheckIn(Booking $booking): void
    {
        $this->create($booking, (int) $booking->guest_user_id, 'booking_ready_for_check_in', 'bookings.notifications.ready_for_check_in.title', 'bookings.notifications.ready_for_check_in.body');
    }

    public function notifyCheckOutSoon(Booking $booking): void
    {
        $this->create($booking, (int) $booking->guest_user_id, 'booking_check_out_soon', 'bookings.notifications.check_out_soon.title', 'bookings.notifications.check_out_soon.body');
    }

    private function create(Booking $booking, int $userId, string $type, string $titleKey, string $bodyKey): void
    {
        if ($userId < 1) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => Booking::class,
            'notifiable_id' => $booking->id,
            'user_id' => $userId,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'data' => [
                'booking_id' => $booking->id,
                'reference' => $booking->booking_number ?: $booking->reference,
                'amount' => (float) $booking->total_payable,
                'currency' => $booking->currency,
            ],
            'title_key' => $titleKey,
            'body_key' => $bodyKey,
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }
}
