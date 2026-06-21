<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveEvent;
use Illuminate\Support\Collection;

class HostUnresponsiveEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingHostUnresponsiveCase $case, string $eventKey, array $context = []): HostUnresponsiveEvent
    {
        return $case->events()->create([
            'booking_id' => $case->booking_id,
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
     * @return Collection<int, HostUnresponsiveEvent>
     */
    public function getTimeline(BookingHostUnresponsiveCase $case): Collection
    {
        return $case->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
