<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class BookingNoShowNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function notifyGuestNoShowReported(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('guest');

        if ($noShow->guest instanceof User) {
            $this->notifications->create($noShow->guest, 'booking_no_show_reported', data: ['booking_id' => $noShow->booking_id, 'no_show_id' => $noShow->id], titleKey: 'no_show.notifications.reported.title', bodyKey: 'no_show.notifications.reported.body');
        }
    }

    public function notifyGuestResponseRequired(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('guest');

        if ($noShow->guest instanceof User) {
            $this->notifications->create($noShow->guest, 'booking_no_show_response_required', data: ['booking_id' => $noShow->booking_id, 'no_show_id' => $noShow->id], titleKey: 'no_show.notifications.response_required.title', bodyKey: 'no_show.notifications.response_required.body');
        }
    }

    public function notifyGuestFinalWarning(BookingNoShow $noShow): void
    {
        $this->notifyGuestResponseRequired($noShow);
    }

    public function notifyHostGuestResponded(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('host');

        if ($noShow->host instanceof User) {
            $this->notifications->create($noShow->host, 'booking_no_show_guest_responded', data: ['booking_id' => $noShow->booking_id, 'no_show_id' => $noShow->id], titleKey: 'no_show.notifications.guest_responded.title', bodyKey: 'no_show.notifications.guest_responded.body');
        }
    }

    public function notifyHostNoShowConfirmed(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('host');

        if ($noShow->host instanceof User) {
            $this->notifications->create($noShow->host, 'booking_no_show_confirmed_host', data: ['booking_id' => $noShow->booking_id, 'no_show_id' => $noShow->id], titleKey: 'no_show.notifications.confirmed.title', bodyKey: 'no_show.notifications.confirmed.body');
        }
    }

    public function notifyGuestNoShowConfirmed(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('guest');

        if ($noShow->guest instanceof User) {
            $this->notifications->create($noShow->guest, 'booking_no_show_confirmed_guest', data: ['booking_id' => $noShow->booking_id, 'no_show_id' => $noShow->id], titleKey: 'no_show.notifications.confirmed.title', bodyKey: 'no_show.notifications.confirmed.body');
        }
    }

    public function notifyGuestNoShowRejected(BookingNoShow $noShow): void
    {
        $noShow->loadMissing('guest');

        if ($noShow->guest instanceof User) {
            $this->notifications->create($noShow->guest, 'booking_no_show_rejected', data: ['booking_id' => $noShow->booking_id, 'no_show_id' => $noShow->id], titleKey: 'no_show.notifications.rejected.title', bodyKey: 'no_show.notifications.rejected.body');
        }
    }

    public function notifyRefundCreated(BookingNoShow $noShow): void
    {
        $this->notifyGuestNoShowConfirmed($noShow);
    }

    public function notifyDatesReleased(BookingNoShow $noShow): void
    {
        $this->notifyHostNoShowConfirmed($noShow);
    }
}
