<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;

class BookingNoShowStayIntegrationService
{
    public function ensureNoStayCreated(BookingNoShow $noShow): void
    {
        $this->cancelStayIfCreatedByMistake($noShow);
    }

    public function cancelStayIfCreatedByMistake(BookingNoShow $noShow): void
    {
        $noShow->booking?->stay()?->whereNotIn('status', ['cancelled', 'closed'])->update([
            'status' => 'cancelled',
            'ended_at' => now(),
            'closed_at' => now(),
        ]);
    }

    public function recalculateOccupancy(BookingNoShow $noShow): void
    {
        $noShow->events()->create([
            'booking_id' => $noShow->booking_id,
            'event_key' => 'occupancy_recalculated',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => [],
        ]);
    }
}
