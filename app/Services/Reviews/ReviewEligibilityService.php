<?php

namespace App\Services\Reviews;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class ReviewEligibilityService
{
    public function canGuestReviewBooking(User $guest, Booking $booking): bool
    {
        return $this->bookingQualifiesForReview($booking)
            && in_array((int) $guest->id, $this->guestIds($booking), true)
            && $this->isReviewWindowOpen($booking);
    }

    public function canHostReviewGuest(User $host, Booking $booking): bool
    {
        return $this->bookingQualifiesForReview($booking)
            && in_array((int) $host->id, $this->hostIds($booking), true)
            && $this->isReviewWindowOpen($booking);
    }

    public function bookingQualifiesForReview(Booking $booking): bool
    {
        if (! $this->hasCompletedStay($booking)) {
            return false;
        }

        $status = $this->statusValue($booking);

        return ! in_array($status, [
            BookingStatus::NoShow->value,
            BookingStatus::CancelledByGuestFlow->value,
            BookingStatus::CancelledByHostFlow->value,
            BookingStatus::CancelledByServiceFuture->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
        ], true);
    }

    public function hasCompletedStay(Booking $booking): bool
    {
        return $this->statusValue($booking) === BookingStatus::Completed->value
            && ($booking->checked_in_at !== null || $booking->guest_check_in_confirmed_at !== null || $booking->guest_checked_in_at !== null)
            && ($booking->checked_out_at !== null || $booking->guest_check_out_confirmed_at !== null || $booking->guest_checked_out_at !== null);
    }

    public function isReviewWindowOpen(Booking $booking): bool
    {
        if ($booking->review_deadline_at === null) {
            return true;
        }

        return $booking->review_deadline_at->greaterThan(now());
    }

    /**
     * @return list<int>
     */
    private function guestIds(Booking $booking): array
    {
        return array_values(array_unique(array_filter([
            (int) $booking->guest_user_id,
            (int) $booking->guest_id,
        ])));
    }

    /**
     * @return list<int>
     */
    private function hostIds(Booking $booking): array
    {
        return array_values(array_unique(array_filter([
            (int) $booking->host_user_id,
            (int) $booking->host_id,
        ])));
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
