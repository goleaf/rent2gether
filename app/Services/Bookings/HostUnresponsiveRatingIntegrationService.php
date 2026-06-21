<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveRatingIntegrationService
{
    public function recordConfirmedHostUnresponsive(BookingHostUnresponsiveCase $case): void
    {
        app(HostUnresponsiveEventService::class)->record($case, 'host_unresponsive_confirmed', ['rating_impact' => true]);
    }

    public function removeRatingImpactIfRejected(BookingHostUnresponsiveCase $case): void
    {
        app(HostUnresponsiveEventService::class)->record($case, 'case_closed', ['rating_impact_removed' => true]);
    }
}
