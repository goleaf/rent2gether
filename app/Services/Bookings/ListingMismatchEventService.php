<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchEvent;
use App\Models\BookingListingMismatchReport;
use Illuminate\Support\Collection;

class ListingMismatchEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingListingMismatchReport $report, string $eventKey, array $context = []): BookingListingMismatchEvent
    {
        return $report->events()->create([
            'booking_id' => $report->booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @return Collection<int, BookingListingMismatchEvent>
     */
    public function getTimeline(BookingListingMismatchReport $report): Collection
    {
        return $report->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
