<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\BookingExtensionEvent;
use Illuminate\Support\Collection;

class BookingExtensionEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(BookingExtension $extension, string $eventKey, array $context = []): BookingExtensionEvent
    {
        return BookingExtensionEvent::query()->create([
            'booking_extension_id' => $extension->id,
            'booking_id' => $extension->booking_id,
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
     * @return Collection<int, BookingExtensionEvent>
     */
    public function getTimeline(BookingExtension $extension): Collection
    {
        return $extension->events()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
