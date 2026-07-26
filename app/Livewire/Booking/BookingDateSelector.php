<?php

namespace App\Livewire\Booking;

use App\Enums\AvailabilityStatus;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Pricing\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Number;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingDateSelector extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    public function mount(int|SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace instanceof SleepingPlace
            ? $sleepingPlace->id
            : $sleepingPlace;
    }

    public function updatedCheckIn(): void
    {
        $this->refreshQuote();
    }

    public function updatedCheckOut(): void
    {
        $this->refreshQuote();
    }

    public function updatedGuestsCount(): void
    {
        $this->refreshQuote();
    }

    public function refreshQuote(): void
    {
        $this->resetValidation();
        unset($this->dateEvaluation);

        $this->dateEvaluation;
    }

    /**
     * @return array{quote:array<string,mixed>|null,unavailableDates:list<string>,nearestRanges:list<array{check_in:string,check_out:string,nights:int}>}
     */
    #[Computed]
    public function dateEvaluation(): array
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return $this->emptyDateEvaluation();
        }

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->addError('checkIn', __('booking.date_selector.errors.login_required'));

            return $this->emptyDateEvaluation();
        }

        $validated = $this->validatedDateInputs();

        if ($validated === null) {
            return $this->emptyDateEvaluation();
        }

        $place = $this->sleepingPlace;
        $checkIn = CarbonImmutable::parse($validated['checkIn'])->startOfDay();
        $checkOut = CarbonImmutable::parse($validated['checkOut'])->startOfDay();

        $nights = (int) $checkIn->diffInDays($checkOut);
        $guestsCount = (int) $validated['guestsCount'];
        [$minNights, $maxNights] = $this->stayLimits($place, $checkIn, $checkOut);

        if ($guestsCount > $place->max_guests) {
            $this->addError('guestsCount', trans_choice('booking.date_selector.errors.max_guests', $place->max_guests, [
                'count' => $place->max_guests,
            ]));

            return $this->emptyDateEvaluation();
        }

        if ($nights < $minNights) {
            $this->addError('checkIn', trans_choice('booking.date_selector.errors.min_nights', $minNights, [
                'count' => $minNights,
            ]));

            return $this->emptyDateEvaluation();
        }

        if ($maxNights !== null && $nights > $maxNights) {
            $this->addError('checkOut', trans_choice('booking.date_selector.errors.max_nights', $maxNights, [
                'count' => $maxNights,
            ]));

            return $this->emptyDateEvaluation();
        }

        $availability = app(AvailabilityService::class);

        if (! $availability->isAvailable($place, $checkIn, $checkOut, usePrefetchedAvailabilityDays: true)) {
            $this->addError('checkIn', __('booking.date_selector.errors.unavailable_dates'));

            return [
                'quote' => null,
                'unavailableDates' => $availability->unavailableDates($place, $checkIn, $checkOut),
                'nearestRanges' => $availability->nearestAvailableRanges($place, $checkIn, max(1, $nights)),
            ];
        }

        return [
            'quote' => app(PricingService::class)
                ->calculate($guest, $place, $checkIn, $checkOut, $guestsCount)
                ->toArray(),
            'unavailableDates' => [],
            'nearestRanges' => [],
        ];
    }

    public function render(): View
    {
        $dateEvaluation = $this->dateEvaluation;

        return view('livewire.booking.booking-date-selector', [
            'quote' => $dateEvaluation['quote'],
            'unavailableDates' => $dateEvaluation['unavailableDates'],
            'nearestRanges' => $dateEvaluation['nearestRanges'],
            'hasAdjustedDates' => $this->hasAdjustedDates($dateEvaluation['quote']),
        ]);
    }

    public function money(float|int|string $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }

    #[Computed]
    public function sleepingPlace(): SleepingPlace
    {
        $dateRange = $this->validatedPrefetchDateRange();

        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'status',
                'display_name',
                'max_guests',
                'base_price_per_night',
                'weekly_price',
                'monthly_price',
                'weekend_price',
                'cleaning_fee',
                'deposit_amount',
                'currency',
                'min_nights',
                'max_nights',
            ])
            ->with([
                'room:id,property_id,status',
                'property:id,status',
                'calendarSettings:id,sleeping_place_id,active,booking_mode',
                'turnoverRules:id,sleeping_place_id,min_gap_minutes,cleaning_required_between_guests,cleaning_gap_minutes,inspection_required_after_checkout,inspection_gap_minutes,same_day_turnover_allowed,morning_checkout_evening_checkin_allowed,earliest_new_check_in_time,latest_previous_check_out_time',
            ])
            ->when($dateRange !== null, function ($query) use ($dateRange): void {
                [$checkIn, $checkOut] = $dateRange;

                $query->with(['availabilityDays' => fn ($relation) => $relation
                    ->select([
                        'id',
                        'sleeping_place_id',
                        'date',
                        'status',
                        'price_override',
                        'min_nights_override',
                        'max_nights_override',
                        'check_in_allowed',
                        'check_out_allowed',
                    ])
                    ->whereDate('date', '>=', $checkIn->toDateString())
                    ->whereDate('date', '<=', $checkOut->toDateString())
                    ->where(function ($availabilityQuery): void {
                        $availabilityQuery->whereNotNull('price_override')
                            ->orWhereNotNull('min_nights_override')
                            ->orWhereNotNull('max_nights_override')
                            ->orWhere('check_in_allowed', false)
                            ->orWhere('check_out_allowed', false)
                            ->orWhereIn('status', AvailabilityStatus::blocksStayValues())
                            ->orWhereIn('status', [
                                AvailabilityStatus::CheckInOnly->value,
                                AvailabilityStatus::CheckOutOnly->value,
                            ]);
                    })]);
            })
            ->findOrFail($this->sleepingPlaceId);
    }

    /**
     * @return array{quote:null,unavailableDates:list<string>,nearestRanges:list<array{check_in:string,check_out:string,nights:int}>}
     */
    private function emptyDateEvaluation(): array
    {
        return [
            'quote' => null,
            'unavailableDates' => [],
            'nearestRanges' => [],
        ];
    }

    /**
     * @return array{checkIn:string,checkOut:string,guestsCount:int}|null
     */
    private function validatedDateInputs(): ?array
    {
        $validator = validator([
            'checkIn' => $this->checkIn,
            'checkOut' => $this->checkOut,
            'guestsCount' => $this->guestsCount,
        ], [
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'guestsCount' => ['required', 'integer', 'min:1'],
        ], [], $this->validationAttributes());

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                $this->addError($field, (string) $messages[0]);
            }

            return null;
        }

        /** @var array{checkIn:string,checkOut:string,guestsCount:int} $validated */
        $validated = $validator->validated();

        return $validated;
    }

    /**
     * @return array{0:CarbonImmutable,1:CarbonImmutable}|null
     */
    private function validatedPrefetchDateRange(): ?array
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return null;
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->startOfDay();
            $checkOut = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return null;
        }

        return [$checkIn, $checkOut];
    }

    /**
     * @return array{0:int,1:int|null}
     */
    private function stayLimits(SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        $minNights = max(1, (int) ($place->min_nights ?: 1));
        $maxNights = $place->max_nights === null ? null : (int) $place->max_nights;

        $availabilityDays = $place->relationLoaded('availabilityDays')
            ? $place->availabilityDays
                ->filter(function ($day) use ($checkIn, $checkOut): bool {
                    $date = CarbonImmutable::parse($day->date)->startOfDay();

                    return $date->greaterThanOrEqualTo($checkIn)
                        && $date->lessThan($checkOut)
                        && ($day->min_nights_override !== null || $day->max_nights_override !== null);
                })
            : $place->availabilityDays()
                ->select(['id', 'sleeping_place_id', 'date', 'min_nights_override', 'max_nights_override'])
                ->whereDate('date', '>=', $checkIn->toDateString())
                ->whereDate('date', '<', $checkOut->toDateString())
                ->where(function ($query): void {
                    $query->whereNotNull('min_nights_override')
                        ->orWhereNotNull('max_nights_override');
                })
                ->get();

        $availabilityDays
            ->each(function ($day) use (&$minNights, &$maxNights): void {
                if ($day->min_nights_override !== null) {
                    $minNights = max($minNights, (int) $day->min_nights_override);
                }

                if ($day->max_nights_override !== null) {
                    $override = (int) $day->max_nights_override;
                    $maxNights = $maxNights === null ? $override : min($maxNights, $override);
                }
            });

        return [$minNights, $maxNights];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        $attributes = app('translator')->get('booking.date_selector.validation_attributes');

        return is_array($attributes) ? $attributes : [];
    }

    /**
     * @param  array<string, mixed>|null  $quote
     */
    private function hasAdjustedDates(?array $quote): bool
    {
        return $quote !== null
            && collect($quote['date_prices'])->contains(fn (array $datePrice): bool => $datePrice['source'] !== 'base');
    }
}
