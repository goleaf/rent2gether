<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomConditionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomConditionDetail>
 */
class RoomConditionDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'condition_state' => 'good',
            'repair_state' => 'good',
            'cleanliness_level' => 'high',
            'floor_condition' => 'good',
            'walls_condition' => 'good',
            'ceiling_condition' => 'good',
            'window_condition' => 'good',
            'door_condition' => 'good',
            'lock_condition' => 'good',
            'furniture_condition' => 'good',
            'wardrobe_condition' => 'good',
            'desk_condition' => 'good',
            'chairs_condition' => 'good',
            'balcony_condition' => null,
            'has_dust' => false,
            'has_bad_smell' => false,
            'has_damp_marks' => false,
            'has_mold' => false,
            'has_insects' => false,
            'has_damage' => false,
            'needs_repair' => false,
            'recently_renovated' => true,
            'needs_refresh' => false,
            'last_cleaned_at' => now()->subDay(),
            'last_checked_at' => now(),
            'last_repaired_at' => now()->subMonth(),
            'host_condition_note' => null,
        ];
    }
}
