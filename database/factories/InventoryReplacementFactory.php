<?php

namespace Database\Factories;

use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\InventoryReplacement;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryReplacement>
 */
class InventoryReplacementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'replacement_number' => sprintf('INVR-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'old_inventory_item_id' => InventoryItem::factory(),
            'inventory_issue_id' => InventoryIssue::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'replacement_reason' => 'manual',
            'status' => 'planned',
            'replacement_cost_amount' => 20,
            'currency' => 'EUR',
        ];
    }
}
