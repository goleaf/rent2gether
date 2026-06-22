<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryStatusLog>
 */
class InventoryStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'old_status' => null,
            'new_status' => 'available',
            'reason_key' => 'inventory.events.inventory_updated',
            'context_json' => [],
        ];
    }
}
