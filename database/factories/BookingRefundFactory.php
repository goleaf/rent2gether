<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\BookingRefund;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRefund>
 */
class BookingRefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'refund_number' => sprintf('REF-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'booking_payment_id' => BookingPayment::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'refund_type' => 'partial_refund',
            'status' => 'pending',
            'amount' => 20,
            'currency' => 'EUR',
            'reason_key' => null,
            'source_type' => null,
            'source_id' => null,
            'provider' => null,
            'provider_refund_id' => null,
            'provider_status' => null,
            'provider_payload_json' => null,
            'requested_at' => now(),
            'approved_at' => null,
            'processed_at' => null,
            'completed_at' => null,
            'failed_at' => null,
            'failure_reason' => null,
            'comment' => null,
        ];
    }
}
