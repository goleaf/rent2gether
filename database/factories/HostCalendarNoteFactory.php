<?php

namespace Database\Factories;

use App\Models\HostCalendarNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCalendarNote>
 */
class HostCalendarNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => null,
            'booking_id' => null,
            'note_date' => now()->toDateString(),
            'note_type' => 'general',
            'note' => $this->faker->sentence(),
            'is_private' => true,
        ];
    }
}
