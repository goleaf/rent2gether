<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class HostUnresponsiveNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function notifyHostUrgent(BookingHostUnresponsiveCase $case): void
    {
        $case->loadMissing('host');

        if ($case->host instanceof User) {
            $this->notifications->create($case->host, 'host_unresponsive_urgent', data: ['booking_id' => $case->booking_id, 'host_unresponsive_case_id' => $case->id], titleKey: 'host_unresponsive.notifications.urgent_host.title', bodyKey: 'host_unresponsive.notifications.urgent_host.body');
        }
    }

    public function notifyRepresentativeUrgent(BookingHostUnresponsiveCase $case): void
    {
        $case->loadMissing('hostRepresentative.representativeUser');
        $representativeUser = $case->hostRepresentative?->representativeUser;

        if ($representativeUser instanceof User) {
            $this->notifications->create($representativeUser, 'host_unresponsive_representative_urgent', data: ['booking_id' => $case->booking_id, 'host_unresponsive_case_id' => $case->id], titleKey: 'host_unresponsive.notifications.urgent_representative.title', bodyKey: 'host_unresponsive.notifications.urgent_representative.body');
        }
    }

    public function notifyGuestHostContactAttempted(BookingHostUnresponsiveCase $case): void
    {
        $case->loadMissing('guest');

        if ($case->guest instanceof User) {
            $this->notifications->create($case->guest, 'host_unresponsive_contact_attempted', data: ['booking_id' => $case->booking_id, 'host_unresponsive_case_id' => $case->id], titleKey: 'host_unresponsive.notifications.contact_attempted.title', bodyKey: 'host_unresponsive.notifications.contact_attempted.body');
        }
    }

    public function notifyGuestHostResponded(BookingHostUnresponsiveCase $case): void
    {
        $case->loadMissing('guest');

        if ($case->guest instanceof User) {
            $this->notifications->create($case->guest, 'host_unresponsive_host_responded', data: ['booking_id' => $case->booking_id, 'host_unresponsive_case_id' => $case->id], titleKey: 'host_unresponsive.notifications.host_responded.title', bodyKey: 'host_unresponsive.notifications.host_responded.body');
        }
    }

    public function notifyHostGuestWaiting(BookingHostUnresponsiveCase $case): void
    {
        $case->loadMissing('host');

        if ($case->host instanceof User) {
            $this->notifications->create($case->host, 'host_unresponsive_guest_waiting', data: ['booking_id' => $case->booking_id, 'host_unresponsive_case_id' => $case->id], titleKey: 'host_unresponsive.notifications.guest_waiting.title', bodyKey: 'host_unresponsive.notifications.guest_waiting.body');
        }
    }

    public function notifyGuestDeadlineExpired(BookingHostUnresponsiveCase $case): void
    {
        $case->loadMissing('guest');

        if ($case->guest instanceof User) {
            $this->notifications->create($case->guest, 'host_unresponsive_deadline_expired', data: ['booking_id' => $case->booking_id, 'host_unresponsive_case_id' => $case->id], titleKey: 'host_unresponsive.notifications.deadline_expired.title', bodyKey: 'host_unresponsive.notifications.deadline_expired.body');
        }
    }

    public function notifyGuestCancellationAvailable(BookingHostUnresponsiveCase $case): void
    {
        $this->notifyGuestDeadlineExpired($case);
    }

    public function notifyGuestRelocationAvailable(BookingHostUnresponsiveCase $case): void
    {
        $this->notifyGuestDeadlineExpired($case);
    }

    public function notifyCaseResolved(BookingHostUnresponsiveCase $case): void
    {
        $this->notifyGuestHostResponded($case);
    }
}
