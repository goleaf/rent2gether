<?php

namespace App\Services\Bookings;

use App\Models\BookingRelocation;

class BookingRelocationDepositIntegrationService
{
    /**
     * @return array<string, mixed>
     */
    public function calculateDepositImpact(BookingRelocation $relocation): array
    {
        return [
            'additional_deposit_amount' => (float) $relocation->additional_deposit_amount,
            'old_place_review_required' => true,
        ];
    }

    public function updateDepositContext(BookingRelocation $relocation): void
    {
        $relocation->events()->create([
            'original_booking_id' => $relocation->original_booking_id,
            'new_booking_id' => $relocation->new_booking_id,
            'event_key' => 'relocation_scheduled',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => ['deposit_context_updated' => true],
        ]);
    }

    public function startOldPlaceDepositReviewIfNeeded(BookingRelocation $relocation): mixed
    {
        $this->updateDepositContext($relocation);

        return null;
    }
}
