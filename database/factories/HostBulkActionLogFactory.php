<?php

namespace Database\Factories;

use App\Models\HostBulkActionBatch;
use App\Models\HostBulkActionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostBulkActionLog>
 */
class HostBulkActionLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'batch_id' => HostBulkActionBatch::factory(),
            'action_type' => 'change_price',
            'target_type' => 'sleeping_place',
            'target_id' => $this->faker->numberBetween(1, 999),
            'message' => 'host_bulk.log.processed',
            'context_json' => ['affected_count' => 1],
        ];
    }
}
