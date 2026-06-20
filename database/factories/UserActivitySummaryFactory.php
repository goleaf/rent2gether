<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserActivitySummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserActivitySummary>
 */
class UserActivitySummaryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'completed_stays_as_guest' => 0,
            'completed_stays_as_host' => 0,
            'cancelled_by_guest_count' => 0,
            'cancelled_by_host_count' => 0,
            'no_show_count' => 0,
            'complaints_submitted_count' => 0,
            'complaints_received_count' => 0,
            'confirmed_complaints_count' => 0,
            'reviews_received_count' => 0,
            'reviews_left_count' => 0,
            'average_guest_rating' => null,
            'average_host_rating' => null,
            'average_response_time_minutes' => null,
            'last_activity_at' => null,
        ];
    }
}
