<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingReviewRequest;

class BookingReviewRequestService
{
    public function createGuestReviewRequest(BookingCheckOut $checkOut): BookingReviewRequest
    {
        return BookingReviewRequest::query()->firstOrCreate(
            [
                'booking_id' => $checkOut->booking_id,
                'reviewer_user_id' => $checkOut->guest_user_id,
                'reviewer_role' => 'guest',
            ],
            [
                'reviewee_user_id' => $checkOut->host_user_id,
                'status' => 'pending',
                'requested_at' => now(),
                'expires_at' => now()->addDays(14),
            ],
        );
    }

    public function createHostReviewRequest(BookingCheckOut $checkOut): BookingReviewRequest
    {
        return BookingReviewRequest::query()->firstOrCreate(
            [
                'booking_id' => $checkOut->booking_id,
                'reviewer_user_id' => $checkOut->host_user_id,
                'reviewer_role' => 'host',
            ],
            [
                'reviewee_user_id' => $checkOut->guest_user_id,
                'status' => 'pending',
                'requested_at' => now(),
                'expires_at' => now()->addDays(14),
            ],
        );
    }

    public function sendReviewRequests(BookingCheckOut $checkOut): void
    {
        $this->createGuestReviewRequest($checkOut);
        $this->createHostReviewRequest($checkOut);
    }

    public function markReviewCompleted(BookingReviewRequest $request): BookingReviewRequest
    {
        $request->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
        ])->save();

        return $request->refresh();
    }
}
