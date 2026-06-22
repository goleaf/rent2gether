<?php

namespace Database\Factories;

use App\Models\CleaningTask;
use App\Models\CleaningTaskItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningTaskItem>
 */
class CleaningTaskItemFactory extends Factory
{
    public function definition(): array
    {
        $itemKey = fake()->randomElement(['replace_bedding', 'replace_towel', 'wipe_dust', 'upload_after_photos']);

        return [
            'cleaning_task_id' => CleaningTask::factory(),
            'item_key' => $itemKey,
            'label_key' => 'cleaning.items.'.$itemKey,
            'status' => 'pending',
            'required' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
