<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutEvent;
use Illuminate\Support\Collection;

class BookingCheckOutEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingCheckOut $checkOut, string $eventKey, array $context = []): BookingCheckOutEvent
    {
        return BookingCheckOutEvent::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $checkOut->booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @return Collection<int, BookingCheckOutEvent>
     */
    public function getTimeline(BookingCheckOut $checkOut): Collection
    {
        return $checkOut->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
