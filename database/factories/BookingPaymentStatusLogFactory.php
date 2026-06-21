<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingPaymentStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPaymentStatusLog>
 */
class BookingPaymentStatusLogFactory extends Factory
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
            'booking_payment_attempt_id' => null,
            'booking_refund_id' => null,
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'old_status' => null,
            'new_status' => 'waiting_payment',
            'event_key' => 'payment_created',
            'note' => null,
            'context_json' => null,
        ];
    }
}
