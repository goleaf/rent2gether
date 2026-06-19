<?php

namespace Database\Factories;

use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SleepingPlace>
 */
class SleepingPlaceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'property_id' => Property::factory(),
            'type' => SleepingPlaceType::Single->value,
            'status' => SleepingPlaceStatus::Active->value,
            'place_number' => (string) $this->faker->numberBetween(1, 20),
            'bunk_level' => null,
            'length_cm' => 200,
            'width_cm' => 90,
            'mattress_type' => 'foam',
            'mattress_firmness' => 'medium',
            'has_pillow' => true,
            'has_blanket' => true,
            'has_bedding' => true,
            'has_towel' => false,
            'has_curtain' => false,
            'has_lamp' => true,
            'has_power_socket' => true,
            'has_usb' => false,
            'has_shelf' => true,
            'has_locker' => true,
            'locker_has_lock' => true,
            'has_luggage_space' => true,
            'is_accessible' => false,
            'suitable_for_tall_person' => true,
            'suitable_for_elderly' => false,
            'max_guests' => 1,
            'min_guest_age' => 18,
            'max_guest_age' => null,
            'base_price_per_night' => $this->faker->randomFloat(2, 12, 45),
            'weekly_price' => null,
            'monthly_price' => null,
            'weekend_price' => null,
            'holiday_price' => null,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
            'currency' => 'EUR',
            'min_nights' => 1,
            'max_nights' => null,
            'instant_booking_enabled' => false,
            'requires_host_approval' => true,
        ];
    }
}
