<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\User;
use App\Services\SleepingPlaces\SleepingPlaceAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingQuoteAvailabilityService
{
    public function __construct(
        private readonly SleepingPlaceAvailabilityService $availability,
    ) {}

    public function checkAvailability(BookingQuote $quote): BookingQuote
    {
        $quote->loadMissing(['guest', 'sleepingPlace']);

        if (! $quote->guest instanceof User || $quote->sleepingPlace === null) {
            return $this->markUnavailable($quote, collect(['sleeping_place_unavailable']));
        }

        $result = $this->availability->canBookRange(
            $quote->guest,
            $quote->sleepingPlace,
            CarbonImmutable::instance($quote->check_in_date),
            CarbonImmutable::instance($quote->check_out_date),
            [
                'check_in_time' => $quote->check_in_time,
                'check_out_time' => $quote->check_out_time,
            ],
        );

        if (! $result['available']) {
            return $this->markUnavailable($quote, collect($result['reasons']));
        }

        if ($result['request_only']) {
            return $this->markRequestOnly($quote);
        }

        return $this->markAvailable($quote);
    }

    /**
     * @return Collection<int, string>
     */
    public function getBlockingReasons(BookingQuote $quote): Collection
    {
        $quote->loadMissing('sleepingPlace');

        if ($quote->sleepingPlace === null) {
            return collect(['sleeping_place_unavailable']);
        }

        return $this->availability->getBlockingReasons(
            $quote->sleepingPlace,
            CarbonImmutable::instance($quote->check_in_date),
            CarbonImmutable::instance($quote->check_out_date),
        );
    }

    public function markAvailable(BookingQuote $quote): BookingQuote
    {
        $quote->forceFill(['availability_status' => 'available'])->save();

        return $quote;
    }

    /**
     * @param  Collection<int, string>  $reasons
     */
    public function markUnavailable(BookingQuote $quote, Collection $reasons): BookingQuote
    {
        $quote->forceFill(['availability_status' => $reasons->count() > 1 ? 'partially_unavailable' : 'unavailable'])->save();

        return $quote;
    }

    public function markRequestOnly(BookingQuote $quote): BookingQuote
    {
        $quote->forceFill(['availability_status' => 'request_only'])->save();

        return $quote;
    }
}
