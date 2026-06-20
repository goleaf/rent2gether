<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
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
            'booking_check_out_id' => null,
            'cleaning_type' => 'after_check_out',
            'status' => 'planned',
            'priority' => 'normal',
            'scheduled_date' => now()->addDay()->toDateString(),
            'scheduled_time' => '12:00',
            'due_at' => now()->addDay()->setTime(14, 0),
            'started_at' => null,
            'reason' => 'after_checkout',
            'note' => null,
            'host_note' => null,
            'cleaner_comment' => null,
            'assigned_to_type' => null,
            'assigned_to_user_id' => null,
            'assigned_person_name' => null,
            'assigned_person_contact' => null,
            'before_photos_required' => false,
            'after_photos_required' => true,
            'has_before_photos' => false,
            'has_after_photos' => false,
            'has_damage_found' => false,
            'has_forgotten_items' => false,
            'has_extra_dirty' => false,
            'needs_repair' => false,
            'needs_repeat_cleaning' => false,
            'place_ready_after_cleaning' => false,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }

    public function forBooking(): static
    {
        return $this->state(fn (): array => [
            'booking_id' => Booking::factory(),
        ]);
    }

    public function forCheckOut(): static
    {
        return $this->state(fn (): array => [
            'booking_check_out_id' => BookingCheckOut::factory(),
            'cleaning_type' => 'after_check_out',
            'reason' => 'after_checkout',
        ]);
    }
}
