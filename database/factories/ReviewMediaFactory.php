<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\ReviewMedia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReviewMedia>
 */
class ReviewMediaFactory extends Factory
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
            'uploaded_by_user_id' => User::factory(),
            'media_type' => 'photo',
            'media_role' => 'positive_photo',
            'path' => 'review-photos/demo.webp',
            'thumbnail_path' => 'review-photos/demo-thumb.webp',
            'caption' => null,
            'visibility' => 'public',
            'approved_for_public_display' => true,
            'public_display_at' => now(),
        ];
    }
}
