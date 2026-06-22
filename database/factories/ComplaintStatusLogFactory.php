<?php

namespace Database\Factories;

use App\Models\ComplaintCase;
use App\Models\ComplaintStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintStatusLog>
 */
class ComplaintStatusLogFactory extends Factory
{
    protected $model = ComplaintStatusLog::class;

    public function definition(): array
    {
        return [
            'complaint_case_id' => ComplaintCase::factory(),
            'new_status' => 'submitted',
            'context_json' => [],
        ];
    }
}
