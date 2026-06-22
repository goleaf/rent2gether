<?php

namespace App\Services\Reviews;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\ReviewRequest;
use App\Models\User;
use Illuminate\Support\Collection;

class ReviewRequestService
{
    public function __construct(
        private readonly ReviewEligibilityService $eligibility,
        private readonly ReviewNumberService $numbers,
        private readonly ReviewPolicyService $policies,
        private readonly ReviewEventService $events,
    ) {}

    /**
     * @return Collection<int, ReviewRequest>
     */
    public function createRequestsAfterCheckout(BookingCheckOut $checkOut): Collection
    {
        $booking = $checkOut->booking()->firstOrFail();

        if (! $this->eligibility->bookingQualifiesForReview($booking)) {
            return collect();
        }

        return collect([
            $this->createGuestPlaceReviewRequest($booking, $checkOut),
            $this->createHostGuestReviewRequest($booking, $checkOut),
            $this->createRoommateExperienceReviewRequest($booking, $checkOut),
        ])->filter()->values();
    }

    public function createGuestPlaceReviewRequest(Booking $booking, ?BookingCheckOut $checkOut = null): ReviewRequest
    {
        return $this->createRequest($booking, $checkOut, [
            'request_type' => 'guest_reviews_place',
            'reviewer_user_id' => $this->guestId($booking),
            'reviewer_type' => 'guest',
            'review_subject_type' => 'sleeping_place',
            'review_subject_user_id' => $this->hostId($booking),
        ]);
    }

    public function createHostGuestReviewRequest(Booking $booking, ?BookingCheckOut $checkOut = null): ReviewRequest
    {
        return $this->createRequest($booking, $checkOut, [
            'request_type' => 'host_reviews_guest',
            'reviewer_user_id' => $this->hostId($booking),
            'reviewer_type' => 'host',
            'review_subject_type' => 'guest',
            'review_subject_user_id' => $this->guestId($booking),
        ]);
    }

    public function createRoommateExperienceReviewRequest(Booking $booking, ?BookingCheckOut $checkOut = null): ?ReviewRequest
    {
        return $this->createRequest($booking, $checkOut, [
            'request_type' => 'guest_reviews_roommates',
            'reviewer_user_id' => $this->guestId($booking),
            'reviewer_type' => 'guest',
            'review_subject_type' => 'roommates',
            'review_subject_user_id' => null,
        ]);
    }

    public function sendReviewRequest(ReviewRequest $request): void
    {
        $request->forceFill([
            'status' => 'sent',
            'notification_sent_at' => now(),
        ])->save();

        $this->events->recordRequest($request, 'review_request_sent');
    }

    public function expireOldRequestsForUser(User $user): int
    {
        $requests = ReviewRequest::query()
            ->where('reviewer_user_id', $user->id)
            ->whereIn('status', ['created', 'sent', 'opened', 'started'])
            ->where('due_at', '<=', now())
            ->get(['id', 'status']);

        $requests->each(fn (ReviewRequest $request) => $request->forceFill([
            'status' => 'expired',
            'expired_at' => now(),
        ])->save());

        return $requests->count();
    }

    public function cancelRequest(ReviewRequest $request, string $reason): ReviewRequest
    {
        $request->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        $this->events->recordRequest($request->refresh(), 'review_request_cancelled', ['reason' => $reason]);

        return $request->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRequest(Booking $booking, ?BookingCheckOut $checkOut, array $attributes): ReviewRequest
    {
        $policy = $this->policies->resolveForBooking($booking);
        $checkoutCompletedAt = $checkOut?->completed_at ?: $booking->checked_out_at ?: now();

        $request = ReviewRequest::query()->firstOrCreate(
            [
                'booking_id' => $booking->id,
                'request_type' => $attributes['request_type'],
                'reviewer_user_id' => $attributes['reviewer_user_id'],
            ],
            array_merge($attributes, [
                'review_request_number' => $this->numbers->generateReviewRequestNumber(),
                'booking_stay_id' => $checkOut?->booking_stay_id,
                'booking_check_out_id' => $checkOut?->id,
                'guest_user_id' => $this->guestId($booking),
                'host_user_id' => $this->hostId($booking),
                'property_id' => (int) $booking->property_id,
                'room_id' => (int) $booking->room_id,
                'sleeping_place_id' => (int) $booking->sleeping_place_id,
                'status' => 'created',
                'due_at' => $checkoutCompletedAt->copy()->addDays($policy->review_window_days),
            ]),
        );

        if ($request->wasRecentlyCreated) {
            $this->events->recordRequest($request, 'review_request_created');
        }

        return $request->refresh();
    }

    private function guestId(Booking $booking): int
    {
        return (int) ($booking->guest_user_id ?: $booking->guest_id);
    }

    private function hostId(Booking $booking): int
    {
        return (int) ($booking->host_user_id ?: $booking->host_id);
    }
}
