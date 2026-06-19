<?php

namespace Database\Factories;

use App\Enums\RefundRequestStatus;
use App\Models\Booking;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RefundRequest>
 */
class RefundRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'requested_by_user_id' => User::factory(),
            'amount' => 10,
            'currency' => 'EUR',
            'reason' => 'schedule_changed',
            'details' => $this->faker->sentence(),
            'status' => RefundRequestStatus::Requested->value,
            'resolved_at' => null,
        ];
    }
}
