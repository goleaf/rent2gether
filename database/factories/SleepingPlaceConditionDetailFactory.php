<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceConditionDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceConditionDetail>
 */
class SleepingPlaceConditionDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'condition_state' => 'good',
            'frame_condition' => 'good',
            'mattress_condition' => 'good',
            'bedding_condition' => 'good',
            'pillow_condition' => 'good',
            'blanket_condition' => 'good',
            'curtain_condition' => 'good',
            'lamp_condition' => 'good',
            'socket_condition' => 'good',
            'locker_condition' => 'good',
            'lock_condition' => 'good',
            'has_damage' => false,
            'has_stains' => false,
            'has_smell' => false,
            'squeaks' => false,
            'needs_repair' => false,
            'needs_mattress_replacement' => false,
            'needs_bedding_replacement' => false,
            'last_cleaned_at' => now()->subDay(),
            'last_bedding_changed_at' => now()->subDay(),
            'last_checked_at' => now(),
            'last_repaired_at' => null,
            'host_condition_note' => null,
        ];
    }
}
