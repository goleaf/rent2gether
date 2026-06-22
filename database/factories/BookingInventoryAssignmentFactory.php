<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingInventoryAssignment;
use App\Models\InventoryItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingInventoryAssignment>
 */
class BookingInventoryAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'assignment_number' => sprintf('INVA-%s-%06d', now()->format('Y'), fake()->unique()->numberBetween(1, 999999)),
            'booking_id' => Booking::factory(),
            'guest_user_id' => User::factory(),
            'host_user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'inventory_item_id' => InventoryItem::factory(),
            'assignment_type' => 'issued_at_check_in',
            'status' => 'planned',
            'expected_return' => true,
            'quantity' => 1,
        ];
    }
}
