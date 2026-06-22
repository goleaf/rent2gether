<?php

namespace Database\Factories;

use App\Models\InventoryEvent;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryEvent>
 */
class InventoryEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'event_key' => 'inventory_created',
            'event_type' => 'system',
            'occurred_at' => now(),
            'context_json' => [],
        ];
    }
}
