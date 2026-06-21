<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;

class BookingPaymentStateService
{
    public function __construct(
        private readonly BookingStatusService $statuses,
        private readonly BookingCalendarIntegrationService $calendar,
    ) {}

    public function markWaitingPayment(Booking $booking): Booking
    {
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
    public function markPaid(Booking $booking, array $paymentData = []): Booking
    {
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
    public function markPartiallyPaid(Booking $booking, array $paymentData = []): Booking
    {
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
}
