<?php

namespace Database\Factories;

use App\Models\InventoryCheck;
use App\Models\InventoryCheckItem;
use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryCheckItem>
 */
class InventoryCheckItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_check_id' => InventoryCheck::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'expected_present' => true,
            'is_present' => true,
            'expected_return' => false,
            'is_returned' => false,
            'missing' => false,
            'damaged' => false,
            'dirty' => false,
            'needs_cleaning' => false,
            'needs_washing' => false,
            'needs_repair' => false,
            'needs_replacement' => false,
        ];
    }
}
