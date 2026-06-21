<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;
use App\Models\BookingCancellationEvent;
use Illuminate\Support\Collection;

class BookingCancellationEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingCancellation $cancellation, string $eventKey, array $context = []): BookingCancellationEvent
    {
        return $cancellation->events()->create([
            'booking_id' => $cancellation->booking_id,
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
     * @return Collection<int, BookingCancellationEvent>
     */
    public function getTimeline(BookingCancellation $cancellation): Collection
    {
        return $cancellation->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
