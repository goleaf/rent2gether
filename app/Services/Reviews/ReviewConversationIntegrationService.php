<?php

namespace App\Services\Reviews;

use App\Models\Booking;
use App\Models\Review;
use App\Models\ReviewRequest;

class ReviewConversationIntegrationService
{
    public function __construct(private readonly ReviewEventService $events) {}

    public function addReviewRequestedEvent(ReviewRequest $request): void
    {
        $this->events->recordRequest($request, 'review_request_created');
    }

    public function addReviewSubmittedEvent(Review $review): void
    {
        $this->events->record($review, 'review_submitted');
    }

    public function addReviewsPublishedEvent(Booking $booking): void
    {
        $booking->reviews()
            ->where('status', 'published')
            ->get(['id'])
            ->each(fn (Review $review) => $this->events->record($review, 'reviews_published'));
    }
}
