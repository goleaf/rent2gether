<?php

namespace App\Services\Reviews;

use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Collection;

class ReviewPublishingService
{
    public function __construct(
        private readonly RatingEventService $ratingEvents,
        private readonly RatingSnapshotService $snapshots,
        private readonly ReviewEventService $events,
    ) {}

    public function shouldPublishNow(Review $review): bool
    {
        if (! $review->is_double_blind) {
            return true;
        }

        return $this->bookingHasBothSidesSubmitted($review->booking)
            || $this->reviewWindowExpired($review->booking);
    }

    public function publishReview(Review $review): Review
    {
        $review->forceFill([
            'status' => ReviewStatus::Published,
            'is_public' => true,
            'published_at' => $review->published_at ?: now(),
            'visible_at' => $review->visible_at ?: now(),
        ])->save();

        $review = $review->refresh();
        $this->ratingEvents->createFromReview($review);
        $this->refreshSnapshotsForReview($review);
        $this->events->record($review, 'review_published');

        return $review;
    }

    /**
     * @return Collection<int, Review>
     */
    public function publishPairIfReady(Booking $booking): Collection
    {
        if (! $this->bookingHasBothSidesSubmitted($booking) && ! $this->reviewWindowExpired($booking)) {
            return collect();
        }

        return $booking->reviews()
            ->whereIn('status', [ReviewStatus::Submitted->value, ReviewStatus::WaitingOtherParty->value, ReviewStatus::Pending->value, ReviewStatus::PendingPublish->value])
            ->get()
            ->map(fn (Review $review): Review => $this->publishReview($review))
            ->values();
    }

    /**
     * @return Collection<int, Review>
     */
    public function publishAfterWindowExpired(Booking $booking): Collection
    {
        if (! $this->reviewWindowExpired($booking)) {
            return collect();
        }

        return $booking->reviews()
            ->whereIn('status', [ReviewStatus::Submitted->value, ReviewStatus::WaitingOtherParty->value, ReviewStatus::Pending->value, ReviewStatus::PendingPublish->value])
            ->get()
            ->map(function (Review $review): Review {
                $review->forceFill(['is_published_after_window' => true])->save();

                return $this->publishReview($review->refresh());
            })
            ->values();
    }

    public function holdUntilOtherPartyOrDeadline(Review $review): Review
    {
        $review->forceFill([
            'status' => ReviewStatus::WaitingOtherParty,
            'is_public' => false,
        ])->save();

        $this->events->record($review->refresh(), 'review_waiting_other_party');

        return $review->refresh();
    }

    private function bookingHasBothSidesSubmitted(?Booking $booking): bool
    {
        if (! $booking) {
            return false;
        }

        $types = $booking->reviews()
            ->whereIn('type', [ReviewType::GuestToPlace->value, ReviewType::HostToGuest->value])
            ->whereIn('status', [ReviewStatus::Submitted->value, ReviewStatus::WaitingOtherParty->value, ReviewStatus::Pending->value, ReviewStatus::Published->value])
            ->pluck('type')
            ->map(fn ($type): string => $type instanceof ReviewType ? $type->value : (string) $type)
            ->unique()
            ->values();

        return $types->contains(ReviewType::GuestToPlace->value)
            && $types->contains(ReviewType::HostToGuest->value);
    }

    private function reviewWindowExpired(?Booking $booking): bool
    {
        return $booking?->review_deadline_at !== null
            && $booking->review_deadline_at->lessThanOrEqualTo(now());
    }

    private function refreshSnapshotsForReview(Review $review): void
    {
        if ($review->sleepingPlace) {
            $this->snapshots->recalculateSleepingPlace($review->sleepingPlace);
        }

        if ($review->room) {
            $this->snapshots->recalculateRoom($review->room);
        }

        if ($review->property) {
            $this->snapshots->recalculateProperty($review->property);
        }

        if ($review->target_type === 'host' && $review->target) {
            $this->snapshots->recalculateHost($review->target);
        }

        if ($review->target_type === 'guest' && $review->target) {
            $this->snapshots->recalculateGuest($review->target);
        }
    }
}
