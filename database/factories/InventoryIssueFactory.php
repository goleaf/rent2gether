<?php

namespace Database\Factories;

use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryIssue>
 */
class InventoryIssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inventory_issue_number' => sprintf('INVI-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'inventory_item_id' => InventoryItem::factory(),
            'host_user_id' => User::factory(),
            'guest_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'issue_type' => fake()->randomElement(['lost', 'damaged', 'missing']),
            'severity' => 'medium',
            'status' => 'reported',
            'description' => fake()->sentence(),
            'quantity_affected' => 1,
            'currency' => 'EUR',
            'guest_responsibility_status' => 'unknown',
        ];
    }
}
