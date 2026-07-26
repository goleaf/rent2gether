<?php

namespace App\Services\Bookings;

use App\Models\BookingQuote;
use App\Models\BookingQuoteSuggestion;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\SleepingPlaceAvailabilitySuggestionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingQuoteSuggestionService
{
    public function __construct(
        private readonly SleepingPlaceAvailabilitySuggestionService $suggestions,
    ) {}

    /**
     * @return Collection<int, BookingQuoteSuggestion>
     */
    public function suggestNearestDates(BookingQuote $quote): Collection
    {
        $quote->loadMissing('sleepingPlace');

        if (! $quote->sleepingPlace instanceof SleepingPlace) {
            return collect();
        }

        return $this->suggestions
            ->suggestNearestAvailableRanges($quote->sleepingPlace, CarbonImmutable::instance($quote->check_in_date), max(1, (int) $quote->nights_count))
            ->values()
            ->map(fn (array $range, int $index) => $quote->suggestions()->create([
                'suggestion_type' => 'nearest_dates',
                'sleeping_place_id' => $quote->sleeping_place_id,
                'room_id' => $quote->room_id,
                'property_id' => $quote->property_id,
                'check_in_date' => $range['check_in'],
                'check_out_date' => $range['check_out'],
                'nights_count' => $range['nights'],
                'price_preview_amount' => $quote->total_without_deposit,
                'currency' => $quote->currency,
                'message_key' => 'booking_quotes.suggestions.nearest_dates',
                'sort_order' => $index,
            ]));
    }

    /**
     * @return Collection<int, BookingQuoteSuggestion>
     */
    public function suggestSameRoomAlternatives(BookingQuote $quote): Collection
    {
        $quote->loadMissing('room');

        return $quote->room instanceof Room
            ? $this->placeAlternatives($quote, $this->suggestions->suggestSameRoomAlternatives($quote->room, $quote->check_in_date, $quote->check_out_date), 'same_room_place', 20)
            : collect();
    }

    /**
     * @return Collection<int, BookingQuoteSuggestion>
     */
    public function suggestSamePropertyAlternatives(BookingQuote $quote): Collection
    {
        $quote->loadMissing('property');

        return $quote->property instanceof Property
            ? $this->placeAlternatives($quote, $this->suggestions->suggestSamePropertyAlternatives($quote->property, $quote->check_in_date, $quote->check_out_date), 'same_property_place', 40)
            : collect();
    }

    /**
     * @return Collection<int, BookingQuoteSuggestion>
     */
    public function suggestSameHostAlternatives(BookingQuote $quote): Collection
    {
        $quote->loadMissing('host');

        return $quote->host instanceof User
            ? $this->placeAlternatives($quote, $this->suggestions->suggestSameHostAlternatives($quote->host, $quote->check_in_date, $quote->check_out_date), 'same_host_place', 60)
            : collect();
    }

    /**
     * @return Collection<int, BookingQuoteSuggestion>
     */
    public function suggestSimilarPlaces(BookingQuote $quote): Collection
    {
        $quote->loadMissing('sleepingPlace');

        return $quote->sleepingPlace instanceof SleepingPlace
            ? $this->placeAlternatives($quote, $this->suggestions->suggestSimilarSleepingPlaces($quote->sleepingPlace, $quote->check_in_date, $quote->check_out_date), 'similar_place', 80)
            : collect();
    }

    /**
     * @return Collection<int, BookingQuoteSuggestion>
     */
    public function createSuggestionsForInvalidQuote(BookingQuote $quote): Collection
    {
        $quote->suggestions()->delete();

        return $this->suggestNearestDates($quote)
            ->merge($this->suggestSameRoomAlternatives($quote))
            ->merge($this->suggestSamePropertyAlternatives($quote))
            ->merge($this->suggestSameHostAlternatives($quote))
            ->merge($this->suggestSimilarPlaces($quote))
            ->values();
    }

    /**
     * @param  Collection<int, array{id:int,room_id:int|null,property_id:int|null,price:mixed,currency:string|null}>  $places
     * @return Collection<int, BookingQuoteSuggestion>
     */
    private function placeAlternatives(BookingQuote $quote, Collection $places, string $type, int $sortBase): Collection
    {
        return $places
            ->reject(fn (array $place): bool => (int) $place['id'] === (int) $quote->sleeping_place_id)
            ->values()
            ->map(fn (array $place, int $index) => $quote->suggestions()->create([
                'suggestion_type' => $type,
                'sleeping_place_id' => $place['id'],
                'room_id' => $place['room_id'],
                'property_id' => $place['property_id'],
                'check_in_date' => $quote->check_in_date,
                'check_out_date' => $quote->check_out_date,
                'nights_count' => $quote->nights_count,
                'price_preview_amount' => $place['price'],
                'currency' => $place['currency'] ?: $quote->currency,
                'message_key' => 'booking_quotes.suggestions.'.$type,
                'sort_order' => $sortBase + $index,
            ]));
    }
}
