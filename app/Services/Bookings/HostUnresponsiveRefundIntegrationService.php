<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingRefund;

class HostUnresponsiveRefundIntegrationService
{
    public function createRefundIfCancellationConfirmed(BookingHostUnresponsiveCase $case): ?BookingRefund
    {
        if ($case->booking_refund_id) {
            return $case->bookingRefund()->first();
        }

        $amounts = $this->calculateGuestFriendlyRefund($case);

        if ((float) $amounts['total_refund_amount'] <= 0.0) {
            return null;
        }

        $refund = app(BookingRefundService::class)->createRefund($case->booking()->firstOrFail(), (float) $amounts['total_refund_amount'], 'host_unresponsive_refund', [
            'reason_key' => 'host_unresponsive',
            'source_type' => 'host_unresponsive',
            'source_id' => $case->id,
            'comment' => $case->guest_comment,
        ]);

        $case->forceFill([
            'booking_refund_id' => $refund->id,
            'refund_status' => 'pending',
            'refund_amount' => $refund->amount,
        ])->save();

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'refund_created', ['refund_id' => $refund->id]);

        return $refund;
    }

    /**
     * @return array<string, float|string>
     */
    public function calculateGuestFriendlyRefund(BookingHostUnresponsiveCase $case): array
    {
        $case->loadMissing('booking');
        $booking = $case->booking;
        $total = (float) ($booking?->total_payable ?: $booking?->total_amount ?: $booking?->total ?: 0);

        return [
            'total_refund_amount' => $total,
            'deposit_refund_amount' => (float) ($booking?->deposit_amount ?: 0),
            'cleaning_fee_refund_amount' => (float) ($booking?->cleaning_fee_amount ?: 0),
            'service_fee_refund_amount' => (float) ($booking?->service_fee_amount ?: 0),
            'currency' => $case->currency ?: $booking?->currency ?: 'EUR',
        ];
    }
}
