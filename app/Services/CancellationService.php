<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Models\Booking;

class CancellationService
{
    /**
     * @return array{refund_amount: float, penalty_amount: float, deposit_refunded: bool, explanation: string}
     */
    public function calculateRefund(Booking $booking): array
    {
        $total = (float) $booking->total;
        $deposit = (float) $booking->deposit;
        $cleaningFee = (float) $booking->cleaning_fee;
        $serviceFee = (float) $booking->service_fee;
        $stayAmount = (float) $booking->subtotal - (float) $booking->discount_amount;

        if ($booking->canCancelFree()) {
            return [
                'refund_amount' => $total,
                'penalty_amount' => 0,
                'deposit_refunded' => true,
                'explanation' => 'Full refund — free cancellation period.',
            ];
        }

        $policy = $booking->cancellation_policy;

        return match ($policy) {
            CancellationPolicy::Flexible => [
                'refund_amount' => round($stayAmount * 0.5 + $deposit + $cleaningFee, 2),
                'penalty_amount' => round($stayAmount * 0.5 + $serviceFee, 2),
                'deposit_refunded' => true,
                'explanation' => '50% stay refund. Deposit and cleaning fee refunded. Service fee retained.',
            ],
            CancellationPolicy::Moderate => [
                'refund_amount' => round($deposit + $cleaningFee, 2),
                'penalty_amount' => round($stayAmount + $serviceFee, 2),
                'deposit_refunded' => true,
                'explanation' => 'Stay amount not refunded. Deposit and cleaning fee refunded.',
            ],
            CancellationPolicy::Strict => [
                'refund_amount' => round($deposit, 2),
                'penalty_amount' => round($stayAmount + $cleaningFee + $serviceFee, 2),
                'deposit_refunded' => true,
                'explanation' => 'Only deposit refunded.',
            ],
            CancellationPolicy::NonRefundable => [
                'refund_amount' => 0,
                'penalty_amount' => $total,
                'deposit_refunded' => false,
                'explanation' => 'Non-refundable booking.',
            ],
            default => [
                'refund_amount' => round($deposit, 2),
                'penalty_amount' => round($total - $deposit, 2),
                'deposit_refunded' => true,
                'explanation' => 'Deposit refunded.',
            ],
        };
    }

    public function cancelByGuest(Booking $booking, ?string $reason = null): bool
    {
        if (! $booking->isCancellable()) {
            return false;
        }

        $refund = $this->calculateRefund($booking);

        $booking->update([
            'status' => BookingStatus::CancelledByGuest->value,
            'cancel_reason' => $reason,
            'cancelled_by' => 'guest',
            'refund_amount' => $refund['refund_amount'],
            'payment_status' => $refund['refund_amount'] > 0 ? PaymentStatus::RefundedPartial->value : PaymentStatus::Paid->value,
        ]);

        return true;
    }

    public function cancelByHost(Booking $booking, ?string $reason = null): bool
    {
        if (! $booking->isCancellable()) {
            return false;
        }

        $booking->update([
            'status' => BookingStatus::CancelledByHost->value,
            'cancel_reason' => $reason,
            'cancelled_by' => 'host',
            'refund_amount' => $booking->total,
            'payment_status' => PaymentStatus::RefundedFull->value,
        ]);

        return true;
    }
}
