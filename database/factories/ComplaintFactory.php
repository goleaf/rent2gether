<?php

namespace Database\Factories;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Complaint;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'bed_id' => Bed::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'type' => ComplaintType::Other->value,
            'description' => $this->faker->paragraph(),
            'photos' => [],
            'urgency' => 'normal',
            'desired_resolution' => 'refund_review',
            'status' => ComplaintStatus::Open->value,
        ];
    }
}
