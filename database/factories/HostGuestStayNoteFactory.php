<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HostGuestStayNote;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostGuestStayNote>
 */
class HostGuestStayNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'guest_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'note' => $this->faker->sentence(),
            'importance' => 'normal',
            'is_pinned' => false,
        ];
    }
}
