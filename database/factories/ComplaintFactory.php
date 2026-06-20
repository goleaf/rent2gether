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
            'reference' => strtoupper('CMP-'.$this->faker->unique()->bothify('######')),
            'reporter_id' => User::factory(),
            'reported_user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'bed_id' => Bed::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'type' => ComplaintType::Other->value,
            'priority' => 'normal',
            'description' => $this->faker->paragraph(),
            'media' => [],
            'photos' => [],
            'urgency' => 'normal',
            'desired_resolution' => 'refund_review',
            'refund_requested' => false,
            'deposit_hold_requested' => false,
            'status' => ComplaintStatus::Created->value,
        ];
    }
}
