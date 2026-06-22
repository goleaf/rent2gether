<?php

namespace Database\Factories;

use App\Models\CleaningStatusLog;
use App\Models\CleaningTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningStatusLog>
 */
class CleaningStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cleaning_task_id' => CleaningTask::factory(),
            'old_status' => null,
            'new_status' => 'scheduled',
            'reason_key' => 'cleaning_created',
            'context_json' => null,
        ];
    }
}
