<?php

namespace Database\Factories;

use App\Models\HostReputationSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HostReputationSnapshot>
 */
class HostReputationSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host_user_id' => User::factory(),
            'overall_rating' => 5,
            'response_speed_rating' => 5,
            'description_accuracy_rating' => 5,
            'cleanliness_rating' => 5,
            'problem_resolution_rating' => 5,
            'honesty_rating' => 5,
            'hospitality_rating' => 5,
            'check_in_quality_rating' => 5,
            'checkout_quality_rating' => 5,
            'reviews_count' => 1,
            'completed_stays_count' => 1,
            'successful_check_ins_count' => 1,
            'host_cancellations_count' => 0,
            'confirmed_host_unresponsive_count' => 0,
            'confirmed_complaints_count' => 0,
            'resolved_complaints_count' => 0,
            'average_response_minutes' => null,
            'verified_host' => false,
            'trusted_host_future' => false,
            'last_recalculated_at' => now(),
        ];
    }
}
