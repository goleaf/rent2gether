<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomAccessDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomAccessDetail>
 */
class RoomAccessDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'has_door' => true,
            'has_lock' => false,
            'has_key' => false,
            'key_given_to_guest' => false,
            'shared_key' => false,
            'key_only_with_host' => false,
            'can_lock_from_inside' => true,
            'can_lock_from_outside' => false,
            'has_latch' => true,
            'glass_door' => false,
            'privacy_level' => 'shared',
            'host_entry_rules' => null,
            'other_guests_entry_rules' => null,
            'has_wardrobe' => true,
            'has_shared_wardrobe' => true,
            'has_personal_lockers' => true,
            'personal_lockers_count' => 2,
            'lockers_have_locks' => true,
            'has_shelves' => true,
            'has_hangers' => true,
            'has_luggage_space' => true,
            'has_shoe_space' => true,
            'has_coat_space' => true,
            'has_bedside_table' => false,
            'has_drawer_unit' => false,
            'has_desk' => true,
            'desks_count' => 1,
            'has_chairs' => true,
            'chairs_count' => 2,
            'has_mirror' => true,
            'has_hooks' => true,
            'has_drying_rack' => false,
            'can_store_food' => false,
            'food_storage_allowed_type' => 'kitchen_only',
        ];
    }
}
