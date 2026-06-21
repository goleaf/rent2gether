<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;

class BookingRelocationReviewIntegrationService
{
    public function __construct(
        private readonly BookingRelocationEventService $events,
    ) {}

    public function updateReviewContext(BookingRelocation $relocation): void
    {
        $this->events->record($relocation, 'relocation_scheduled', ['review_context' => 'multi_place']);
    }

    public function markStayAsMultiPlace(BookingRelocation $relocation): void
    {
        $relocation->bookingStay?->forceFill([
            'host_note' => trim((string) $relocation->bookingStay?->host_note.' booking_relocations.review.multi_place'),
        ])->save();
    }
}
