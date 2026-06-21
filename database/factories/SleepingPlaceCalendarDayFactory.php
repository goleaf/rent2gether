<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlaceCalendarDay>
 */
class SleepingPlaceCalendarDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'date' => $this->faker->dateTimeBetween('+1 week', '+2 months')->format('Y-m-d'),
            'status' => 'available',
            'price' => 20,
            'price_override' => null,
            'currency' => 'EUR',
            'min_nights' => null,
            'max_nights' => null,
            'check_in_allowed' => true,
            'check_out_allowed' => true,
            'reason' => null,
            'reason_key' => null,
            'source' => 'factory',
            'source_type' => 'factory',
            'source_id' => null,
            'note' => null,
            'booking_id' => null,
            'blocked_by_host' => false,
        ];
    }

    public function booked(): static
    {
        return $this->state([
            'booking_id' => Booking::factory(),
            'status' => 'booked',
            'check_in_allowed' => false,
            'check_out_allowed' => false,
        ]);
    }
}
