<?php

namespace Database\Factories;

use App\Models\GuestPreference;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestPreference>
 */
class GuestPreferenceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'preferred_budget_min' => 15,
            'preferred_budget_max' => 45,
            'preferred_currency' => 'EUR',
            'preferred_room_type' => 'shared',
            'preferred_sleeping_place_type' => 'single',
            'wants_wifi' => true,
            'wants_kitchen' => true,
            'wants_washing_machine' => true,
            'wants_locker' => true,
            'wants_lower_bunk' => false,
            'avoids_mixed_room' => false,
            'avoids_smoking' => true,
            'avoids_pets' => false,
            'needs_late_check_in' => false,
            'needs_workspace' => false,
            'needs_quiet_hours' => true,
            'accessibility_needs_json' => [],
        ];
    }
}
