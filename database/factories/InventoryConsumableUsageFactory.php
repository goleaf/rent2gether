<?php

namespace Database\Factories;

use App\Models\InventoryConsumableUsage;
use App\Models\InventoryItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryConsumableUsage>
 */
class InventoryConsumableUsageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'usage_type' => 'manual',
            'quantity_used' => 1,
            'unit' => 'pcs',
            'used_at' => now(),
        ];
    }
}
