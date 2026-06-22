<?php

namespace Database\Factories;

use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_number' => sprintf('INV-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'inventory_category_id' => InventoryCategory::factory(),
            'item_type' => fake()->randomElement(['key', 'bedding_set', 'towel', 'lamp', 'locker']),
            'inventory_scope' => fake()->randomElement(['property', 'room', 'sleeping_place', 'guest_issued']),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['active', 'available', 'in_storage']),
            'condition_status' => 'good',
            'quantity' => 1,
            'unit' => 'pcs',
            'is_returnable' => false,
            'is_consumable' => false,
            'is_fixed_asset' => false,
            'is_guest_visible' => true,
            'is_required_for_readiness' => false,
            'is_promised_in_listing' => false,
            'current_location_type' => 'sleeping_place',
            'currency' => 'EUR',
        ];
    }
}
