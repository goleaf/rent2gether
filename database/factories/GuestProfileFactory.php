<?php

namespace Database\Factories;

use App\Models\GuestProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestProfile>
 */
class GuestProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'travel_purpose_default' => 'work',
            'preferred_check_in_time' => '15:00',
            'preferred_check_out_time' => '11:00',
            'has_large_luggage' => false,
            'needs_luggage_storage' => false,
            'needs_quiet_place' => true,
            'needs_desk' => false,
            'needs_fast_wifi' => false,
            'needs_registration' => false,
            'needs_work_documents' => false,
            'smokes' => false,
            'travels_with_pet' => false,
            'pet_description' => null,
            'prefers_private_room' => false,
            'accepts_shared_room' => true,
            'accepts_living_with_strangers' => true,
            'max_people_in_room_preference' => 4,
            'long_stay_interested' => false,
            'short_stay_interested' => true,
            'night_schedule' => 'normal',
            'early_wakeup' => false,
            'late_sleep' => false,
            'works_remotely' => false,
            'studies' => false,
            'often_at_home' => false,
            'rarely_at_home' => true,
            'sociability_level' => 'neutral',
            'cleanliness_expectation' => 'normal',
            'ready_to_join_cleaning' => true,
        ];
    }
}
