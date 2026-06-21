<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingLifecycleEvent;
use Illuminate\Support\Collection;

class BookingLifecycleEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(Booking $booking, string $eventKey, array $context = []): BookingLifecycleEvent
    {
        return $booking->lifecycleEvents()->create([
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context['context_json'] ?? $this->eventContext($context),
        ]);
    }

    /**
     * @return Collection<int, BookingLifecycleEvent>
     */
    public function getTimeline(Booking $booking): Collection
    {
        return $booking->lifecycleEvents()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function eventContext(array $context): array
    {
        return collect($context)
            ->except(['event_type', 'source_type', 'source_id', 'user_id', 'occurred_at', 'context_json'])
            ->all();
    }
}
