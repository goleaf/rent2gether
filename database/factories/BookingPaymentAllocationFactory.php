<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingPaymentAllocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPaymentAllocation>
 */
class BookingPaymentAllocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_payment_id' => BookingPayment::factory(),
            'booking_id' => Booking::factory(),
            'allocation_type' => 'accommodation',
            'amount' => 60,
            'currency' => 'EUR',
            'refundable' => false,
            'line_reference_type' => null,
            'line_reference_id' => null,
        ];
    }
}
