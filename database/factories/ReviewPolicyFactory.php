<?php

namespace Database\Factories;

use App\Models\ReviewPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewPolicy>
 */
class ReviewPolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scope_type' => 'global',
            'scope_id' => null,
            'review_window_days' => 14,
            'edit_window_hours' => 24,
            'double_blind_enabled' => true,
            'publish_after_both_submitted' => true,
            'publish_after_window_expired' => true,
            'allow_review_photos' => true,
            'allow_host_response' => true,
            'allow_guest_response_future' => false,
            'minimum_stay_nights_for_review' => 1,
            'active' => true,
        ];
    }
}
