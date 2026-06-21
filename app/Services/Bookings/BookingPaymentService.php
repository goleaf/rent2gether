<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingPayment;
use Illuminate\Support\Collection;

class BookingPaymentService
{
    public function __construct(
        private readonly BookingPaymentStateService $states,
    ) {}

    /**
     * @return Collection<int, BookingPayment>
     */
    public function getForBooking(Booking $booking): Collection
    {
        return $booking->bookingPayments()
            ->with(['allocations', 'attempts', 'deadlines', 'receipt'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getOutstandingAmount(Booking $booking): float
    {
        return max(0, (float) $booking->total_payable - $this->getPaidAmount($booking));
    }

    public function getPaidAmount(Booking $booking): float
    {
        $paid = (float) $booking->bookingPayments()
            ->whereIn('status', ['paid', 'refunded', 'partially_refunded'])
            ->sum('amount');

        $partial = (float) $booking->bookingPayments()
            ->where('status', 'partially_paid')
            ->sum('required_now_amount');

        return $paid + $partial;
    }

    public function isFullyPaid(Booking $booking): bool
    {
        return (float) $booking->total_payable > 0
            && $this->getPaidAmount($booking) >= (float) $booking->total_payable;
    }

    public function isPartiallyPaid(Booking $booking): bool
    {
        $paid = $this->getPaidAmount($booking);

        return $paid > 0 && $paid < (float) $booking->total_payable;
    }

    public function canRetryPayment(BookingPayment $payment): bool
    {
        if (! in_array($payment->status, ['waiting_payment', 'payment_started', 'pending', 'failed'], true)) {
            return false;
        }

        return $payment->payment_deadline_at === null || now()->lessThanOrEqualTo($payment->payment_deadline_at);
    }

    public function cancelPayment(BookingPayment $payment, string $reason): BookingPayment
    {
        $payment->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'failure_reason' => $reason,
        ])->save();

        $this->states->syncBookingPaymentStatus($payment->booking);

        return $payment->fresh();
    }
}
