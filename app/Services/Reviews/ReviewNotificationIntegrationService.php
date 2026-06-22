<?php

namespace App\Services\Reviews;

use App\Models\Review;
use App\Models\ReviewRequest;
use App\Models\ReviewResponse;

class ReviewNotificationIntegrationService
{
    public function __construct(private readonly ReviewNotificationService $notifications) {}

    public function notifyGuestReviewRequested(ReviewRequest $request): void
    {
        $this->notifications->notifyGuestReviewRequested($request);
    }

    public function notifyHostReviewRequested(ReviewRequest $request): void
    {
        $this->notifications->notifyHostReviewRequested($request);
    }

    public function notifyReviewPublished(Review $review): void
    {
        $this->notifications->notifyReviewPublished($review);
    }

    public function notifyHostResponsePublished(ReviewResponse $response): void
    {
        $this->notifications->notifyHostResponsePublished($response);
    }
}
