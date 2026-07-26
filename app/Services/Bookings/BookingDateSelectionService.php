<?php

namespace App\Services\Bookings;

use App\Data\Bookings\BookingDateSelectionData;
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
        return collect($this->checkoutCalendar($guest, $place, $checkIn, $maxNights)['available_checkout_dates']);
    }

    /**
     * @return array{
     *     available_checkout_dates:list<array{check_out:string,nights:int,chargeable_days:int,calendar_presence_days:int}>,
     *     unavailable_checkout_dates:list<array{check_out:string,nights:int,reasons:list<string>,message_keys:list<string>}>,
     *     earliest_checkout_date:?string,
     *     latest_checkout_date:?string,
     *     min_checkout_date:string,
     *     max_checkout_date:string,
     *     nearest_available_ranges:list<array{check_in:string,check_out:string,nights:int}>,
     *     similar_sleeping_places:list<array{id:int,title:string|null,room_id:int|null,property_id:int|null,price:mixed,currency:string|null}>,
     *     same_host_alternatives:list<array{id:int,title:string|null,room_id:int|null,property_id:int|null,price:mixed,currency:string|null}>,
     *     neighbor_room_alternatives:list<array{id:int,title:string|null,room_id:int|null,property_id:int|null,price:mixed,currency:string|null}>
     * }
     */
    public function checkoutCalendar(User $guest, SleepingPlace $place, CarbonInterface|string $checkIn, int $maxNights = 30): array
    {
        $checkIn = $this->date($checkIn);
        [$minimumNights, $maximumNights] = $this->candidateStayLimits($place, $maxNights);
        $available = collect();
        $unavailable = collect();
        $candidates = $this->availabilitySuggestions->checkoutCandidateAvailability($place, $checkIn, $maximumNights);

        foreach ($candidates as $candidate) {
            $nights = $candidate['nights'];
            $checkOut = $this->date($candidate['check_out']);
            $rangeMinimumNights = max($minimumNights, $candidate['minimum_nights_override']);

            if ($nights < $rangeMinimumNights) {
                $unavailable->push($this->unavailableCheckoutDate($checkOut, $nights, ['below_min_nights']));

                continue;
            }

            if ($candidate['available']) {
                $available->push($this->checkoutDate($checkIn, $checkOut));

                continue;
            }

            $unavailable->push($this->unavailableCheckoutDate(
                $checkOut,
                $nights,
                $candidate['reasons'] === [] ? ['date_unavailable'] : $candidate['reasons'],
            ));
        }

        $earliest = $available->first()['check_out'] ?? null;
        $latest = $available->last()['check_out'] ?? null;
        $suggestionCheckOut = $earliest
            ? $this->date($earliest)
            : $checkIn->addDays($minimumNights);

        return [
            'available_checkout_dates' => $available->values()->all(),
            'unavailable_checkout_dates' => $unavailable->values()->all(),
            'earliest_checkout_date' => $earliest,
            'latest_checkout_date' => $latest,
            'min_checkout_date' => $checkIn->addDays($minimumNights)->toDateString(),
            'max_checkout_date' => $checkIn->addDays($maximumNights)->toDateString(),
            'nearest_available_ranges' => $this->availabilitySuggestions
                ->suggestNearestAvailableRanges($place, $checkIn, $minimumNights)
                ->all(),
            'similar_sleeping_places' => $this->availabilitySuggestions
                ->suggestSimilarSleepingPlaces($place, $checkIn, $suggestionCheckOut)
                ->all(),
            'same_host_alternatives' => $this->sameHostAlternatives($place, $checkIn, $suggestionCheckOut),
            'neighbor_room_alternatives' => $this->availabilitySuggestions
                ->suggestNeighborRoomAlternatives($place, $checkIn, $suggestionCheckOut)
                ->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function selectionData(array $data): BookingDateSelectionData
    {
        $checkIn = $this->date($data['check_in_date']);
        $checkOut = $this->date($data['check_out_date']);

        return new BookingDateSelectionData(
            checkInDate: $checkIn->toDateString(),
            checkInTime: $this->nullableString($data['check_in_time'] ?? null),
            checkOutDate: $checkOut->toDateString(),
            checkOutTime: $this->nullableString($data['check_out_time'] ?? null),
            nightsCount: $this->stayLength->calculateNights($checkIn, $checkOut),
            stayDaysCount: $this->stayLength->calculateChargeableDays($checkIn, $checkOut),
            calendarPresenceDaysCount: $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
            earlyCheckInRequested: (bool) ($data['early_check_in_requested'] ?? false),
            lateCheckOutRequested: (bool) ($data['late_check_out_requested'] ?? false),
            flexibleCheckIn: (bool) ($data['flexible_check_in'] ?? false),
            flexibleCheckOut: (bool) ($data['flexible_check_out'] ?? false),
            requiresHostTimeApproval: (bool) ($data['requires_host_time_approval'] ?? false),
            checkInComment: $this->nullableString($data['check_in_comment'] ?? null),
            checkOutComment: $this->nullableString($data['check_out_comment'] ?? null),
        );
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

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array{0:int,1:int}
     */
    private function candidateStayLimits(SleepingPlace $place, int $maxNights): array
    {
        $minimumNights = max(1, (int) ($place->min_nights ?: 1));
        $maximumNights = max(1, min(60, $maxNights));

        if ($place->max_nights !== null) {
            $maximumNights = min($maximumNights, max($minimumNights, (int) $place->max_nights));
        }

        return [$minimumNights, $maximumNights];
    }

    /**
     * @return array{check_out:string,nights:int,chargeable_days:int,calendar_presence_days:int}
     */
    private function checkoutDate(CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        return [
            'check_out' => $checkOut->toDateString(),
            'nights' => $this->stayLength->calculateNights($checkIn, $checkOut),
            'chargeable_days' => $this->stayLength->calculateChargeableDays($checkIn, $checkOut),
            'calendar_presence_days' => $this->stayLength->calculateCalendarPresenceDays($checkIn, $checkOut),
        ];
    }

    /**
     * @param  list<string>  $reasons
     * @return array{check_out:string,nights:int,reasons:list<string>,message_keys:list<string>}
     */
    private function unavailableCheckoutDate(CarbonImmutable $checkOut, int $nights, array $reasons): array
    {
        return [
            'check_out' => $checkOut->toDateString(),
            'nights' => $nights,
            'reasons' => collect($reasons)->unique()->values()->all(),
            'message_keys' => collect($reasons)
                ->map(fn (string $reason): string => $this->messageKeyForReason($reason))
                ->unique()
                ->values()
                ->all(),
        ];
    }

    private function messageKeyForReason(string $reason): string
    {
        return match ($reason) {
            'range_overlaps_existing_booking' => 'booking_dates.validation.date_locked_by_another_booking',
            'closed_by_host', 'hierarchy_unavailable', 'date_unavailable' => 'booking_dates.validation.sleeping_place_unavailable',
            'closed_by_service' => 'booking_dates.validation.closed_by_service',
            'cleaning_gap_required' => 'booking_dates.validation.cleaning_gap_required',
            'inspection_gap_required' => 'booking_dates.validation.inspection_gap_required',
            'repair' => 'booking_dates.validation.sleeping_place_repair',
            'broken' => 'booking_dates.validation.broken',
            'complaint_blocked' => 'booking_dates.validation.complaint_block',
            'hidden', 'temporarily_hidden' => 'booking_dates.validation.hidden',
            'check_in_not_allowed' => 'booking_dates.validation.check_in_weekday_not_allowed',
            'check_out_not_allowed' => 'booking_dates.validation.check_out_weekday_not_allowed',
            'request_only' => 'booking_dates.validation.request_only',
            'below_min_nights' => 'booking_dates.validation.below_min_nights_checkout',
            'above_max_nights' => 'booking_dates.validation.above_max_nights_checkout',
            default => 'booking_dates.validation.sleeping_place_unavailable',
        };
    }

    /**
     * @return list<array{id:int,title:string|null,room_id:int|null,property_id:int|null,price:mixed,currency:string|null}>
     */
    private function sameHostAlternatives(SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        if (! $place->user_id) {
            return [];
        }

        return $this->availabilitySuggestions
            ->suggestSameHostAlternatives((int) $place->user_id, $checkIn, $checkOut, $place->id)
            ->all();
    }
}
