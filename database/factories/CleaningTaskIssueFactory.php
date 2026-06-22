<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningTaskIssue>
 */
class CleaningTaskIssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cleaning_task_id' => CleaningTask::factory(),
            'booking_id' => Booking::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'issue_type' => 'needs_repair',
            'severity' => 'medium',
            'status' => 'reported',
            'description' => fake()->sentence(),
            'creates_maintenance_request' => false,
            'creates_deposit_review' => false,
            'creates_complaint' => false,
            'blocks_calendar' => false,
        ];
    }
}
