<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;

class HostUnresponsiveNoShowIntegrationService
{
    public function blockNoShowWhileActive(BookingHostUnresponsiveCase $case): void
    {
        $case->booking?->noShows()
            ->whereNotIn('status', ['confirmed_no_show', 'rejected_no_show', 'closed', 'cancelled'])
            ->update([
                'host_unresponsive_case_id' => $case->id,
                'decision_key' => 'converted_to_host_unresponsive',
            ]);
    }

    public function rejectPendingNoShowIfHostUnresponsiveConfirmed(BookingHostUnresponsiveCase $case): void
    {
        $case->booking?->noShows()
            ->whereNotIn('status', ['confirmed_no_show', 'rejected_no_show', 'closed', 'cancelled'])
            ->update([
                'status' => 'rejected_no_show',
                'decision_key' => 'converted_to_host_unresponsive',
                'host_unresponsive_case_id' => $case->id,
                'decision_at' => now(),
                'future_support_review_required' => false,
            ]);
    }
}
