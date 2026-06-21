<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingPaymentAllocation;
use Illuminate\Support\Collection;

class BookingPaymentAllocationService
{
    /**
     * @return Collection<int, BookingPaymentAllocation>
     */
    public function createAllocationsFromBooking(BookingPayment $payment, Booking $booking): Collection
    {
        $allocations = collect();

        $this->pushAllocation($allocations, $payment, 'accommodation', (float) $booking->accommodation_amount, false);
        $this->pushAllocation($allocations, $payment, 'cleaning_fee', (float) $booking->cleaning_fee_amount, false);
        $this->pushAllocation($allocations, $payment, 'guest_service_fee', (float) $booking->service_fee_amount, false);
        $this->pushAllocation($allocations, $payment, 'deposit', (float) $booking->deposit_amount, true);
        $this->pushAllocation($allocations, $payment, 'tax_future', (float) ($booking->tax_amount ?? 0), false);
        $this->pushAllocation($allocations, $payment, 'city_fee_future', (float) ($booking->city_fee_amount ?? 0), false);

        return $allocations;
    }

    public function createAllocation(BookingPayment $payment, string $type, float|int|string $amount, bool $refundable): BookingPaymentAllocation
    {
        return BookingPaymentAllocation::query()->create([
            'booking_payment_id' => $payment->id,
            'booking_id' => $payment->booking_id,
            'allocation_type' => $type,
            'amount' => $amount,
            'currency' => $payment->currency,
            'refundable' => $refundable,
        ]);
    }

    /**
     * @return Collection<int, BookingPaymentAllocation>
     */
    public function getRefundableAllocations(BookingPayment $payment): Collection
    {
        return $payment->allocations()->where('refundable', true)->get();
    }

    /**
     * @return Collection<int, BookingPaymentAllocation>
     */
    public function getNonRefundableAllocations(BookingPayment $payment): Collection
    {
        return $payment->allocations()->where('refundable', false)->get();
    }

    /**
     * @param  Collection<int, BookingPaymentAllocation>  $allocations
     */
    private function pushAllocation(Collection $allocations, BookingPayment $payment, string $type, float $amount, bool $refundable): void
    {
        if ($amount <= 0) {
            return;
        }

        $allocations->push($this->createAllocation($payment, $type, $amount, $refundable));
    }
}
