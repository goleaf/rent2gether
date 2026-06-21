<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;
use App\Models\BookingRelocationEvent;
use Illuminate\Support\Collection;

class BookingRelocationEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingRelocation $relocation, string $eventKey, array $context = []): BookingRelocationEvent
    {
        return BookingRelocationEvent::query()->create([
            'booking_relocation_id' => $relocation->id,
            'original_booking_id' => $relocation->original_booking_id,
            'new_booking_id' => $relocation->new_booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? $relocation->source_type,
            'source_id' => $context['source_id'] ?? $relocation->source_id,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @return Collection<int, BookingRelocationEvent>
     */
    public function getTimeline(BookingRelocation $relocation): Collection
    {
        return $relocation->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
