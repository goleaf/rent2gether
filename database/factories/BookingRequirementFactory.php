<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingRequirement>
 */
class BookingRequirementFactory extends Factory
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
            'requirement_key' => 'rules_acceptance',
            'status' => BookingRequirement::STATUS_PENDING,
            'required' => true,
            'completed_at' => null,
            'message_key' => 'bookings.requirements.rules_acceptance',
        ];
    }
}
