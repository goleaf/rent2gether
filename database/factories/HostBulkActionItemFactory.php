<?php

namespace Database\Factories;

use App\Models\HostBulkActionBatch;
use App\Models\HostBulkActionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostBulkActionItem>
 */
class HostBulkActionItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'batch_id' => HostBulkActionBatch::factory(),
            'target_type' => 'sleeping_place',
            'target_id' => $this->faker->numberBetween(1, 999),
            'status' => 'pending',
            'before_json' => null,
            'after_json' => null,
            'error_message' => null,
            'processed_at' => null,
        ];
    }
}
