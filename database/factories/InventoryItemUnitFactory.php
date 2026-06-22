<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\InventoryItem;
use App\Models\InventoryItemUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItemUnit>
 */
class InventoryItemUnitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_item_id' => InventoryItem::factory(),
            'unit_number' => sprintf('UNIT-%06d', fake()->unique()->numberBetween(1, 999999)),
            'unit_label' => fake()->optional()->word(),
            'status' => 'available',
            'condition_status' => 'good',
            'current_location_type' => 'storage',
            'assigned_booking_id' => fake()->optional()->randomElement([Booking::factory(), null]),
            'assigned_guest_user_id' => fake()->optional()->randomElement([User::factory(), null]),
        ];
    }
}
