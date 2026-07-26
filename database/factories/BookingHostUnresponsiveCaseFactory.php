<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingHostUnresponsiveCase>
 */
class BookingHostUnresponsiveCaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_number' => sprintf('HU-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory()->host(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'case_type' => 'check_in_no_response',
            'reason_key' => 'host_not_answering_messages',
            'status' => 'reported',
            'check_in_date' => now()->toDateString(),
            'planned_check_in_time' => '18:00',
            'check_in_window' => '18:00-22:00',
            'guest_wants_help' => true,
            'response_deadline_minutes' => 60,
            'response_deadline_at' => now()->addMinutes(60),
            'refund_amount' => 0,
            'compensation_amount_future' => 0,
            'currency' => 'EUR',
            'future_support_review_required' => false,
        ];
    }
}
