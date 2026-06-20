<?php

namespace Database\Factories;

use App\Enums\PropertyRentalUnitType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Property>
 */
class PropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'host_user_id' => User::factory(),
            'rental_unit_type' => PropertyRentalUnitType::SleepingPlace->value,
            'country_id' => Country::factory(),
            'region_id' => Region::factory(),
            'region_name' => 'Vilnius County',
            'city_id' => City::factory(),
            'title' => $this->faker->sentence(3),
            'type' => $this->faker->randomElement(PropertyType::cases())->value,
            'property_type' => PropertyType::Apartment->value,
            'property_subtype' => $this->faker->optional()->randomElement(['shared_flat', 'private_flat', 'family_house']),
            'description' => $this->faker->paragraph(),
            'country' => 'Lithuania',
            'city' => $this->faker->city(),
            'district' => $this->faker->word(),
            'street' => $this->faker->streetName(),
            'building' => $this->faker->buildingNumber(),
            'entrance' => (string) $this->faker->numberBetween(1, 5),
            'floor' => $this->faker->numberBetween(1, 10),
            'has_elevator' => $this->faker->boolean(),
            'lat' => $this->faker->latitude(54.5, 55.0),
            'lng' => $this->faker->longitude(25.0, 25.5),
            'show_exact_address' => false,
            'nearest_transport' => $this->faker->streetName(),
            'distance_to_transport_meters' => $this->faker->numberBetween(100, 1200),
            'amenities' => ['wifi', 'kitchen', 'washer'],
            'rules' => ['no_smoking', 'quiet_hours'],
            'status' => PropertyStatus::Active->value,
            'address_line_1' => $this->faker->streetAddress(),
            'house_number' => $this->faker->buildingNumber(),
            'postal_code' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(54.5, 55.0),
            'longitude' => $this->faker->longitude(25.0, 25.5),
            'approximate_latitude' => $this->faker->latitude(54.5, 55.0),
            'approximate_longitude' => $this->faker->longitude(25.0, 25.5),
            'show_exact_address_before_booking' => false,
            'show_exact_address_after_confirmation' => true,
            'show_exact_address_after_payment' => true,
            'show_only_approximate_location' => true,
            'distance_to_center_meters' => $this->faker->numberBetween(500, 8000),
            'total_area' => $this->faker->randomFloat(2, 35, 120),
            'living_area' => $this->faker->randomFloat(2, 25, 90),
            'rooms_count' => $this->faker->numberBetween(1, 5),
            'bedrooms_count' => $this->faker->numberBetween(1, 4),
            'shared_rooms_count' => $this->faker->numberBetween(0, 2),
            'pass_through_rooms_count' => $this->faker->numberBetween(0, 1),
            'bathrooms_count' => 1,
            'showers_count' => 1,
            'kitchens_count' => 1,
            'balconies_count' => $this->faker->numberBetween(0, 2),
            'max_guests' => $this->faker->numberBetween(1, 8),
            'current_guests_count' => 0,
            'max_residents' => $this->faker->numberBetween(1, 10),
            'current_residents_count' => 0,
            'permanent_residents_count' => 0,
            'short_term_guests_count' => 0,
            'active_rooms_count' => 0,
            'active_sleeping_places_count' => 0,
            'free_sleeping_places_count' => 0,
            'occupied_sleeping_places_count' => 0,
            'unavailable_sleeping_places_count' => 0,
            'can_book_whole_property' => false,
            'can_book_private_room' => true,
            'can_book_sleeping_place' => true,
            'noise_level' => 'moderate',
            'cleanliness_level' => 'good',
            'safety_level' => 'good',
            'repair_state' => 'good',
            'has_heating' => true,
            'has_air_conditioning' => false,
            'has_hot_water' => true,
            'has_parking' => $this->faker->boolean(30),
            'has_security' => $this->faker->boolean(40),
            'has_cctv_common_areas' => $this->faker->boolean(25),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
        ];
    }
}
