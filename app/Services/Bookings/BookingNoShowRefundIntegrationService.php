<?php

namespace App\Services\Bookings;

use App\Models\BookingNoShow;
use App\Models\BookingRefund;

class BookingNoShowRefundIntegrationService
{
    public function createRefundIfNeeded(BookingNoShow $noShow): ?BookingRefund
    {
        if ((float) $noShow->refund_amount <= 0.0) {
            $noShow->forceFill(['refund_or_penalty_status' => 'penalty_applied'])->save();

            return null;
        }

        $noShow->loadMissing('booking');

        $refund = app(BookingRefundService::class)->createRefund($noShow->booking, (float) $noShow->refund_amount, 'no_show_refund', [
            'reason_key' => 'no_show_related',
            'source_type' => 'booking_no_show',
            'source_id' => $noShow->id,
        ]);

        $noShow->forceFill([
            'booking_refund_id' => $refund->id,
            'refund_or_penalty_status' => 'refund_created',
        ])->save();

        return $refund;
    }

    public function syncRefundStatus(BookingNoShow $noShow): void
    {
        if ($noShow->bookingRefund) {
            $noShow->forceFill(['refund_or_penalty_status' => 'refund_created'])->save();
        }
    }
}
