<?php

namespace App\Services\Waitlist;

use App\Data\Waitlist\DateRange;
use App\Data\Waitlist\WaitlistEligibilityResult;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use App\Services\AvailabilityService;
use Carbon\CarbonInterface;

class WaitlistAvailabilityService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly WaitlistQueueService $queue,
        private readonly WaitlistOfferService $offers,
    ) {}

    public function checkItem(WaitlistItem $item): WaitlistEligibilityResult
    {
        $item->loadMissing('sleepingPlace');

        $result = $this->queue->isEligible($item, $item->sleepingPlace, $this->queue->priceDataFor($item));
        $item->update(['last_checked_at' => now()]);

        return $result;
    }

    public function checkPlace(SleepingPlace $place): void
    {
        WaitlistItem::query()
            ->forSleepingPlace($place)
            ->active()
            ->get()
            ->each(fn (WaitlistItem $item) => $this->checkItem($item));
    }

    public function handlePlaceBecameAvailable(
        SleepingPlace $place,
        CarbonInterface|string|DateRange $checkIn,
        CarbonInterface|string|null $checkOut = null,
    ): ?WaitlistOffer {
        $range = $checkIn instanceof DateRange
            ? $checkIn
            : new DateRange($checkIn, $checkOut);

        $item = $this->queue->getNextEligibleGuest($place, $range);

        if (! $item instanceof WaitlistItem) {
            return null;
        }

        return $this->offers->createOffer($item, $this->queue->priceDataFor($item));
    }

    public function handleBookingCancelled(Booking $booking): ?WaitlistOffer
    {
        $booking->loadMissing('sleepingPlace');

        if (! $booking->status instanceof BookingStatus || ! $booking->status->isCancelled()) {
            $booking->forceFill(['status' => BookingStatus::CancelledByGuestFlow])->save();
        }

        $this->availability->releaseForBooking($booking);

        return $this->handlePlaceBecameAvailable(
            $booking->sleepingPlace,
            $booking->check_in_date ?: $booking->check_in,
            $booking->check_out_date ?: $booking->check_out,
        );
    }

    public function handleBookingExpired(Booking $booking): ?WaitlistOffer
    {
        $booking->forceFill(['status' => BookingStatus::Expired])->save();
        $this->availability->releaseForBooking($booking);

        return $this->handlePlaceBecameAvailable(
            $booking->sleepingPlace,
            $booking->check_in_date ?: $booking->check_in,
            $booking->check_out_date ?: $booking->check_out,
        );
    }

    public function handleHostOpenedDates(SleepingPlace $place, DateRange $range): ?WaitlistOffer
    {
        return $this->handlePlaceBecameAvailable($place, $range);
    }
}
