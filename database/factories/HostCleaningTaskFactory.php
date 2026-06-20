<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HostCleaningTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCleaningTask>
 */
class HostCleaningTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => null,
            'status' => 'planned',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '12:00',
            'reason' => 'after_checkout',
            'note' => null,
            'completed_at' => null,
        ];
    }

    public function forBooking(): static
    {
        return $this->state(fn (): array => [
            'booking_id' => Booking::factory(),
        ]);
    }
}
