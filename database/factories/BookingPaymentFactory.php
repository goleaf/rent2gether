<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingPayment>
 */
class BookingPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_number' => sprintf('PAY-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_quote_id' => null,
            'booking_request_id' => null,
            'booking_extension_id' => null,
            'booking_relocation_id' => null,
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'payment_type' => 'full_payment',
            'payment_purpose' => 'booking_payment',
            'payment_method' => 'internal_test',
            'status' => 'waiting_payment',
            'amount' => 126,
            'currency' => 'EUR',
            'required_now_amount' => 126,
            'remaining_amount' => 0,
            'remaining_due_at' => null,
            'provider' => null,
            'provider_payment_id' => null,
            'provider_status' => null,
            'provider_payload_json' => null,
            'payment_deadline_at' => now()->addMinutes(30),
            'paid_at' => null,
            'failed_at' => null,
            'expired_at' => null,
            'cancelled_at' => null,
            'failure_reason' => null,
            'description' => null,
        ];
    }
}
