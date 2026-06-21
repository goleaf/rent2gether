<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutIssue;

class BookingCheckOutRatingIntegrationService
{
    public function recordSuccessfulCheckout(BookingCheckOut $checkOut): void
    {
        app(BookingCheckOutEventService::class)->record($checkOut, 'successful_checkout_rating_event');
    }

    public function recordDamageEventIfConfirmed(BookingCheckOutIssue $issue): void
    {
        if ($issue->status !== 'resolved') {
            return;
        }

        app(BookingCheckOutEventService::class)->record($issue->checkOut, 'damage_confirmed_rating_event', [
            'source_type' => 'booking_check_out_issue',
            'source_id' => $issue->id,
        ]);
    }

    public function recordDepositDisputeIfOpened(BookingCheckOutIssue $issue): void
    {
        if ($issue->issue_type !== 'deposit_dispute') {
            return;
        }

        app(BookingCheckOutEventService::class)->record($issue->checkOut, 'deposit_dispute_rating_event', [
            'source_type' => 'booking_check_out_issue',
            'source_id' => $issue->id,
        ]);
    }
}
