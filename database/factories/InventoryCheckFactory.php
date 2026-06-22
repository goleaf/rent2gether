<?php

namespace Database\Factories;

use App\Models\InventoryCheck;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryCheck>
 */
class InventoryCheckFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_check_number' => sprintf('INVC-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'check_type' => 'manual',
            'status' => 'draft',
            'items_expected_count' => 0,
            'items_checked_count' => 0,
            'items_missing_count' => 0,
            'items_damaged_count' => 0,
            'issues_found' => false,
        ];
    }
}
