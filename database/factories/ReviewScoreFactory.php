<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewScore>
 */
class ReviewScoreFactory extends Factory
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
            'score_key' => 'overall',
            'score_value' => 5,
            'max_score' => 5,
            'weight' => 1,
            'is_public' => true,
        ];
    }
}
