<?php

namespace Database\Factories;

use App\Models\CleaningEvent;
use App\Models\CleaningTask;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CleaningEvent>
 */
class CleaningEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cleaning_task_id' => CleaningTask::factory(),
            'event_key' => 'cleaning_created',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => null,
        ];
    }
}
