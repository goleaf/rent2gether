<?php

namespace Database\Factories;

use App\Models\HostCleaningTask;
use App\Models\HostCleaningTaskItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostCleaningTaskItem>
 */
class HostCleaningTaskItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_cleaning_task_id' => HostCleaningTask::factory(),
            'item_key' => 'replace_bedding',
            'label_key' => 'cleaning.checklist.replace_bedding',
            'status' => 'pending',
            'required' => true,
            'sort_order' => 10,
            'completed_by_user_id' => null,
            'completed_at' => null,
            'note' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'done',
            'completed_by_user_id' => User::factory(),
            'completed_at' => now(),
        ]);
    }
}
