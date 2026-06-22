<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\InspectionTask;
use App\Models\InspectionTaskMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InspectionTaskMedia>
 */
class InspectionTaskMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_task_id' => InspectionTask::factory(),
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'inspection_sleeping_place',
            'path' => 'inspections/'.fake()->uuid().'.jpg',
            'thumbnail_path' => null,
            'caption' => null,
            'visibility' => 'host_only',
        ];
    }
}
