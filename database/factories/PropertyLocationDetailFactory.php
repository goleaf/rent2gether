<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\PropertyLocationDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyLocationDetail>
 */
class PropertyLocationDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'nearest_metro' => $this->faker->optional()->streetName(),
            'nearest_metro_distance_meters' => $this->faker->optional()->numberBetween(200, 2000),
            'nearest_metro_walk_minutes' => $this->faker->optional()->numberBetween(3, 25),
            'nearest_bus_stop' => $this->faker->streetName(),
            'nearest_bus_stop_distance_meters' => $this->faker->numberBetween(100, 1000),
            'nearest_bus_stop_walk_minutes' => $this->faker->numberBetween(2, 15),
            'nearest_tram_stop' => $this->faker->optional()->streetName(),
            'nearest_train_station' => $this->faker->optional()->city(),
            'nearest_railway_station' => $this->faker->optional()->city(),
            'railway_station_distance_meters' => $this->faker->optional()->numberBetween(500, 8000),
            'nearest_airport' => $this->faker->optional()->city().' Airport',
            'airport_distance_meters' => $this->faker->optional()->numberBetween(5000, 30000),
            'airport_transport_minutes' => $this->faker->optional()->numberBetween(20, 90),
            'nearest_shop' => $this->faker->streetName().' shop',
            'nearest_supermarket' => $this->faker->company(),
            'nearest_pharmacy' => $this->faker->company(),
            'nearest_hospital' => $this->faker->optional()->company(),
            'nearest_clinic' => $this->faker->optional()->company(),
            'nearest_university' => $this->faker->optional()->company(),
            'nearest_school' => $this->faker->optional()->company(),
            'nearest_gym' => $this->faker->optional()->company(),
            'nearest_park' => $this->faker->optional()->streetName().' park',
            'nearest_mall' => $this->faker->optional()->company(),
            'nearest_cafe' => $this->faker->optional()->company(),
            'nearest_laundry' => $this->faker->optional()->company(),
            'nearest_atm' => $this->faker->optional()->company(),
            'nearest_coworking' => $this->faker->optional()->company(),
            'nearest_landmark' => $this->faker->optional()->company(),
            'near_work_area' => $this->faker->boolean(30),
            'near_sea' => $this->faker->boolean(10),
            'near_nightlife' => $this->faker->boolean(25),
            'area_residential' => $this->faker->boolean(65),
            'area_city_center' => $this->faker->boolean(30),
            'area_suburb' => $this->faker->boolean(20),
            'area_industrial' => $this->faker->boolean(8),
            'area_tourist' => $this->faker->boolean(25),
            'area_students' => $this->faker->boolean(20),
            'area_workers' => $this->faker->boolean(25),
            'area_long_stay' => $this->faker->boolean(35),
            'distance_to_center_meters' => $this->faker->numberBetween(500, 9000),
            'walk_minutes_to_center' => $this->faker->numberBetween(5, 90),
            'transport_minutes_to_center' => $this->faker->numberBetween(5, 45),
            'car_minutes_to_center' => $this->faker->numberBetween(5, 35),
            'transport_convenience_level' => $this->faker->randomElement(['low', 'moderate', 'good', 'high']),
            'has_night_transport' => $this->faker->boolean(45),
            'easy_to_reach_with_luggage' => $this->faker->boolean(70),
            'district_noise_level' => $this->faker->randomElement(['quiet', 'low', 'moderate', 'high']),
            'district_safety_level' => $this->faker->randomElement(['moderate', 'good', 'high']),
            'street_lighting_level' => $this->faker->randomElement(['low', 'moderate', 'good', 'high']),
            'street_busy_at_night' => $this->faker->boolean(45),
            'has_street_noise' => $this->faker->boolean(35),
            'has_bar_noise' => $this->faker->boolean(15),
            'has_train_noise' => $this->faker->boolean(10),
            'has_construction_nearby' => $this->faker->boolean(10),
            'has_parking_nearby' => $this->faker->boolean(60),
            'has_free_parking' => $this->faker->boolean(35),
            'has_paid_parking' => $this->faker->boolean(50),
            'has_private_parking' => $this->faker->boolean(20),
            'has_yard_parking' => $this->faker->boolean(30),
            'has_underground_parking' => $this->faker->boolean(10),
            'has_bicycle_parking' => $this->faker->boolean(45),
            'parking_permit_required' => $this->faker->boolean(20),
            'parking_usually_full' => $this->faker->boolean(25),
        ];
    }
}
