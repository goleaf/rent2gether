<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\InventoryStockAlert;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryStockAlert>
 */
class InventoryStockAlertFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'alert_type' => 'manual',
            'status' => 'active',
            'threshold_quantity' => 2,
            'current_quantity' => 1,
            'message_key' => 'inventory.messages.low_stock',
        ];
    }
}
