<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceAvailabilityStatusLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceAvailabilityStatusLog>
 */
class SleepingPlaceAvailabilityStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'date' => now()->addDays(7)->toDateString(),
            'old_status' => 'available',
            'new_status' => 'closed_by_host',
            'source_type' => 'factory',
            'source_id' => null,
            'user_id' => null,
            'note' => null,
        ];
    }
}
