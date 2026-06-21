<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\SleepingPlaceAvailabilitySuggestionService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class BookingDateSelectionService
{
    public function __construct(
        private readonly SleepingPlaceAvailabilitySuggestionService $availabilitySuggestions,
        private readonly StayLengthCalculatorService $stayLength,
        private readonly BookingPriceQuoteService $quotes,
    ) {}

    /**
     * @return Collection<int, array{check_out:string,nights:int,chargeable_days:int,calendar_presence_days:int}>
     */
    public function availableCheckoutDates(User $guest, SleepingPlace $place, CarbonInterface|string $checkIn, int $maxNights = 30): Collection
    {
        $checkIn = $this->date($checkIn);

        return $this->availabilitySuggestions
            ->suggestAvailableCheckOutDates($place, $checkIn)
            ->take(max(1, $maxNights))
            ->map(function (array $date) use ($checkIn): array {
                $checkOut = $this->date($date['check_out']);

                return [
                    'check_out' => $checkOut->toDateString(),
                    'nights' => $this->stayLength->calculateNights($checkIn, $checkOut),
                    'chargeable_days' => $this->stayLength->calculateChargeableDays($checkIn, $checkOut),
                    'calendar_presence_days' => $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
                ];
            })
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createQuotePreview(User $guest, SleepingPlace $place, array $data): BookingQuote
    {
        return $this->quotes->createQuote($guest, $place, $data);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function updateQuotePreview(BookingQuote $quote, array $changes): BookingQuote
    {
        return $this->quotes->recalculateQuote($quote, $changes);
    }

    private function date(CarbonInterface|string $date): CarbonImmutable
    {
        return $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse($date)->startOfDay();
    }
}
