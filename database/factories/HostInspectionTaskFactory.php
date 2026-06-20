<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\HostInspectionTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostInspectionTask>
 */
class HostInspectionTaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => Booking::factory(),
            'booking_check_out_id' => BookingCheckOut::factory(),
            'status' => 'planned',
            'scheduled_date' => now()->toDateString(),
            'scheduled_time' => '11:30',
            'reason' => 'after_checkout',
            'checklist_json' => [],
            'result_json' => [],
            'note' => null,
            'completed_at' => null,
        ];
    }
}
