<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;

class BookingCancellationHostUnresponsiveIntegrationService
{
    public function createFromHostUnresponsive($case): BookingCancellation
    {
        return app(BookingCancellationService::class)->cancelBooking($case->guest, $case->booking, [
            'cancellation_type' => 'host_unresponsive_related',
            'reason_key' => 'host_unresponsive',
        ]);
    }

    public function applyGuestFriendlyRefund(BookingCancellation $cancellation): void
    {
        $cancellation->forceFill([
            'requires_dispute' => false,
            'total_refund_amount' => (float) $cancellation->accommodation_amount + (float) $cancellation->cleaning_fee_amount + (float) $cancellation->service_fee_amount + (float) $cancellation->deposit_amount,
            'total_non_refundable_amount' => 0,
        ])->save();
    }
}
