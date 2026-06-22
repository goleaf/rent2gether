<?php

namespace Database\Factories;

use App\Models\GuestReputationSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestReputationSnapshot>
 */
class GuestReputationSnapshotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'guest_user_id' => User::factory(),
            'overall_rating' => 5,
            'rules_respect_rating' => 5,
            'cleanliness_rating' => 5,
            'communication_rating' => 5,
            'punctuality_rating' => 5,
            'respect_for_roommates_rating' => 5,
            'care_for_property_rating' => 5,
            'payment_reliability_rating' => 5,
            'reviews_count' => 1,
            'completed_stays_count' => 1,
            'confirmed_no_show_count' => 0,
            'guest_cancellations_count' => 0,
            'confirmed_deposit_deductions_count' => 0,
            'confirmed_complaints_count' => 0,
            'resolved_complaints_count' => 0,
            'recommended_by_hosts_count' => 1,
            'not_recommended_by_hosts_count' => 0,
            'last_recalculated_at' => now(),
        ];
    }
}
