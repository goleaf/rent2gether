<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingPaymentDeadline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPaymentDeadline>
 */
class BookingPaymentDeadlineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'booking_payment_id' => BookingPayment::factory(),
            'deadline_type' => 'initial_payment',
            'due_at' => now()->addMinutes(30),
            'status' => 'pending',
        ];
    }
}
