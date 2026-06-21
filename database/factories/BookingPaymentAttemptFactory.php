<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingPaymentAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPaymentAttempt>
 */
class BookingPaymentAttemptFactory extends Factory
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
            'guest_user_id' => User::factory(),
            'attempt_number' => 1,
            'status' => 'created',
            'payment_method' => 'internal_test',
            'amount' => 126,
            'currency' => 'EUR',
            'provider' => null,
            'provider_attempt_id' => null,
            'provider_redirect_url' => null,
            'provider_status' => null,
            'provider_error_code' => null,
            'provider_error_message' => null,
            'provider_payload_json' => null,
            'started_at' => null,
            'succeeded_at' => null,
            'failed_at' => null,
            'cancelled_at' => null,
            'expired_at' => null,
        ];
    }
}
