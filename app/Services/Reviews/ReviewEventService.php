<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\ReviewEvent;
use App\Models\ReviewRequest;
use Illuminate\Support\Collection;

class ReviewEventService
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(?Review $review, string $eventKey, array $context = []): ReviewEvent
    {
        return ReviewEvent::query()->create([
            'review_id' => $review?->id,
            'review_request_id' => $review?->review_request_id,
            'booking_id' => $review?->booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? 'review',
            'source_id' => $context['source_id'] ?? $review?->id,
            'user_id' => $context['user_id'] ?? $review?->author_user_id,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function recordRequest(ReviewRequest $request, string $eventKey, array $context = []): ReviewEvent
    {
        return ReviewEvent::query()->create([
            'review_id' => null,
            'review_request_id' => $request->id,
            'booking_id' => $request->booking_id,
            'event_key' => $eventKey,
            'event_type' => $context['event_type'] ?? 'system',
            'source_type' => $context['source_type'] ?? 'review_request',
            'source_id' => $context['source_id'] ?? $request->id,
            'user_id' => $context['user_id'] ?? $request->reviewer_user_id,
            'occurred_at' => $context['occurred_at'] ?? now(),
            'context_json' => $context,
        ]);
    }

    /**
     * Returns timeline events for a review.
     *
     * @return Collection<int, ReviewEvent>
     */
    public function getTimeline(Review $review): Collection
    {
        return ReviewEvent::query()
            ->where('review_id', $review->id)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();
    }
}
