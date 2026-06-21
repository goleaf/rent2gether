<?php

namespace App\Services\Bookings;

use App\Models\BookingCancellation;

class BookingCancellationPayoutService
{
    public function calculateHostPayoutAfterCancellation(BookingCancellation $cancellation): float
    {
        return max(0.0, (float) $cancellation->penalty_amount - (float) $cancellation->service_fee_amount);
    }

    public function applyHostPayoutAdjustment(BookingCancellation $cancellation): void
    {
        $cancellation->forceFill([
            'host_payout_adjustment_amount' => $this->calculateHostPayoutAfterCancellation($cancellation),
        ])->save();
    }

    public function markPayoutCancelledIfNeeded(BookingCancellation $cancellation): void
    {
        if ((float) $cancellation->total_refund_amount > 0.0) {
            $this->applyHostPayoutAdjustment($cancellation);
        }
    }
}
