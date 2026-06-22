<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\ReviewRequest;
use App\Models\ReviewResponse;

class ReviewNotificationService
{
    public function notifyGuestReviewRequested(ReviewRequest $request): void
    {
        $request->forceFill(['notification_sent_at' => $request->notification_sent_at ?: now()])->save();
    }

    public function notifyHostReviewRequested(ReviewRequest $request): void
    {
        $request->forceFill(['notification_sent_at' => $request->notification_sent_at ?: now()])->save();
    }

    public function notifyReviewPublished(Review $review): void
    {
        $review->events()->create([
            'event_key' => 'review_published',
            'event_type' => 'notification',
            'booking_id' => $review->booking_id,
            'occurred_at' => now(),
        ]);
    }

    public function notifyHostResponsePublished(ReviewResponse $response): void
    {
        $response->review?->events()->create([
            'event_key' => 'review_response_published',
            'event_type' => 'notification',
            'booking_id' => $response->review?->booking_id,
            'user_id' => $response->responder_user_id,
            'occurred_at' => now(),
        ]);
    }
}
