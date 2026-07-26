<?php

namespace App\Services\Waitlist;

use App\Actions\Bookings\BookingSubmit;
use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;

class WaitlistAutoRequestService
{
    public function __construct(
        private readonly WaitlistQueueService $queue,
        private readonly BookingSubmit $bookingSubmit,
    ) {}

    public function canAutoSendRequest(WaitlistItem $item): bool
    {
        $item->loadMissing('sleepingPlace:id,instant_booking_enabled,requires_host_approval');

        return (bool) $item->auto_send_request
            && (bool) $item->ready_to_book_immediately
            && $this->placeAcceptsHostRequest($item);
    }

    public function sendRequestToHost(WaitlistItem $item): ?Booking
    {
        if (! $this->canAutoSendRequest($item)) {
            return null;
        }

        return $this->createHostApprovalRequest($item);
    }

    public function canCreateBookingDraft(WaitlistItem $item): bool
    {
        $item->loadMissing('sleepingPlace:id,instant_booking_enabled,requires_host_approval');

        return (bool) $item->auto_create_booking_draft
            && (bool) $item->ready_to_book_immediately
            && $this->placeAcceptsHostRequest($item);
    }

    public function createBookingDraft(WaitlistOffer $offer): ?Booking
    {
        $offer->loadMissing('waitlistItem');

        if (! $offer->waitlistItem instanceof WaitlistItem || ! $this->canCreateBookingDraft($offer->waitlistItem)) {
            return null;
        }

        return $this->createHostApprovalRequest($offer->waitlistItem);
    }

    private function placeAcceptsHostRequest(WaitlistItem $item): bool
    {
        $place = $item->sleepingPlace;

        return $place instanceof SleepingPlace
            && (! (bool) $place->instant_booking_enabled || (bool) $place->requires_host_approval);
    }

    private function createHostApprovalRequest(WaitlistItem $item): ?Booking
    {
        $item->loadMissing(['user:id', 'sleepingPlace']);

        if (! $item->user instanceof User || ! $item->sleepingPlace instanceof SleepingPlace) {
            return null;
        }

        $range = $this->queue->rangeForItem($item);

        return $this->bookingSubmit->handle($item->user, $item->sleepingPlace, [
            'check_in' => $range->checkIn->toDateString(),
            'check_out' => $range->checkOut->toDateString(),
            'guests_count' => max(1, (int) $item->guests_count),
            'guest_message' => $item->guest_message,
            'rules_accepted' => true,
            'profile_ready' => true,
            'payment_mode' => 'pay_later',
        ]);
    }
}
