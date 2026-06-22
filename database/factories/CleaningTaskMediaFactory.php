<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\CleaningTask;
use App\Models\CleaningTaskMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningTaskMedia>
 */
class CleaningTaskMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cleaning_task_id' => CleaningTask::factory(),
            'booking_id' => Booking::factory(),
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'after_cleaning_sleeping_place',
            'path' => 'cleaning/'.fake()->uuid().'.jpg',
            'thumbnail_path' => null,
            'caption' => null,
            'visibility' => 'host_only',
        ];
    }
}
