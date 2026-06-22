<?php

namespace App\Services\Reviews;

use App\Models\RatingEvent;
use App\Models\Review;
use App\Models\ReviewRequest;

class ReviewNumberService
{
    public function generateReviewNumber(): string
    {
        return $this->nextNumber('REV', Review::class, 'review_number');
    }

    public function generateReviewRequestNumber(): string
    {
        return $this->nextNumber('REVR', ReviewRequest::class, 'review_request_number');
    }

    public function generateRatingEventNumber(): string
    {
        return $this->nextNumber('RATE', RatingEvent::class, 'rating_event_number');
    }

    public function ensureUnique(string $number): string
    {
        $exists = Review::query()->where('review_number', $number)->exists()
            || ReviewRequest::query()->where('review_request_number', $number)->exists()
            || RatingEvent::query()->where('rating_event_number', $number)->exists();

        if (! $exists) {
            return $number;
        }

        $prefix = str($number)->before('-')->toString();

        return match ($prefix) {
            'REVR' => $this->generateReviewRequestNumber(),
            'RATE' => $this->generateRatingEventNumber(),
            default => $this->generateReviewNumber(),
        };
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    private function nextNumber(string $prefix, string $model, string $column): string
    {
        $year = now()->format('Y');
        $latest = $model::query()
            ->where($column, 'like', $prefix.'-'.$year.'-%')
            ->orderByDesc($column)
            ->value($column);

        $next = $latest ? ((int) str($latest)->afterLast('-')->toString()) + 1 : 1;

        return sprintf('%s-%s-%06d', $prefix, $year, $next);
    }
}
