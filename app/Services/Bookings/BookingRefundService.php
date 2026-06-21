<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingPaymentStatusLog;
use App\Models\BookingRefund;

class BookingRefundService
{
    public function __construct(
        private readonly BookingPaymentNumberService $numbers,
    ) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function createRefund(Booking $booking, float|int|string $amount, string $type, array $context = []): BookingRefund
    {
        $paymentId = $context['booking_payment_id']
            ?? $booking->bookingPayments()->whereIn('status', ['paid', 'partially_paid'])->latest('id')->value('id');

        $refund = BookingRefund::query()->create([
            'refund_number' => $this->numbers->generateRefundNumber(),
            'booking_id' => $booking->id,
            'booking_payment_id' => $paymentId,
            'guest_user_id' => $booking->guest_user_id,
            'host_user_id' => $booking->host_user_id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'refund_type' => $type,
            'status' => 'pending',
            'amount' => $amount,
            'currency' => $booking->currency,
            'reason_key' => $context['reason_key'] ?? null,
            'source_type' => $context['source_type'] ?? null,
            'source_id' => $context['source_id'] ?? null,
            'requested_at' => now(),
            'comment' => $context['comment'] ?? null,
        ]);

        $this->logRefund($refund, null, 'pending', 'refund_created');

        return $refund->fresh();
    }

    public function createFullRefund(Booking $booking, string $reason): BookingRefund
    {
        return $this->createRefund($booking, (float) $booking->total_payable, 'full_refund', [
            'reason_key' => $reason,
        ]);
    }

    public function createPartialRefund(Booking $booking, float|int|string $amount, string $reason): BookingRefund
    {
        return $this->createRefund($booking, $amount, 'partial_refund', [
            'reason_key' => $reason,
        ]);
    }

    public function createDepositRefund(Booking $booking, float|int|string $amount): BookingRefund
    {
        return $this->createRefund($booking, $amount, 'deposit_refund', [
            'reason_key' => 'deposit_return',
        ]);
    }

    public function markRefundApproved(BookingRefund $refund): BookingRefund
    {
        return $this->transitionRefund($refund, 'approved', ['approved_at' => now()]);
    }

    public function markRefundProcessing(BookingRefund $refund): BookingRefund
    {
        return $this->transitionRefund($refund, 'processing', ['processed_at' => now()]);
    }

    public function markRefundCompleted(BookingRefund $refund): BookingRefund
    {
        return $this->transitionRefund($refund, 'completed', ['completed_at' => now()]);
    }

    public function markRefundFailed(BookingRefund $refund, string $reason): BookingRefund
    {
        return $this->transitionRefund($refund, 'failed', [
            'failed_at' => now(),
            'failure_reason' => $reason,
        ], $reason);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transitionRefund(BookingRefund $refund, string $newStatus, array $attributes = [], ?string $note = null): BookingRefund
    {
        $oldStatus = $refund->status;

        $refund->forceFill([
            ...$attributes,
            'status' => $newStatus,
        ])->save();

        $this->logRefund($refund, $oldStatus, $newStatus, 'refund_'.$newStatus, $note);

        return $refund->fresh();
    }

    private function logRefund(BookingRefund $refund, ?string $oldStatus, string $newStatus, string $eventKey, ?string $note = null): void
    {
        BookingPaymentStatusLog::query()->create([
            'booking_payment_id' => $refund->booking_payment_id,
            'booking_refund_id' => $refund->id,
            'booking_id' => $refund->booking_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'event_key' => $eventKey,
            'note' => $note,
        ]);
    }
}
