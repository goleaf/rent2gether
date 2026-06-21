<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveCalendarIntegrationService
{
    public function releaseLocksIfCancelledBeforeCheckIn(BookingHostUnresponsiveCase $case): void
    {
        $case->booking?->sleepingPlaceDateLocks()
            ->whereIn('status', ['held', 'booked', 'pending_payment', 'pending_host_confirmation'])
            ->update(['status' => 'released']);

        $this->syncCalendarAfterResolution($case->fresh());
    }

    public function keepPlaceBlockedIfAccessProblem(BookingHostUnresponsiveCase $case): void
    {
        app(HostUnresponsiveEventService::class)->record($case, 'case_closed', ['calendar_release_status' => 'kept_blocked']);
    }

    public function syncCalendarAfterResolution(BookingHostUnresponsiveCase $case): void
    {
        app(HostUnresponsiveEventService::class)->record($case, 'case_closed', ['calendar_synced' => true]);
    }
}
