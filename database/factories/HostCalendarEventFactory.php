<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\HostCalendarEvent;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCalendarEvent>
 */
class HostCalendarEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => null,
            'cleaning_task_id' => null,
            'event_type' => 'note',
            'event_status' => 'active',
            'event_date' => now()->toDateString(),
            'title_key' => 'host_calendar.event_titles.note',
            'title_params_json' => [],
            'description_key' => null,
            'description_params_json' => null,
            'guest_user_id' => null,
            'guest_display_name' => null,
            'needs_cleaning' => false,
            'needs_inspection' => false,
            'needs_repair' => false,
            'currency' => 'EUR',
            'priority' => 0,
            'source' => 'factory',
            'is_private' => true,
        ];
    }

    public function forBooking(): static
    {
        return $this->state(fn (): array => [
            'booking_id' => Booking::factory(),
            'event_type' => 'booking',
        ]);
    }
}
