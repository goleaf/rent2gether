<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use Illuminate\Support\Collection;

class BookingExtensionTimelineService
{
    /**
     * @return Collection<int, array{key:string,date:string|null}>
     */
    public function buildTimelineDates(BookingExtension $extension): Collection
    {
        return collect([
            ['key' => 'new_checkout', 'date' => $extension->new_check_out_date?->toDateString()],
            ['key' => 'payment_deadline', 'date' => $extension->payment_deadline_at?->toDateTimeString()],
            ['key' => 'hold_expires', 'date' => $extension->hold_expires_at?->toDateTimeString()],
        ]);
    }

    /**
     * @return Collection<int, array{key:string,date:string|null}>
     */
    public function rescheduleBookingTimeline(BookingExtension $extension): Collection
    {
        return $this->buildTimelineDates($extension);
    }

    public function cancelOldCheckoutTimeline(BookingExtension $extension): int
    {
        return 0;
    }

    /**
     * @return Collection<int, array{key:string,date:string|null}>
     */
    public function rescheduleNotificationEvents(BookingExtension $extension): Collection
    {
        return $this->buildTimelineDates($extension);
    }
}
