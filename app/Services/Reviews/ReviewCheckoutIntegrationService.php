<?php

namespace App\Services\Reviews;

use App\Models\BookingCheckOut;
use App\Models\ReviewRequest;
use Illuminate\Support\Collection;

class ReviewCheckoutIntegrationService
{
    public function __construct(private readonly ReviewRequestService $requests) {}

    /**
     * @return Collection<int, ReviewRequest>
     */
    public function createReviewRequestsAfterCheckout(BookingCheckOut $checkOut): Collection
    {
        return $this->requests->createRequestsAfterCheckout($checkOut);
    }

    public function cancelReviewRequestsIfCheckoutCancelled(BookingCheckOut $checkOut): void
    {
        ReviewRequest::query()
            ->where('booking_check_out_id', $checkOut->id)
            ->whereIn('status', ['created', 'sent', 'opened', 'started'])
            ->get()
            ->each(fn ($request) => $this->requests->cancelRequest($request, 'checkout_cancelled'));
    }
}
