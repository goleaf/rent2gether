<?php

namespace Database\Factories;

use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCalendarBlock>
 */
class SleepingPlaceCalendarBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = now()->addDays(7)->startOfDay();

        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'room_id' => null,
            'property_id' => null,
            'booking_id' => null,
            'source_type' => 'factory',
            'source_id' => null,
            'block_type' => 'closed_by_host',
            'status' => 'active',
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays(2),
            'check_in_date' => $start->toDateString(),
            'check_out_date' => $start->copy()->addDays(2)->toDateString(),
            'reason_key' => 'closed_by_host',
            'visible_to_guest' => false,
            'created_by_user_id' => null,
            'released_at' => null,
        ];
    }

    public function repair(): static
    {
        return $this->state([
            'block_type' => 'repair',
            'reason_key' => 'repair',
        ]);
    }

    public function complaint(): static
    {
        return $this->state([
            'block_type' => 'complaint',
            'reason_key' => 'unavailable_complaint',
        ]);
    }
}
