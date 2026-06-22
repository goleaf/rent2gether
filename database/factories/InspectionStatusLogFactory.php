<?php

namespace Database\Factories;

use App\Models\InspectionStatusLog;
use App\Models\InspectionTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InspectionStatusLog>
 */
class InspectionStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_task_id' => InspectionTask::factory(),
            'old_status' => null,
            'new_status' => 'scheduled',
            'reason_key' => 'inspection_created',
            'context_json' => null,
        ];
    }
}
