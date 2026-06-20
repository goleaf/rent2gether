<?php

namespace App\Livewire\Booking;

use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\PricingService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

class BookingDateSelector extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    public string $checkIn = '';

    public string $checkOut = '';

    public int $guestsCount = 1;

    /** @var array<string, mixed>|null */
    public ?array $quote = null;

    /** @var list<string> */
    public array $unavailableDates = [];

    /** @var list<array{check_in:string,check_out:string,nights:int}> */
    public array $nearestRanges = [];

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
        $this->quote = null;
        $this->unavailableDates = [];
        $this->nearestRanges = [];

        if ($this->checkIn === '' || $this->checkOut === '') {
            return;
        }

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->addError('checkIn', __('booking.date_selector.errors.login_required'));

            return;
        }

        $place = $this->sleepingPlace();
        $validated = $this->validate([
            'checkIn' => ['required', 'date', 'after_or_equal:today'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
            'guestsCount' => ['required', 'integer', 'min:1'],
        ], attributes: $this->validationAttributes());

        $checkIn = CarbonImmutable::parse($validated['checkIn'])->startOfDay();
        $checkOut = CarbonImmutable::parse($validated['checkOut'])->startOfDay();
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guestsCount = (int) $validated['guestsCount'];
        [$minNights, $maxNights] = $this->stayLimits($place, $checkIn, $checkOut);

        if ($guestsCount > $place->max_guests) {
            $this->addError('guestsCount', trans_choice('booking.date_selector.errors.max_guests', $place->max_guests, [
                'count' => $place->max_guests,
            ]));

            return;
        }

        if ($nights < $minNights) {
            $this->addError('checkIn', trans_choice('booking.date_selector.errors.min_nights', $minNights, [
                'count' => $minNights,
            ]));

            return;
        }

        if ($maxNights !== null && $nights > $maxNights) {
            $this->addError('checkOut', trans_choice('booking.date_selector.errors.max_nights', $maxNights, [
                'count' => $maxNights,
            ]));

            return;
        }

        $availability = app(AvailabilityService::class);

        if (! $availability->isAvailable($place, $checkIn, $checkOut)) {
            $this->unavailableDates = $availability->unavailableDates($place, $checkIn, $checkOut);
            $this->nearestRanges = $availability->nearestAvailableRanges($place, $checkIn, max(1, $nights));
            $this->addError('checkIn', __('booking.date_selector.errors.unavailable_dates'));

            return;
        }

        $this->quote = app(PricingService::class)
            ->calculate($guest, $place, $checkIn, $checkOut, $guestsCount)
            ->toArray();
    }

    public function render(): View
    {
        return view('livewire.booking.booking-date-selector');
    }

    private function sleepingPlace(): SleepingPlace
    {
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
            ])
            ->findOrFail($this->sleepingPlaceId);
    }

    /**
     * @return array{0:int,1:int|null}
     */
    private function stayLimits(SleepingPlace $place, CarbonImmutable $checkIn, CarbonImmutable $checkOut): array
    {
        $minNights = max(1, (int) ($place->min_nights ?: 1));
        $maxNights = $place->max_nights === null ? null : (int) $place->max_nights;

        $place->availabilityDays()
            ->select(['id', 'sleeping_place_id', 'min_nights_override', 'max_nights_override'])
            ->whereDate('date', '>=', $checkIn->toDateString())
            ->whereDate('date', '<', $checkOut->toDateString())
            ->where(function ($query): void {
                $query->whereNotNull('min_nights_override')
                    ->orWhereNotNull('max_nights_override');
            })
            ->get()
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
}
