<?php

namespace Database\Factories;

use App\Models\HostBulkActionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostBulkActionBatch>
 */
class HostBulkActionBatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action_type' => 'change_price',
            'target_type' => 'sleeping_place',
            'status' => 'draft',
            'selected_count' => 0,
            'affected_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'payload_json' => ['price' => 20],
            'preview_json' => null,
            'result_json' => null,
            'started_at' => null,
            'completed_at' => null,
            'cancelled_at' => null,
        ];
    }
}
