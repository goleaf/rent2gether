<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PropertyAddress> */
class PropertyAddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'country_id' => null,
            'city_id' => null,
            'district_id' => null,
            'street_name' => $this->faker->streetName(),
            'house_number' => $this->faker->buildingNumber(),
            'apartment_number' => (string) $this->faker->numberBetween(1, 80),
            'postal_code' => $this->faker->postcode(),
            'floor' => $this->faker->numberBetween(1, 8),
            'latitude' => 54.7000000,
            'longitude' => 25.2700000,
            'approximate_latitude' => 54.7010000,
            'approximate_longitude' => 25.2710000,
            'public_location_label' => 'Central area',
            'show_exact_address_after_booking' => true,
            'show_street_before_booking' => false,
            'show_district_before_booking' => true,
        ];
    }
}
