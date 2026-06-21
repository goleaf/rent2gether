<?php

namespace App\Services\Stays;

use App\Models\BookingStay;
use App\Models\BookingStayEvent;
use Illuminate\Support\Collection;

class StayEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingStay $stay, string $eventKey, array $context = []): BookingStayEvent
    {
        return $stay->events()->create([
            'booking_id' => $stay->booking_id,
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
     * @return Collection<int, BookingStayEvent>
     */
    public function getTimeline(BookingStay $stay): Collection
    {
        return $stay->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
