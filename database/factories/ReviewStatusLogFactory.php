<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewStatusLog>
 */
class ReviewStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'user_id' => User::factory(),
            'old_status' => null,
            'new_status' => 'submitted',
            'reason_key' => null,
            'note' => null,
            'context_json' => [],
        ];
    }
}
