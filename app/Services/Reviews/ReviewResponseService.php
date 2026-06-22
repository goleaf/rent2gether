<?php

namespace App\Services\Reviews;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Review;
use App\Models\ReviewResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReviewResponseService
{
    public function respondToReview(User $user, Review $review, string $text): ReviewResponse
    {
        if (! $this->canRespond($user, $review)) {
            throw ValidationException::withMessages([
                'review' => __('reviews.validation.response_not_allowed'),
            ]);
        }

        return ReviewResponse::query()->create([
            'review_id' => $review->id,
            'responder_user_id' => $user->id,
            'responder_type' => 'host',
            'status' => ReviewStatus::Submitted->value,
            'response_text' => $text,
            'is_public' => true,
            'submitted_at' => now(),
        ]);
    }

    public function publishResponse(ReviewResponse $response): ReviewResponse
    {
        $response->forceFill([
            'status' => ReviewStatus::Published->value,
            'is_public' => true,
            'published_at' => now(),
        ])->save();

        return $response->refresh();
    }

    public function hideResponse(ReviewResponse $response, string $reason): ReviewResponse
    {
        $response->forceFill([
            'status' => ReviewStatus::Hidden->value,
            'is_public' => false,
            'hidden_at' => now(),
        ])->save();

        return $response->refresh();
    }

    private function canRespond(User $user, Review $review): bool
    {
        return (int) $review->host_user_id === (int) $user->id
            && $review->type === ReviewType::GuestToPlace
            && $review->status === ReviewStatus::Published
            && $review->is_public;
    }
}
