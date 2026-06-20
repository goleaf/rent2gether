<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Models\AvailabilityDay;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AvailabilityDay>
 */
class AvailabilityDayFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sleeping_place_id' => SleepingPlace::factory(),
            'booking_id' => null,
            'date' => $this->faker->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'status' => AvailabilityStatus::Available->value,
            'price_override' => null,
            'min_nights_override' => null,
            'max_nights_override' => null,
            'check_in_allowed' => true,
            'check_out_allowed' => true,
            'note' => null,
        ];
    }
}
