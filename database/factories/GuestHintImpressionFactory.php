<?php

namespace Database\Factories;

use App\Models\GuestHintImpression;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestHintImpression>
 */
class GuestHintImpressionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'hint_key' => 'new_listing',
            'context' => 'card',
            'shown_at' => now(),
            'clicked_at' => null,
            'dismissed_at' => null,
        ];
    }
}
