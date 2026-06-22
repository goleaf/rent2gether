<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryMovement>
 */
class InventoryMovementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'movement_number' => sprintf('INVM-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'inventory_item_id' => InventoryItem::factory(),
            'to_location_type' => 'storage',
            'movement_type' => 'manual_adjustment',
            'quantity' => 1,
            'moved_at' => now(),
        ];
    }
}
