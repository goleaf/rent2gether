<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\ComplaintEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintEvent>
 */
class ComplaintEventFactory extends Factory
{
    protected $model = ComplaintEvent::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'event_key' => 'complaint_submitted',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
