<?php

namespace Database\Factories;

use App\Models\DisputeCase;
use App\Models\DisputeStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisputeStatusLog>
 */
class DisputeStatusLogFactory extends Factory
{
    protected $model = DisputeStatusLog::class;

    public function definition(): array
    {
        return [
            'dispute_case_id' => DisputeCase::factory(),
            'new_status' => 'opened',
            'context_json' => [],
        ];
    }
}
