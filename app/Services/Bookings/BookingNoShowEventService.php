<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;
use App\Models\BookingNoShowEvent;
use Illuminate\Support\Collection;

class BookingNoShowEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingNoShow $noShow, string $eventKey, array $context = []): BookingNoShowEvent
    {
        return $noShow->events()->create([
            'booking_id' => $noShow->booking_id,
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
     * @return Collection<int, BookingNoShowEvent>
     */
    public function getTimeline(BookingNoShow $noShow): Collection
    {
        return $noShow->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
