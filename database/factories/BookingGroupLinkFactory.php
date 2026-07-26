<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingGroupLink;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingGroupLink>
 */
class BookingGroupLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_booking_number' => sprintf('BG-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'main_booking_id' => null,
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'status' => 'active',
        ];
    }
}
