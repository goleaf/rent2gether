<?php

namespace Database\Factories;

use App\Models\GuestHintDismissal;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestHintDismissal>
 */
class GuestHintDismissalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'hint_key' => 'new_listing',
            'context' => 'card',
            'dismissed_at' => now(),
            'expires_at' => now()->addWeek(),
        ];
    }
}
