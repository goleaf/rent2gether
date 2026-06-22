<?php

namespace Database\Factories;

use App\Models\InspectionTask;
use App\Models\InspectionTaskItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InspectionTaskItem>
 */
class InspectionTaskItemFactory extends Factory
{
    public function definition(): array
    {
        $itemKey = fake()->randomElement(['sleeping_place_clean', 'bedding_ready', 'towel_ready', 'photos_uploaded']);

        return [
            'inspection_task_id' => InspectionTask::factory(),
            'item_key' => $itemKey,
            'label_key' => 'inspections.items.'.$itemKey,
            'status' => 'pending',
            'required' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
