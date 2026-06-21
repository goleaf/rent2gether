<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingStay;
use App\Models\BookingStayOccupant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookingStayOccupant>
 */
class BookingStayOccupantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_stay_id' => BookingStay::factory(),
            'booking_id' => Booking::factory(),
            'user_id' => User::factory(),
            'occupant_name' => fake()->firstName(),
            'occupant_type' => 'main_guest',
            'is_main_guest' => true,
            'age_range' => fake()->randomElement(['18-24', '25-34', '35-44']),
            'gender' => fake()->randomElement(['female', 'male', null]),
            'public_gender_visible' => false,
            'city_name' => fake()->city(),
            'country_name' => fake()->country(),
            'languages_json' => ['en'],
            'stay_purpose' => fake()->randomElement(['tourist', 'student', 'work', 'long_term_resident']),
            'sleep_schedule' => fake()->randomElement(['wakes_up_early', 'sleeps_late', null]),
            'smoking_status' => fake()->randomElement(['does_not_smoke', 'smokes', null]),
            'sociability_level' => fake()->randomElement(['social', 'prefers_quiet', null]),
            'neighbor_rating_snapshot' => fake()->randomFloat(2, 3.5, 5.0),
            'public_visibility' => 'roommates_only',
        ];
    }
}
