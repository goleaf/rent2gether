<?php

namespace App\Services\Waitlist;

use App\Models\Booking;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;

class WaitlistAutoRequestService
{
    public function canAutoSendRequest(WaitlistItem $item): bool
    {
        return (bool) $item->auto_send_request && (bool) $item->ready_to_book_immediately;
    }

    public function sendRequestToHost(WaitlistItem $item): ?Booking
    {
        return null;
    }

    public function canCreateBookingDraft(WaitlistItem $item): bool
    {
        return (bool) $item->auto_create_booking_draft && (bool) $item->ready_to_book_immediately;
    }

    public function createBookingDraft(WaitlistOffer $offer): ?Booking
    {
        return null;
    }
}
