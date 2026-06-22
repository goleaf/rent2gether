<?php

namespace App\Services\Reviews;

use App\Enums\ReviewStatus;
use App\Models\Booking;
use App\Models\Review;
use App\Models\ReviewRequest;
use App\Models\User;

class ReviewPrivacyService
{
    public function canGuestCreate(User $guest, ReviewRequest $request): bool
    {
        return $request->reviewer_type === 'guest'
            && (int) $request->reviewer_user_id === (int) $guest->id
            && ! in_array($request->status, ['submitted', 'expired', 'cancelled', 'closed'], true);
    }

    public function canHostCreate(User $host, ReviewRequest $request): bool
    {
        return $request->reviewer_type === 'host'
            && (int) $request->reviewer_user_id === (int) $host->id
            && ! in_array($request->status, ['submitted', 'expired', 'cancelled', 'closed'], true);
    }

    public function canViewReview(User $user, Review $review): bool
    {
        return $this->canViewPublicReview($user, $review)
            || in_array((int) $user->id, [(int) $review->author_user_id, (int) $review->target_user_id, (int) $review->guest_user_id, (int) $review->host_user_id], true);
    }

    public function canViewPublicReview(?User $user, Review $review): bool
    {
        return $review->status === ReviewStatus::Published && $review->is_public;
    }

    public function canViewPrivateComment(User $user, Review $review): bool
    {
        return (int) $review->author_user_id === (int) $user->id;
    }

    public function canViewGuestReputation(User $host, User $guest, ?Booking $booking = null): bool
    {
        if ($booking === null) {
            return false;
        }

        return in_array((int) $host->id, [(int) $booking->host_user_id, (int) $booking->host_id], true)
            && in_array((int) $guest->id, [(int) $booking->guest_user_id, (int) $booking->guest_id], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterReviewForPublic(Review $review): array
    {
        if (! $this->canViewPublicReview(null, $review)) {
            return [];
        }

        $review->loadMissing(['scores', 'responses', 'roommateExperience']);

        return array_filter([
            'review_number' => $review->review_number,
            'target_type' => $review->target_type,
            'review_subject_type' => $review->review_subject_type,
            'overall_rating' => $review->overall_rating,
            'title' => $review->title,
            'public_comment' => $review->public_comment,
            'what_liked' => $review->what_liked,
            'what_disliked' => $review->what_disliked,
            'advice_to_future_guests' => $review->advice_to_future_guests,
            'recommend' => $review->recommend,
            'published_at' => $review->published_at,
            'scores' => $review->scores
                ->where('is_public', true)
                ->map(fn ($score): array => [
                    'score_key' => $score->score_key,
                    'score_value' => (float) $score->score_value,
                    'max_score' => (float) $score->max_score,
                ])
                ->values()
                ->all(),
            'responses' => $review->responses
                ->where('is_public', true)
                ->where('status', ReviewStatus::Published->value)
                ->map(fn ($response): array => [
                    'response_text' => $response->response_text,
                    'published_at' => $response->published_at,
                ])
                ->values()
                ->all(),
            'roommate_summary' => $review->roommateExperience ? [
                'quiet_roommates' => $review->roommateExperience->quiet_roommates,
                'clean_roommates' => $review->roommateExperience->clean_roommates,
                'friendly_roommates' => $review->roommateExperience->friendly_roommates,
                'roommate_experience_rating' => $review->roommateExperience->roommate_experience_rating,
            ] : null,
        ], fn ($value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterReviewForGuest(User $guest, Review $review): array
    {
        $data = $this->filterReviewForPublic($review);

        if ((int) $review->author_user_id === (int) $guest->id) {
            $data['private_comment'] = $review->private_comment;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterReviewForHost(User $host, Review $review): array
    {
        return $this->filterReviewForPublic($review);
    }
}
