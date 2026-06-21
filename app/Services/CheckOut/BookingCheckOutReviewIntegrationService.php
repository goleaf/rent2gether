<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingReviewRequest;
use Illuminate\Support\Collection;

class BookingCheckOutReviewIntegrationService
{
    /**
     * @return Collection<int, BookingReviewRequest>
     */
    public function createReviewRequestsAfterCheckout(BookingCheckOut $checkOut): Collection
    {
        app(BookingReviewRequestService::class)->sendReviewRequests($checkOut);
        app(BookingCheckOutEventService::class)->record($checkOut, 'review_requested');

        return BookingReviewRequest::query()
            ->where('booking_id', $checkOut->booking_id)
            ->orderBy('id')
            ->get();
    }

    public function notifyGuestReviewRequested(BookingCheckOut $checkOut): void
    {
        $this->createReviewRequestsAfterCheckout($checkOut);
    }

    public function notifyHostReviewRequested(BookingCheckOut $checkOut): void
    {
        $this->createReviewRequestsAfterCheckout($checkOut);
    }
}
