<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingPaymentStatusLog;

class BookingPaymentStateService
{
    public function __construct(
        private readonly BookingStatusService $statuses,
        private readonly BookingCalendarIntegrationService $calendar,
        private readonly BookingPaymentCalendarIntegrationService $paymentCalendar,
    ) {}

    public function markWaitingPayment(Booking|BookingPayment $subject): Booking|BookingPayment
    {
        if ($subject instanceof BookingPayment) {
            return $this->transitionPayment($subject, 'waiting_payment', 'payment_waiting');
        }

        $booking = $subject;

        $booking->forceFill([
            'payment_status' => PaymentStatus::WaitingPayment,
            'payment_deadline_at' => $booking->payment_deadline_at ?: now()->addMinutes(20),
            'availability_hold_expires_at' => $booking->availability_hold_expires_at ?: now()->addMinutes(20),
        ])->save();

        return $this->statuses->transition($booking, BookingStatus::WaitingPayment->value, null, [
            'event_key' => 'payment_started',
            'event_type' => 'payment',
        ]);
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function markPaid(Booking|BookingPayment $subject, array $paymentData = []): Booking|BookingPayment
    {
        if ($subject instanceof BookingPayment) {
            $payment = $this->transitionPayment($subject, 'paid', 'payment_completed', [
                'paid_at' => $paymentData['paid_at'] ?? now(),
                'remaining_amount' => 0,
            ]);

            $this->paymentCalendar->convertPaymentLocksToBooked($payment->booking);
            $this->syncBookingPaymentStatus($payment->booking);

            return $payment;
        }

        $booking = $subject;

        $booking->forceFill([
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => $paymentData['paid_at'] ?? now(),
            'payment_paid_at' => $paymentData['paid_at'] ?? now(),
            'payment_method' => $paymentData['payment_method'] ?? $booking->payment_method,
        ])->save();

        $this->statuses->transition($booking, BookingStatus::Paid->value, null, [
            'event_key' => 'payment_completed',
            'event_type' => 'payment',
        ]);

        if (in_array((string) $booking->approval_type, ['instant_confirmed', 'request_only', 'requires_host_confirmation'], true)) {
            return $this->statuses->markConfirmed($booking->fresh());
        }

        return $booking->fresh();
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function markPartiallyPaid(Booking|BookingPayment $subject, mixed $paymentData = []): Booking|BookingPayment
    {
        if ($subject instanceof BookingPayment) {
            $amount = is_array($paymentData) ? (float) ($paymentData['amount'] ?? $subject->required_now_amount) : (float) $paymentData;
            $remaining = max(0, (float) $subject->amount - $amount);
            $payment = $this->transitionPayment($subject, 'partially_paid', 'payment_partially_paid', [
                'paid_at' => now(),
                'remaining_amount' => $remaining,
            ]);

            $this->syncBookingPaymentStatus($payment->booking);

            return $payment;
        }

        $booking = $subject;

        $booking->forceFill([
            'payment_status' => PaymentStatus::PartiallyPaid,
            'paid_at' => $paymentData['paid_at'] ?? now(),
            'payment_paid_at' => $paymentData['paid_at'] ?? now(),
        ])->save();

        return $this->statuses->transition($booking, BookingStatus::WaitingPayment->value, null, [
            'event_key' => 'payment_started',
            'event_type' => 'payment',
            'partial' => true,
        ]);
    }

    public function markPaymentStarted(BookingPayment $payment): BookingPayment
    {
        return $this->transitionPayment($payment, 'payment_started', 'payment_started');
    }

    public function markFailed(BookingPayment $payment, string $reason): BookingPayment
    {
        $payment = $this->transitionPayment($payment, 'failed', 'payment_failed', [
            'failed_at' => now(),
            'failure_reason' => $reason,
        ], $reason);

        $this->syncBookingPaymentStatus($payment->booking);

        if ($payment->payment_deadline_at !== null && now()->greaterThanOrEqualTo($payment->payment_deadline_at)) {
            $this->paymentCalendar->releaseLocksAfterPaymentFailure($payment->booking);
            $this->markPaymentFailed($payment->booking, $reason);
        }

        return $payment;
    }

    public function markExpired(BookingPayment $payment): BookingPayment
    {
        $payment = $this->transitionPayment($payment, 'expired', 'payment_expired', [
            'expired_at' => now(),
        ]);

        $this->paymentCalendar->releaseLocksAfterPaymentExpiration($payment->booking);
        $payment->booking->forceFill(['payment_status' => PaymentStatus::Failed])->save();
        $this->markPaymentFailed($payment->booking, 'payment_expired');

        return $payment;
    }

    public function markRefunded(BookingPayment $payment): BookingPayment
    {
        $payment = $this->transitionPayment($payment, 'refunded', 'payment_refunded');
        $this->syncBookingPaymentStatus($payment->booking);

        return $payment;
    }

    public function markPaymentFailed(Booking $booking, string $reason): Booking
    {
        $booking->forceFill([
            'payment_status' => PaymentStatus::Failed,
        ])->save();

        $this->releaseLocksAfterPaymentFailure($booking);

        return $this->statuses->transition($booking, BookingStatus::PaymentFailed->value, null, [
            'event_key' => 'payment_failed',
            'event_type' => 'payment',
            'reason' => $reason,
        ]);
    }

    public function releaseLocksAfterPaymentFailure(Booking $booking): void
    {
        $this->calendar->releaseBookingLocks($booking, 'payment_failed');
    }

    public function isPaymentDeadlinePassed(Booking $booking): bool
    {
        return $booking->payment_deadline_at !== null && now()->greaterThan($booking->payment_deadline_at);
    }

    public function syncBookingPaymentStatus(Booking $booking): Booking
    {
        $payments = $booking->bookingPayments()->get(['id', 'amount', 'required_now_amount', 'status']);

        if ($payments->isEmpty()) {
            return $booking->fresh();
        }

        $paidAmount = (float) $payments
            ->whereIn('status', ['paid', 'refunded', 'partially_refunded'])
            ->sum(fn (BookingPayment $payment): float => (float) $payment->amount);

        $partiallyPaidAmount = (float) $payments
            ->where('status', 'partially_paid')
            ->sum(fn (BookingPayment $payment): float => (float) $payment->required_now_amount);

        $received = $paidAmount + $partiallyPaidAmount;
        $required = (float) $booking->total_payable;

        if ($required > 0 && $received >= $required) {
            $booking->forceFill(['payment_status' => PaymentStatus::Paid])->save();

            return $booking->fresh();
        }

        if ($received > 0) {
            $booking->forceFill(['payment_status' => PaymentStatus::PartiallyPaid])->save();

            return $booking->fresh();
        }

        if ($payments->contains(fn (BookingPayment $payment): bool => in_array($payment->status, ['failed', 'expired'], true))) {
            $booking->forceFill(['payment_status' => PaymentStatus::Failed])->save();

            return $booking->fresh();
        }

        $booking->forceFill(['payment_status' => PaymentStatus::WaitingPayment])->save();

        return $booking->fresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transitionPayment(BookingPayment $payment, string $newStatus, string $eventKey, array $attributes = [], ?string $note = null): BookingPayment
    {
        $oldStatus = $payment->status;

        $payment->forceFill([
            ...$attributes,
            'status' => $newStatus,
        ])->save();

        BookingPaymentStatusLog::query()->create([
            'booking_payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'event_key' => $eventKey,
            'note' => $note,
        ]);

        return $payment->fresh(['booking']);
    }
}
