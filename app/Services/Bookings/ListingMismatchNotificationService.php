<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchReport;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class ListingMismatchNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function notifyHostMismatchReported(BookingListingMismatchReport $report): void
    {
        $report->loadMissing('host');

        if ($report->host instanceof User) {
            $this->notifications->create($report->host, 'listing_mismatch_reported', data: ['booking_id' => $report->booking_id, 'mismatch_report_id' => $report->id], titleKey: 'listing_mismatch.notifications.host_reported.title', bodyKey: 'listing_mismatch.notifications.host_reported.body');
        }
    }

    public function notifyGuestHostResponded(BookingListingMismatchReport $report): void
    {
        $this->notifyGuest($report, 'listing_mismatch_host_responded', 'host_responded');
    }

    public function notifyGuestResolutionOffered(BookingListingMismatchReport $report): void
    {
        $this->notifyGuest($report, 'listing_mismatch_resolution_offered', 'resolution_offered');
    }

    public function notifyHostGuestAcceptedResolution(BookingListingMismatchReport $report): void
    {
        $this->notifyHost($report, 'listing_mismatch_resolution_accepted', 'resolution_accepted');
    }

    public function notifyHostGuestRejectedResolution(BookingListingMismatchReport $report): void
    {
        $this->notifyHost($report, 'listing_mismatch_resolution_rejected', 'resolution_rejected');
    }

    public function notifyRefundCreated(BookingListingMismatchReport $report): void
    {
        $this->notifyGuest($report, 'listing_mismatch_refund_created', 'refund_created');
    }

    public function notifyRelocationCreated(BookingListingMismatchReport $report): void
    {
        $this->notifyGuest($report, 'listing_mismatch_relocation_created', 'relocation_created');
    }

    public function notifyCancellationCreated(BookingListingMismatchReport $report): void
    {
        $this->notifyGuest($report, 'listing_mismatch_cancellation_created', 'cancellation_created');
    }

    public function notifyCaseClosed(BookingListingMismatchReport $report): void
    {
        $this->notifyGuest($report, 'listing_mismatch_closed', 'case_closed');
        $this->notifyHost($report, 'listing_mismatch_closed', 'case_closed');
    }

    private function notifyGuest(BookingListingMismatchReport $report, string $type, string $key): void
    {
        $report->loadMissing('guest');

        if ($report->guest instanceof User) {
            $this->notifications->create($report->guest, $type, data: ['booking_id' => $report->booking_id, 'mismatch_report_id' => $report->id], titleKey: 'listing_mismatch.notifications.'.$key.'.title', bodyKey: 'listing_mismatch.notifications.'.$key.'.body');
        }
    }

    private function notifyHost(BookingListingMismatchReport $report, string $type, string $key): void
    {
        $report->loadMissing('host');

        if ($report->host instanceof User) {
            $this->notifications->create($report->host, $type, data: ['booking_id' => $report->booking_id, 'mismatch_report_id' => $report->id], titleKey: 'listing_mismatch.notifications.'.$key.'.title', bodyKey: 'listing_mismatch.notifications.'.$key.'.body');
        }
    }
}
