<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\RatingEvent;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RatingEvent>
 */
class RatingEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating_event_number' => sprintf('RATE-%s-%06d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 999999)),
            'source_type' => 'review',
            'source_id' => 1,
            'event_key' => 'review_submitted',
            'event_type' => 'system',
            'target_type' => 'sleeping_place',
            'target_user_id' => User::factory(),
            'property_id' => null,
            'room_id' => null,
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => Booking::factory(),
            'booking_stay_id' => null,
            'metric_key' => 'overall',
            'impact_direction' => 'positive',
            'impact_value' => 5,
            'weight' => 1,
            'confirmed' => true,
            'frozen' => false,
            'ignored' => false,
            'reason_key' => null,
            'note' => null,
        ];
    }
}
