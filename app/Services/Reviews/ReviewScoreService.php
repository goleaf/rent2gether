<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\ReviewScore;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReviewScoreService
{
    /**
     * @param  array<string, int|float|string|null>  $scores
     * @return Collection<int, ReviewScore>
     */
    public function createScores(Review $review, array $scores): Collection
    {
        return collect($scores)
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->map(function ($value, string $scoreKey) use ($review): ReviewScore {
                if (! $this->validateScoreValue($scoreKey, (float) $value)) {
                    throw ValidationException::withMessages([
                        'scores.'.$scoreKey => __('reviews.validation.score_between'),
                    ]);
                }

                return ReviewScore::query()->create([
                    'review_id' => $review->id,
                    'score_key' => $scoreKey,
                    'score_value' => (float) $value,
                    'max_score' => 5,
                    'weight' => 1,
                    'is_public' => true,
                ]);
            })
            ->values();
    }

    /**
     * @param  array<string, int|float|string|null>  $scores
     * @return Collection<int, ReviewScore>
     */
    public function updateScores(Review $review, array $scores): Collection
    {
        ReviewScore::query()->where('review_id', $review->id)->delete();

        return $this->createScores($review, $scores);
    }

    /**
     * @return Collection<int, ReviewScore>
     */
    public function getPublicScores(Review $review): Collection
    {
        return ReviewScore::query()
            ->where('review_id', $review->id)
            ->where('is_public', true)
            ->orderBy('id')
            ->get();
    }

    public function validateScoreValue(string $scoreKey, int|float $value): bool
    {
        return $scoreKey !== '' && $value >= 1 && $value <= 5;
    }
}
