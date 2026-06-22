<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\DisputeEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeEvent>
 */
class DisputeEventFactory extends Factory
{
    protected $model = DisputeEvent::class;

    public function definition(): array
    {
        return [
            'dispute_case_id' => DisputeCase::factory(),
            'event_key' => 'dispute_opened',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
