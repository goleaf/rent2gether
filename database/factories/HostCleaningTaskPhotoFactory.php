<?php

namespace Database\Factories;

use App\Models\HostCleaningTask;
use App\Models\HostCleaningTaskPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCleaningTaskPhoto>
 */
class HostCleaningTaskPhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_cleaning_task_id' => HostCleaningTask::factory(),
            'uploaded_by_user_id' => User::factory(),
            'photo_type' => 'after',
            'path' => 'cleaning/photos/after.jpg',
            'note' => null,
        ];
    }
}
