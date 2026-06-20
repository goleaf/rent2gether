<?php

namespace Database\Factories;

use App\Enums\GenderType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => Property::factory(),
            'title' => 'Room '.$this->faker->numberBetween(1, 20),
            'gender_type' => GenderType::Mixed->value,
            'description' => $this->faker->sentence(),
            'capacity' => $this->faker->numberBetween(2, 8),
            'area_sqm' => $this->faker->randomFloat(1, 10, 40),
            'has_lock' => $this->faker->boolean(),
            'has_window' => true,
            'has_wardrobe' => $this->faker->boolean(),
            'has_desk' => $this->faker->boolean(),
            'has_ac' => $this->faker->boolean(),
            'has_heating' => true,
            'has_balcony' => $this->faker->boolean(20),
            'status' => RoomStatus::Active->value,
            'type' => RoomType::Shared->value,
            'room_type' => RoomType::Shared->value,
            'living_format' => 'long_stay',
            'internal_name' => null,
            'is_private' => false,
            'is_shared' => true,
            'is_pass_through' => false,
            'is_for_one_person' => false,
            'is_for_couples' => false,
            'is_for_groups' => false,
            'is_for_long_stay' => true,
            'is_for_short_stay' => true,
            'room_number' => (string) $this->faker->numberBetween(1, 50),
            'floor' => $this->faker->numberBetween(1, 8),
            'area' => $this->faker->randomFloat(2, 10, 40),
            'beds_count' => $this->faker->numberBetween(1, 6),
            'sleeping_places_count' => 4,
            'active_sleeping_places_count' => 4,
            'max_guests' => $this->faker->numberBetween(1, 6),
            'current_guests_count' => 0,
            'permanent_residents_count' => 0,
            'short_term_guests_count' => 0,
            'occupied_places_count' => 0,
            'available_places_count' => $this->faker->numberBetween(1, 6),
            'occupied_sleeping_places_count' => 0,
            'free_sleeping_places_count' => 4,
            'unavailable_sleeping_places_count' => 0,
            'gender_policy' => GenderType::Mixed->value,
            'min_guest_age' => 18,
            'max_guest_age' => null,
            'can_book_entire_room' => false,
            'can_book_individual_places' => true,
            'sort_order' => 0,
            'windows_count' => 1,
            'window_view' => 'street',
            'has_chair' => true,
            'has_mirror' => true,
            'has_air_conditioning' => false,
            'has_curtains' => true,
            'has_blackout_curtains' => false,
            'noise_level' => 'moderate',
            'light_level' => 'moderate',
            'ventilation_level' => 'good',
            'can_eat' => false,
            'can_work_at_night' => false,
            'can_turn_light_at_night' => false,
            'can_talk_at_night' => false,
            'room_rules_text' => null,
        ];
    }
}
