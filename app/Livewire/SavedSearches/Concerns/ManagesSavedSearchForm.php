<?php

namespace App\Livewire\SavedSearches\Concerns;

use App\Enums\RoomType;
use App\Enums\SleepingPlaceType;
use App\Models\City;
use App\Models\SavedSearch;
use App\Support\Geo\GeoNameNormalizer;
use App\Support\SavedSearches\SavedSearchFormOptions;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Throwable;

trait ManagesSavedSearchForm
{
    public string $title = '';

    public string $description = '';

    public ?int $cityId = null;

    public string $cityQuery = '';

    public string $district = '';

    public string $checkInDate = '';

    public string $checkOutDate = '';

    public string $budgetMin = '';

    public string $budgetMax = '';

    public string $currency = 'EUR';

    public string $roomType = '';

    public string $sleepingPlaceType = '';

    /** @var array<string, bool> */
    public array $requiredAmenities = [
        'wifi' => false,
        'kitchen' => false,
        'washing_machine' => false,
        'locker' => false,
        'workspace' => false,
    ];

    /** @var array<string, bool> */
    public array $excludedConditions = [
        'smoking' => false,
        'pets' => false,
        'mixed_room' => false,
    ];

    public bool $onlyVerifiedHosts = false;

    public bool $onlyInstantBooking = false;

    public bool $notifyNewMatches = true;

    public bool $notifyPriceDrops = true;

    public bool $notifyAvailableAgain = true;

    public string $notificationFrequency = 'on_visit';

    public string $status = 'active';

    public function updatedCityQuery(): void
    {
        $this->cityId = null;

        unset($this->cityOptions);
    }

    public function selectCity(int $cityId): void
    {
        $city = City::query()
            ->select(['id', 'name', 'ascii_name', 'population', 'name_normalized', 'status', 'is_active'])
            ->visible()
            ->translated(app()->getLocale())
            ->find($cityId);

        if (! $city instanceof City) {
            return;
        }

        $this->cityId = $city->id;
        $this->cityQuery = $city->localizedName();

        unset($this->cityOptions);
    }

    /**
     * @return list<array{id:int,name:string,meta:string}>
     */
    #[Computed]
    public function cityOptions(): array
    {
        $normalized = GeoNameNormalizer::normalize($this->cityQuery);

        if ($this->cityId !== null || Str::length($normalized) < 2) {
            return [];
        }

        return City::query()
            ->select(['id', 'name', 'ascii_name', 'population', 'name_normalized', 'status', 'is_active'])
            ->visible()
            ->translated(app()->getLocale())
            ->namePrefix($normalized)
            ->orderByDesc('population')
            ->limit(10)
            ->get()
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->localizedName(),
                'meta' => $city->population ? __('saved_searches.city_population', ['count' => number_format((int) $city->population)]) : '',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function roomTypeOptions(): array
    {
        return collect(RoomType::cases())
            ->mapWithKeys(fn (RoomType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function sleepingPlaceTypeOptions(): array
    {
        return collect(SleepingPlaceType::cases())
            ->mapWithKeys(fn (SleepingPlaceType $type): array => [$type->value => $type->label()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function requiredAmenityOptions(): array
    {
        return collect(SavedSearchFormOptions::requiredAmenities())
            ->mapWithKeys(fn (string $key): array => [$key => __('saved_searches.amenities.'.$key)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function excludedConditionOptions(): array
    {
        return collect(SavedSearchFormOptions::excludedConditions())
            ->mapWithKeys(fn (string $key): array => [$key => __('saved_searches.excluded.'.$key)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function frequencyOptions(): array
    {
        return collect(SavedSearchFormOptions::notificationFrequencies())
            ->mapWithKeys(fn (string $frequency): array => [$frequency => __('saved_searches.frequency.'.$frequency)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function statusOptions(): array
    {
        return collect(SavedSearchFormOptions::statuses())
            ->mapWithKeys(fn (string $status): array => [$status => __('saved_searches.statuses.'.$status)])
            ->all();
    }

    #[Computed]
    public function nightsPreview(): ?int
    {
        if ($this->checkInDate === '' || $this->checkOutDate === '') {
            return null;
        }

        try {
            $start = CarbonImmutable::parse($this->checkInDate)->startOfDay();
            $end = CarbonImmutable::parse($this->checkOutDate)->startOfDay();
        } catch (Throwable) {
            return null;
        }

        if ($end->lessThanOrEqualTo($start)) {
            return null;
        }

        return (int) $start->diffInDays($end);
    }

    protected function loadSavedSearchForm(SavedSearch $search): void
    {
        $this->title = $search->displayTitle();
        $this->description = (string) $search->description;
        $this->cityId = $search->city_id;
        $this->cityQuery = $search->cityModel?->localizedName() ?: (string) $search->city;
        $this->district = (string) $search->district;
        $this->checkInDate = $search->check_in_date?->toDateString() ?: $search->check_in?->toDateString() ?: '';
        $this->checkOutDate = $search->check_out_date?->toDateString() ?: $search->check_out?->toDateString() ?: '';
        $this->budgetMin = $search->budget_min === null ? '' : (string) $search->budget_min;
        $this->budgetMax = $search->budget_max === null ? '' : (string) $search->budget_max;
        $this->currency = strtoupper($search->currency ?: 'EUR');
        $this->roomType = (string) ($search->room_type ?: $this->firstString((array) $search->room_types_json));
        $this->sleepingPlaceType = (string) ($search->bed_type ?: $this->firstString((array) $search->sleeping_place_types_json));
        $this->requiredAmenities = $this->booleanSelection(
            SavedSearchFormOptions::requiredAmenityColumns(),
            (array) ($search->required_amenity_ids_json ?: []),
            $search,
        );
        $this->excludedConditions = $this->booleanSelection(
            SavedSearchFormOptions::excludedConditionColumns(),
            (array) ($search->excluded_conditions_json ?: []),
            $search,
        );
        $this->onlyVerifiedHosts = (bool) $search->only_verified_hosts;
        $this->onlyInstantBooking = (bool) $search->only_instant_booking;
        $this->notifyNewMatches = (bool) $search->notify_new_matches;
        $this->notifyPriceDrops = (bool) $search->notify_price_drops;
        $this->notifyAvailableAgain = (bool) $search->notify_available_again;
        $this->notificationFrequency = $search->notification_frequency ?: 'on_visit';
        $this->status = in_array($search->status, SavedSearchFormOptions::statuses(), true) ? $search->status : 'active';
    }

    /**
     * @return array<string, mixed>
     */
    protected function savedSearchPayload(): array
    {
        $this->validateSavedSearchForm();

        $requiredAmenities = $this->selectedRequiredAmenities();
        $excludedConditions = $this->selectedExcludedConditions();

        return [
            'title' => trim($this->title),
            'description' => $this->blankToNull($this->description),
            'city_id' => $this->cityId,
            'city_name' => $this->blankToNull($this->cityQuery),
            'district' => $this->blankToNull($this->district),
            'check_in_date' => $this->blankToNull($this->checkInDate),
            'check_out_date' => $this->blankToNull($this->checkOutDate),
            'budget_min' => $this->moneyToNull($this->budgetMin),
            'budget_max' => $this->moneyToNull($this->budgetMax),
            'currency' => strtoupper($this->currency ?: 'EUR'),
            'room_type' => $this->blankToNull($this->roomType),
            'sleeping_place_type' => $this->blankToNull($this->sleepingPlaceType),
            'required_amenities' => $requiredAmenities,
            'excluded_conditions' => $excludedConditions,
            'require_wifi' => in_array('wifi', $requiredAmenities, true),
            'require_kitchen' => in_array('kitchen', $requiredAmenities, true),
            'require_washing_machine' => in_array('washing_machine', $requiredAmenities, true),
            'require_locker' => in_array('locker', $requiredAmenities, true),
            'require_workspace' => in_array('workspace', $requiredAmenities, true),
            'avoid_smoking' => in_array('smoking', $excludedConditions, true),
            'avoid_pets' => in_array('pets', $excludedConditions, true),
            'avoid_mixed_room' => in_array('mixed_room', $excludedConditions, true),
            'only_verified_hosts' => $this->onlyVerifiedHosts,
            'only_instant_booking' => $this->onlyInstantBooking,
            'notify_new_matches' => $this->notifyNewMatches,
            'notify_price_drops' => $this->notifyPriceDrops,
            'notify_available_again' => $this->notifyAvailableAgain,
            'notification_frequency' => $this->notificationFrequency,
            'status' => $this->status,
        ];
    }

    protected function validateSavedSearchForm(): void
    {
        $this->resetErrorBag();

        validator($this->validationData(), [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cityId' => ['nullable', 'integer', 'exists:cities,id'],
            'cityQuery' => ['nullable', 'string', 'max:160'],
            'district' => ['nullable', 'string', 'max:160'],
            'checkInDate' => ['nullable', 'required_with:checkOutDate', 'date'],
            'checkOutDate' => ['nullable', 'required_with:checkInDate', 'date', 'after:checkInDate'],
            'budgetMin' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'budgetMax' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'currency' => ['required', 'string', 'size:3'],
            'roomType' => ['nullable', Rule::in(SavedSearchFormOptions::roomTypes())],
            'sleepingPlaceType' => ['nullable', Rule::in(SavedSearchFormOptions::sleepingPlaceTypes())],
            'requiredAmenities' => ['array'],
            'requiredAmenities.*' => ['boolean'],
            'excludedConditions' => ['array'],
            'excludedConditions.*' => ['boolean'],
            'onlyVerifiedHosts' => ['boolean'],
            'onlyInstantBooking' => ['boolean'],
            'notifyNewMatches' => ['boolean'],
            'notifyPriceDrops' => ['boolean'],
            'notifyAvailableAgain' => ['boolean'],
            'notificationFrequency' => ['required', Rule::in(SavedSearchFormOptions::notificationFrequencies())],
            'status' => ['required', Rule::in(SavedSearchFormOptions::statuses())],
        ], [], $this->validationAttributes())
            ->after(function ($validator): void {
                $min = $this->moneyToNull($this->budgetMin);
                $max = $this->moneyToNull($this->budgetMax);

                if ($min !== null && $max !== null && $max < $min) {
                    $validator->errors()->add('budgetMax', __('validation.gte.numeric', [
                        'attribute' => __('saved_searches.budget_max'),
                        'value' => __('saved_searches.budget_min'),
                    ]));
                }
            })
            ->validate();
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationData(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'cityId' => $this->cityId,
            'cityQuery' => $this->cityQuery,
            'district' => $this->district,
            'checkInDate' => $this->checkInDate,
            'checkOutDate' => $this->checkOutDate,
            'budgetMin' => $this->budgetMin,
            'budgetMax' => $this->budgetMax,
            'currency' => $this->currency,
            'roomType' => $this->roomType,
            'sleepingPlaceType' => $this->sleepingPlaceType,
            'requiredAmenities' => $this->requiredAmenities,
            'excludedConditions' => $this->excludedConditions,
            'onlyVerifiedHosts' => $this->onlyVerifiedHosts,
            'onlyInstantBooking' => $this->onlyInstantBooking,
            'notifyNewMatches' => $this->notifyNewMatches,
            'notifyPriceDrops' => $this->notifyPriceDrops,
            'notifyAvailableAgain' => $this->notifyAvailableAgain,
            'notificationFrequency' => $this->notificationFrequency,
            'status' => $this->status,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'title' => __('saved_searches.search_name'),
            'description' => __('saved_searches.description'),
            'cityId' => __('saved_searches.city'),
            'cityQuery' => __('saved_searches.city'),
            'district' => __('saved_searches.district'),
            'checkInDate' => __('saved_searches.check_in'),
            'checkOutDate' => __('saved_searches.check_out'),
            'budgetMin' => __('saved_searches.budget_min'),
            'budgetMax' => __('saved_searches.budget_max'),
            'currency' => __('saved_searches.currency'),
            'roomType' => __('saved_searches.room_type'),
            'sleepingPlaceType' => __('saved_searches.sleeping_place_type'),
            'requiredAmenities' => __('saved_searches.required_amenities'),
            'excludedConditions' => __('saved_searches.excluded_conditions'),
            'onlyVerifiedHosts' => __('saved_searches.verified_hosts_only'),
            'onlyInstantBooking' => __('saved_searches.instant_booking_only'),
            'notifyNewMatches' => __('saved_searches.notify_new_matches'),
            'notifyPriceDrops' => __('saved_searches.notify_price_drops'),
            'notifyAvailableAgain' => __('saved_searches.notify_available_again'),
            'notificationFrequency' => __('saved_searches.notification_frequency'),
            'status' => __('saved_searches.search_status'),
        ];
    }

    /**
     * @return list<string>
     */
    private function selectedRequiredAmenities(): array
    {
        return $this->selectedKeys($this->requiredAmenities, SavedSearchFormOptions::requiredAmenities());
    }

    /**
     * @return list<string>
     */
    private function selectedExcludedConditions(): array
    {
        return $this->selectedKeys($this->excludedConditions, SavedSearchFormOptions::excludedConditions());
    }

    /**
     * @param  array<string, bool>  $values
     * @param  list<string>  $allowed
     * @return list<string>
     */
    private function selectedKeys(array $values, array $allowed): array
    {
        return collect($values)
            ->filter()
            ->keys()
            ->intersect($allowed)
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string>  $columns
     * @param  list<string>  $storedKeys
     * @return array<string, bool>
     */
    private function booleanSelection(array $columns, array $storedKeys, SavedSearch $search): array
    {
        return collect($columns)
            ->mapWithKeys(fn (string $column, string $key): array => [
                $key => in_array($key, $storedKeys, true) || (bool) $search->{$column},
            ])
            ->all();
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function moneyToNull(string $value): ?float
    {
        return $value === '' ? null : round((float) $value, 2);
    }

    private function firstString(array $values): string
    {
        foreach ($values as $value) {
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return '';
    }
}
