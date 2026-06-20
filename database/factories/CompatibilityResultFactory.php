<?php

namespace Database\Factories;

use App\Models\CompatibilityResult;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompatibilityResult>
 */
class CompatibilityResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'property_id' => Property::factory(),
            'room_id' => Room::factory(),
            'sleeping_place_id' => SleepingPlace::factory(),
            'check_in_date' => now()->addMonth()->toDateString(),
            'check_out_date' => now()->addMonth()->addDays(3)->toDateString(),
            'nights_count' => 3,
            'compatibility_score' => 88,
            'fit_status' => 'great',
            'positive_reasons_json' => [
                ['key' => 'quiet_match', 'message' => __('compatibility.positive.quiet_match'), 'weight' => 10],
            ],
            'warning_reasons_json' => [],
            'blocking_reasons_json' => [],
            'calculated_at' => now(),
            'expires_at' => now()->addMinutes(30),
        ];
    }
}
