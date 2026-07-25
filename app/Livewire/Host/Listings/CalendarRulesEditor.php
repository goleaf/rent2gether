<?php

namespace App\Livewire\Host\Listings;

use App\Models\Property;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CalendarRulesEditor extends Component
{
    #[Locked]
    public int $propertyId;

    public ?int $sleepingPlaceId = null;

    public ?float $defaultPrice = null;

    public ?int $minNights = null;

    public ?int $maxNights = null;

    public int $cleaningGapHours = 0;

    public int $cleaningGapDays = 0;

    public string $checkInTimeFrom = '';

    public string $checkOutTimeUntil = '';

    /** @var list<int> */
    public array $checkInDays = [1, 2, 3, 4, 5, 6, 7];

    /** @var list<int> */
    public array $checkOutDays = [1, 2, 3, 4, 5, 6, 7];

    public function mount(int $propertyId): void
    {
        $this->propertyId = $this->ownedProperty($propertyId)->id;
        $this->sleepingPlaceId = $this->ownedProperty($propertyId)
            ->sleepingPlaces()
            ->value('id');

        $this->loadSelectedPlaceSettings();
    }

    public function updatedSleepingPlaceId(): void
    {
        $this->loadSelectedPlaceSettings();
    }

    public function saveSettings(HostCalendarDraftService $calendar): void
    {
        $host = auth()->user();
        $place = $this->place();

        abort_unless($host instanceof User && $place instanceof SleepingPlace, 403);

        $validated = $this->validate($this->rules(), attributes: $this->validationAttributes());

        $calendar->updateSettings($host, $place, [
            'default_price' => $validated['defaultPrice'],
            'currency' => $place->currency,
            'min_nights' => (int) $validated['minNights'],
            'max_nights' => $validated['maxNights'] === null ? null : (int) $validated['maxNights'],
            'cleaning_gap_hours' => (int) $validated['cleaningGapHours'],
            'cleaning_gap_days' => (int) $validated['cleaningGapDays'],
            'check_in_time_from' => $validated['checkInTimeFrom'] ?: null,
            'check_out_time_until' => $validated['checkOutTimeUntil'] ?: null,
        ]);

        $calendar->setCheckInDays($host, $place, $this->normalizedWeekdays($validated['checkInDays']));
        $calendar->setCheckOutDays($host, $place, $this->normalizedWeekdays($validated['checkOutDays']));

        $this->dispatch('listing-step-saved');
    }

    public function render(): View
    {
        $places = $this->ownedProperty($this->propertyId)
            ->sleepingPlaces()
            ->select(['id', 'property_id', 'display_name', 'place_number', 'base_price_per_night'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('livewire.host.listings.calendar-rules-editor', [
            'places' => $places,
            'weekdays' => [1, 2, 3, 4, 5, 6, 7],
        ]);
    }

    private function place(): ?SleepingPlace
    {
        return $this->sleepingPlaceId
            ? SleepingPlace::query()
                ->where('property_id', $this->propertyId)
                ->find($this->sleepingPlaceId)
            : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'sleepingPlaceId' => ['required', 'integer', ValidationRule::exists('sleeping_places', 'id')],
            'defaultPrice' => ['required', 'numeric', 'min:0.01', 'max:100000'],
            'minNights' => ['required', 'integer', 'min:1', 'max:365'],
            'maxNights' => ['nullable', 'integer', 'min:1', 'max:365', 'gte:minNights'],
            'cleaningGapHours' => ['required', 'integer', 'min:0', 'max:168'],
            'cleaningGapDays' => ['required', 'integer', 'min:0', 'max:30'],
            'checkInTimeFrom' => ['nullable', 'date_format:H:i'],
            'checkOutTimeUntil' => ['nullable', 'date_format:H:i'],
            'checkInDays' => ['required', 'array', 'min:1', 'max:7'],
            'checkInDays.*' => ['integer', ValidationRule::in([1, 2, 3, 4, 5, 6, 7])],
            'checkOutDays' => ['required', 'array', 'min:1', 'max:7'],
            'checkOutDays.*' => ['integer', ValidationRule::in([1, 2, 3, 4, 5, 6, 7])],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationAttributes(): array
    {
        return [
            'sleepingPlaceId' => __('listing_calendar.fields.sleeping_place'),
            'defaultPrice' => __('listing_calendar.fields.default_price'),
            'minNights' => __('listing_calendar.fields.min_nights'),
            'maxNights' => __('listing_calendar.fields.max_nights'),
            'cleaningGapHours' => __('listing_calendar.fields.cleaning_gap_hours'),
            'cleaningGapDays' => __('listing_calendar.fields.cleaning_gap_days'),
            'checkInTimeFrom' => __('listing_calendar.fields.check_in_time_from'),
            'checkOutTimeUntil' => __('listing_calendar.fields.check_out_time_until'),
            'checkInDays' => __('listing_calendar.fields.check_in_days'),
            'checkOutDays' => __('listing_calendar.fields.check_out_days'),
        ];
    }

    private function loadSelectedPlaceSettings(): void
    {
        $place = $this->place();

        if (! $place instanceof SleepingPlace) {
            return;
        }

        $settings = app(HostCalendarDraftService::class)->getSettings($place);

        $this->defaultPrice = $settings->default_price === null ? null : (float) $settings->default_price;
        $this->minNights = $settings->min_nights ?: $place->min_nights ?: 1;
        $this->maxNights = $settings->max_nights ?: $place->max_nights;
        $this->cleaningGapHours = (int) $settings->cleaning_gap_hours;
        $this->cleaningGapDays = (int) $settings->cleaning_gap_days;
        $this->checkInTimeFrom = (string) $settings->check_in_time_from;
        $this->checkOutTimeUntil = (string) $settings->check_out_time_until;
        $this->checkInDays = $this->weekdayRule($place, 'check_in_days');
        $this->checkOutDays = $this->weekdayRule($place, 'check_out_days');
    }

    /**
     * @return list<int>
     */
    private function weekdayRule(SleepingPlace $place, string $type): array
    {
        $weekdays = $place->calendarRules()
            ->where('rule_type', $type)
            ->value('weekdays_json');

        return $this->normalizedWeekdays(is_array($weekdays) ? $weekdays : [1, 2, 3, 4, 5, 6, 7]);
    }

    /**
     * @param  list<int|string>  $weekdays
     * @return list<int>
     */
    private function normalizedWeekdays(array $weekdays): array
    {
        return collect($weekdays)
            ->map(fn (int|string $day): int => (int) $day)
            ->filter(fn (int $day): bool => $day >= 1 && $day <= 7)
            ->unique()
            ->values()
            ->all();
    }

    private function ownedProperty(int $propertyId): Property
    {
        $property = Property::query()
            ->select(['id', 'host_user_id', 'user_id'])
            ->findOrFail($propertyId);

        $host = auth()->user();
        abort_unless($host instanceof User && $property->isOwnedBy($host), 403);

        return $property;
    }
}
