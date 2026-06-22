<?php

namespace Database\Factories;

use App\Models\InspectionEvent;
use App\Models\InspectionTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InspectionEvent>
 */
class InspectionEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inspection_task_id' => InspectionTask::factory(),
            'event_key' => 'inspection_created',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => null,
        ];
    }
}
