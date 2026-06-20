<?php

namespace App\Livewire\Search;

use App\Data\Listings\ListingCardContext;
use App\Enums\GenderType;
use App\Enums\PropertyType;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceType;
use App\Models\Amenity;
use App\Models\City;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Compatibility\CompatibilityService;
use App\Services\Geo\GeoSearchService;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\Localization\SupportedContentLocales;
use App\Services\Pricing\PricingService;
use App\Support\Geo\GeoNameNormalizer;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class SleepingPlaceSearch extends Component
{
    private const INITIAL_VISIBLE_COUNT = 12;

    private const MAX_VISIBLE_COUNT = 60;

    private const CARD_AMENITY_SLUGS = [
        'wifi',
        'fast_wifi',
        'kitchen',
        'washing_machine',
        'personal_locker',
        'locker_with_lock',
        'workspace',
        'desk',
        'parking',
        'elevator',
    ];

    #[Url(as: 'city', except: '')]
    public string $city = '';

    #[Url(as: 'city_name', except: '')]
    public string $cityQuery = '';

    #[Url(as: 'district', except: '')]
    public string $district = '';

    #[Url(as: 'in', except: '')]
    public string $checkIn = '';

    #[Url(as: 'out', except: '')]
    public string $checkOut = '';

    #[Url(as: 'guests', except: 1)]
    public int $guestsCount = 1;

    #[Url(as: 'price_min', except: '')]
    public string $priceMin = '';

    #[Url(as: 'price_max', except: '')]
    public string $priceMax = '';

    #[Url(as: 'currency', except: '')]
    public string $currency = '';

    #[Url(as: 'property_type', except: '')]
    public string $propertyType = '';

    #[Url(as: 'room_type', except: '')]
    public string $roomType = '';

    #[Url(as: 'place_type', except: '')]
    public string $sleepingPlaceType = '';

    #[Url(as: 'gender', except: '')]
    public string $roomGenderPolicy = '';

    #[Url(as: 'instant', except: false)]
    public bool $instantBooking = false;

    #[Url(as: 'approval', except: false)]
    public bool $hostApprovalRequired = false;

    #[Url(as: 'wifi', except: false)]
    public bool $wifi = false;

    #[Url(as: 'kitchen', except: false)]
    public bool $kitchen = false;

    #[Url(as: 'washing', except: false)]
    public bool $washingMachine = false;

    #[Url(as: 'locker', except: false)]
    public bool $locker = false;

    #[Url(as: 'lower_bunk', except: false)]
    public bool $lowerBunkOnly = false;

    #[Url(as: 'not_upper', except: false)]
    public bool $notUpperBunk = false;

    #[Url(as: 'bedding', except: false)]
    public bool $beddingIncluded = false;

    #[Url(as: 'towel', except: false)]
    public bool $towelIncluded = false;

    #[Url(as: 'workspace', except: false)]
    public bool $workspace = false;

    #[Url(as: 'late', except: false)]
    public bool $lateCheckIn = false;

    #[Url(as: 'self_checkin', except: false)]
    public bool $selfCheckIn = false;

    #[Url(as: 'quiet', except: false)]
    public bool $quietHours = false;

    #[Url(as: 'no_smoking', except: false)]
    public bool $noSmoking = false;

    #[Url(as: 'pets', except: false)]
    public bool $petsAllowed = false;

    #[Url(as: 'no_pets', except: false)]
    public bool $noPets = false;

    #[Url(as: 'no_mixed', except: false)]
    public bool $noMixedRoom = false;

    #[Url(as: 'max_people', except: '')]
    public string $maxPeopleInRoom = '';

    #[Url(as: 'elevator', except: false)]
    public bool $elevator = false;

    #[Url(as: 'parking', except: false)]
    public bool $parking = false;

    #[Url(as: 'rating', except: false)]
    public bool $highRating = false;

    #[Url(as: 'verified', except: false)]
    public bool $verifiedHost = false;

    #[Url(as: 'reviews', except: false)]
    public bool $hasReviews = false;

    #[Url(as: 'no_deposit', except: false)]
    public bool $noDeposit = false;

    #[Url(as: 'free_cancel', except: false)]
    public bool $freeCancellation = false;

    #[Url(as: 'long_stay', except: false)]
    public bool $longStayAllowed = false;

    #[Url(as: 'today', except: false)]
    public bool $availableToday = false;

    #[Url(as: 'flexible', except: false)]
    public bool $flexibleDates = false;

    #[Url(as: 'sort', except: 'recommended')]
    public string $sort = 'recommended';

    public bool $filtersOpen = false;

    public bool $cityOpen = false;

    public int $visibleCount = self::INITIAL_VISIBLE_COUNT;

    public function mount(): void
    {
        if ($this->cityQuery === '' && $this->city !== '') {
            $this->cityQuery = $this->selectedCity()?->name ?: $this->city;
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'cityQuery') {
            $this->city = $this->cityQuery;
            $this->cityOpen = true;
        }

        if (in_array($property, $this->filterPropertyNames(), true)) {
            $this->visibleCount = self::INITIAL_VISIBLE_COUNT;
        }
    }

    public function selectCity(int $cityId): void
    {
        $city = City::query()
            ->select(['id', 'name', 'status', 'is_active'])
            ->visible()
            ->find($cityId);

        if (! $city) {
            return;
        }

        $this->city = (string) $city->id;
        $this->cityQuery = $city->name;
        $this->cityOpen = false;
        $this->visibleCount = self::INITIAL_VISIBLE_COUNT;
    }

    public function clearCity(): void
    {
        $this->city = '';
        $this->cityQuery = '';
        $this->cityOpen = false;
        $this->visibleCount = self::INITIAL_VISIBLE_COUNT;
    }

    public function clearFilters(): void
    {
        $this->reset([
            'city',
            'cityQuery',
            'district',
            'checkIn',
            'checkOut',
            'guestsCount',
            'priceMin',
            'priceMax',
            'currency',
            'propertyType',
            'roomType',
            'sleepingPlaceType',
            'roomGenderPolicy',
            'instantBooking',
            'hostApprovalRequired',
            'wifi',
            'kitchen',
            'washingMachine',
            'locker',
            'lowerBunkOnly',
            'notUpperBunk',
            'beddingIncluded',
            'towelIncluded',
            'workspace',
            'lateCheckIn',
            'selfCheckIn',
            'quietHours',
            'noSmoking',
            'petsAllowed',
            'noPets',
            'noMixedRoom',
            'maxPeopleInRoom',
            'elevator',
            'parking',
            'highRating',
            'verifiedHost',
            'hasReviews',
            'noDeposit',
            'freeCancellation',
            'longStayAllowed',
            'availableToday',
            'flexibleDates',
            'sort',
        ]);

        $this->filtersOpen = false;
        $this->cityOpen = false;
        $this->visibleCount = self::INITIAL_VISIBLE_COUNT;
    }

    public function loadMore(): void
    {
        $this->visibleCount = min(self::MAX_VISIBLE_COUNT, $this->visibleCount + self::INITIAL_VISIBLE_COUNT);
    }

    #[Computed]
    public function nights(): int
    {
        $dates = $this->dateRange();

        return $dates ? (int) $dates[0]->diffInDays($dates[1]) : 0;
    }

    #[Computed]
    public function calendarDays(): int
    {
        return $this->nights > 0 ? $this->nights + 1 : 0;
    }

    #[Computed]
    public function dateWarning(): ?string
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return null;
        }

        try {
            $start = CarbonImmutable::parse($this->checkIn)->startOfDay();
            $end = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            return __('search.date_warnings.use_valid_dates');
        }

        if ($start->isBefore(CarbonImmutable::today())) {
            return __('search.date_warnings.past_dates');
        }

        if ($end->lessThanOrEqualTo($start)) {
            return __('search.date_warnings.checkout_after_checkin');
        }

        return null;
    }

    /**
     * @return list<array{id:int,name:string,country:?string}>
     */
    #[Computed]
    public function cityOptions(): array
    {
        if (! $this->cityOpen || Str::length(GeoNameNormalizer::normalize($this->cityQuery)) < 2) {
            return [];
        }

        return app(GeoSearchService::class)
            ->cities($this->cityQuery, app()->getLocale())
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'name' => $city->localizedName(),
                'country' => $city->country?->localizedName(),
            ])
            ->all();
    }

    /**
     * @return array{cards:list<array<string,mixed>>,has_more:bool,showing:int,total:int}
     */
    #[Computed]
    public function searchResults(): array
    {
        $context = $this->listingCardContext();
        $query = $this->searchQuery();
        $total = (clone $query)->reorder()->count('sleeping_places.id');
        $places = $query
            ->limit($this->visibleCount)
            ->get();

        $cards = app(ListingCardService::class)
            ->buildMany($places, $context)
            ->map(fn ($card): array => $card->toArray())
            ->values()
            ->all();

        return [
            'cards' => $cards,
            'has_more' => $total > $this->visibleCount && $this->visibleCount < self::MAX_VISIBLE_COUNT,
            'showing' => count($cards),
            'total' => $total,
        ];
    }

    public function propertyTypeOptions(): array
    {
        return collect(PropertyType::cases())
            ->mapWithKeys(fn (PropertyType $type): array => [$type->value => $type->label()])
            ->all();
    }

    public function roomTypeOptions(): array
    {
        return collect(RoomType::cases())
            ->mapWithKeys(fn (RoomType $type): array => [$type->value => $type->label()])
            ->all();
    }

    public function sleepingPlaceTypeOptions(): array
    {
        return collect(SleepingPlaceType::cases())
            ->mapWithKeys(fn (SleepingPlaceType $type): array => [$type->value => $type->label()])
            ->all();
    }

    public function genderOptions(): array
    {
        return collect(GenderType::cases())
            ->mapWithKeys(fn (GenderType $gender): array => [$gender->value => $gender->label()])
            ->all();
    }

    public function sortOptions(): array
    {
        return [
            'recommended' => __('search.sort_options.recommended'),
            'cheapest' => __('search.sort_options.cheapest'),
            'highest_rating' => __('search.sort_options.highest_rating'),
            'closest_to_center' => __('search.sort_options.closest_to_center'),
            'fewer_people' => __('search.sort_options.fewer_people'),
            'newest' => __('search.sort_options.newest'),
        ];
    }

    public function activeFilterCount(): int
    {
        return collect($this->filterPropertyNames())
            ->reject(fn (string $property): bool => in_array($property, ['cityQuery', 'checkIn', 'checkOut', 'sort'], true))
            ->filter(function (string $property): bool {
                $value = $this->{$property};

                return is_bool($value) ? $value : $value !== '' && $value !== 1;
            })
            ->count();
    }

    public function render(): View
    {
        return view('livewire.search.sleeping-place-search', [
            'results' => $this->resultsForView(),
            'saveSearchCityId' => $this->saveSearchCityId(),
            'cityHasEnoughCharacters' => $this->cityHasEnoughCharacters(),
        ])->layout('layouts.app', ['title' => __('search.title')]);
    }

    /**
     * @return array{cards:list<array<string,mixed>>,has_more:bool,showing:int,total:int}
     */
    private function resultsForView(): array
    {
        return array_merge([
            'cards' => [],
            'has_more' => false,
            'showing' => 0,
            'total' => 0,
        ], $this->searchResults);
    }

    private function saveSearchCityId(): ?int
    {
        return ctype_digit((string) $this->city) ? (int) $this->city : null;
    }

    private function cityHasEnoughCharacters(): bool
    {
        return Str::length(GeoNameNormalizer::normalize($this->cityQuery)) >= 2;
    }

    private function searchQuery(): Builder
    {
        $query = app(ListingCardQueryService::class)->forSearch($this->listingCardContext());

        $this->applyLocationFilters($query);
        $this->applyDateFilters($query);
        $this->applyPriceAndTypeFilters($query);
        $this->applyComfortFilters($query);
        $this->applyTrustFilters($query);
        $this->applySorting($query);

        return $query;
    }

    private function listingCardContext(): ListingCardContext
    {
        return new ListingCardContext(
            userId: auth()->id(),
            locale: app()->getLocale(),
            currency: strtoupper($this->currency ?: 'EUR'),
            checkInDate: $this->checkIn ?: null,
            checkOutDate: $this->checkOut ?: null,
            nightsCount: $this->nights ?: null,
            calendarDaysCount: $this->calendarDays ?: null,
            guestsCount: max(1, $this->guestsCount),
            source: 'search',
            filters: [
                'variant' => 'search',
                'search_filtered_available' => $this->dateRange() !== null && ! $this->flexibleDates,
                'comparison_ids' => session('comparison_places', []),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function eagerLoads(): array
    {
        $locales = $this->translationLocales();
        $mediaSelect = ['id', 'mediable_type', 'mediable_id', 'disk', 'path', 'thumb_path', 'thumbnail_path', 'mobile_path', 'full_path', 'alt_text', 'sort_order', 'is_primary', 'is_cover', 'status'];
        $mediaTranslations = fn ($query) => $query
            ->select(['id', 'media_item_id', 'locale', 'caption'])
            ->whereIn('locale', $locales);
        $amenitySelect = ['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status'];
        $cardAmenities = fn ($query) => $query
            ->select($amenitySelect)
            ->whereIn('amenities.slug', self::CARD_AMENITY_SLUGS);
        $amenityTranslation = fn ($query) => $query
            ->select(['id', 'amenity_id', 'locale', 'name'])
            ->whereIn('locale', $locales);

        $with = [
            'translations' => fn ($query) => $query
                ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary'])
                ->whereIn('locale', $locales),
            'cardMedia' => fn ($query) => $query->select($mediaSelect)->with(['translations' => $mediaTranslations]),
            'amenities' => $cardAmenities,
            'amenities.translations' => $amenityTranslation,
            'room' => fn ($query) => $query
                ->select(['id', 'property_id', 'type', 'status', 'title', 'gender_policy', 'gender_type', 'max_guests', 'occupied_places_count', 'available_places_count', 'has_desk', 'has_chair', 'noise_level'])
                ->with([
                    'translations' => fn ($translation) => $translation
                        ->select(['id', 'room_id', 'locale', 'title', 'summary'])
                        ->whereIn('locale', $locales),
                    'cardMedia' => fn ($media) => $media->select($mediaSelect)->with(['translations' => $mediaTranslations]),
                    'amenities' => $cardAmenities,
                    'amenities.translations' => $amenityTranslation,
                ]),
            'property' => fn ($query) => $query
                ->select(['id', 'host_user_id', 'city_id', 'type', 'status', 'city', 'district', 'distance_to_center_meters', 'kitchens_count', 'has_elevator', 'has_parking', 'title'])
                ->with([
                    'translations' => fn ($translation) => $translation
                        ->select(['id', 'property_id', 'locale', 'title', 'summary'])
                        ->whereIn('locale', $locales),
                    'cityModel:id,name',
                    'cardMedia' => fn ($media) => $media->select($mediaSelect)->with(['translations' => $mediaTranslations]),
                    'amenities' => $cardAmenities,
                    'amenities.translations' => $amenityTranslation,
                    'host:id,name,rating_as_host,identity_verified,identity_verified_at',
                    'host.hostProfile:id,user_id,rating_average,reviews_count,response_time_minutes,verified_at,default_cancellation_policy',
                ]),
        ];

        $dates = $this->dateRange();

        if ($dates) {
            $with['availabilityDays'] = fn ($query) => $query
                ->select(['id', 'sleeping_place_id', 'date', 'price_override'])
                ->whereDate('date', '>=', $dates[0]->toDateString())
                ->whereDate('date', '<', $dates[1]->toDateString())
                ->whereNotNull('price_override');
        }

        return $with;
    }

    private function applyLocationFilters(Builder $query): void
    {
        if ($cityId = $this->selectedCityId()) {
            $query->where('search_properties.city_id', $cityId);
        } elseif ($this->city !== '') {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.city', 'like', '%'.$this->city.'%')
                    ->orWhereHas('property.cityModel', fn (Builder $city) => $city->nameContainsInLocale($this->city, app()->getLocale()));
            });
        }

        if ($this->district !== '') {
            $query->where('search_properties.district', 'like', '%'.$this->district.'%');
        }
    }

    private function applyDateFilters(Builder $query): void
    {
        $dates = $this->dateRange();

        if ($dates && ! $this->flexibleDates) {
            $nights = (int) $dates[0]->diffInDays($dates[1]);

            $query->availableBetween($dates[0]->toDateString(), $dates[1]->toDateString())
                ->where('sleeping_places.min_nights', '<=', $nights)
                ->where(function (Builder $builder) use ($nights): void {
                    $builder->whereNull('sleeping_places.max_nights')
                        ->orWhere('sleeping_places.max_nights', '>=', $nights);
                });
        }

        if ($this->availableToday) {
            $today = CarbonImmutable::today();

            $query->availableBetween($today->toDateString(), $today->addDay()->toDateString())
                ->where('sleeping_places.min_nights', '<=', 1);
        }
    }

    private function applyPriceAndTypeFilters(Builder $query): void
    {
        if (($priceMin = $this->decimal($this->priceMin)) !== null) {
            $query->where('sleeping_places.base_price_per_night', '>=', $priceMin);
        }

        if (($priceMax = $this->decimal($this->priceMax)) !== null) {
            $query->where('sleeping_places.base_price_per_night', '<=', $priceMax);
        }

        if ($this->currency !== '') {
            $query->where('sleeping_places.currency', strtoupper($this->currency));
        }

        if ($this->propertyType !== '') {
            $query->where('search_properties.type', $this->propertyType);
        }

        if ($this->roomType !== '') {
            $query->where('search_rooms.type', $this->roomType);
        }

        if ($this->sleepingPlaceType !== '') {
            $query->where('sleeping_places.type', $this->sleepingPlaceType);
        }

        if ($this->roomGenderPolicy !== '') {
            $query->where('search_rooms.gender_policy', $this->roomGenderPolicy);
        }

        $query->where('sleeping_places.max_guests', '>=', max(1, $this->guestsCount));
    }

    private function applyComfortFilters(Builder $query): void
    {
        if ($this->instantBooking) {
            $query->where('sleeping_places.instant_booking_enabled', true);
        }

        if ($this->hostApprovalRequired) {
            $query->where('sleeping_places.requires_host_approval', true);
        }

        if ($this->wifi) {
            $this->whereHasAnyAmenity($query, ['wifi', 'fast_wifi']);
        }

        if ($this->kitchen) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.kitchens_count', '>', 0);
                $this->orWhereHasAnyAmenity($builder, ['kitchen']);
            });
        }

        if ($this->washingMachine) {
            $this->whereHasAnyAmenity($query, ['washing_machine']);
        }

        if ($this->locker) {
            $query->where(function (Builder $builder): void {
                $builder->where('sleeping_places.has_locker', true);
                $this->orWhereHasAnyAmenity($builder, ['personal_locker', 'locker_with_lock']);
            });
        }

        if ($this->lowerBunkOnly) {
            $query->where(function (Builder $builder): void {
                $builder->where('sleeping_places.type', SleepingPlaceType::BunkBottom->value)
                    ->orWhereIn('sleeping_places.bunk_level', ['bottom', 'lower', '1']);
            });
        }

        if ($this->notUpperBunk) {
            $query->where('sleeping_places.type', '!=', SleepingPlaceType::BunkTop->value)
                ->where(function (Builder $builder): void {
                    $builder->whereNull('sleeping_places.bunk_level')
                        ->orWhereNotIn('sleeping_places.bunk_level', ['top', 'upper', '2']);
                });
        }

        if ($this->beddingIncluded) {
            $query->where('sleeping_places.has_bedding', true);
        }

        if ($this->towelIncluded) {
            $query->where('sleeping_places.has_towel', true);
        }

        if ($this->workspace) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.has_desk', true)
                    ->orWhere('search_rooms.has_chair', true);
                $this->orWhereHasAnyAmenity($builder, ['workspace', 'desk', 'chair']);
            });
        }

        if ($this->lateCheckIn) {
            $query->where(function (Builder $builder): void {
                $this->orWhereHasAnyAmenity($builder, ['self_check_in', 'key_safe', 'electronic_lock']);
                $builder->orWhereHas('property.host.hostProfile', fn (Builder $host) => $host->where('can_help_with_check_in', true));
            });
        }

        if ($this->selfCheckIn) {
            $this->whereHasAnyAmenity($query, ['self_check_in', 'key_safe', 'electronic_lock']);
        }

        if ($this->quietHours) {
            $this->whereHasAnyRule($query, ['quiet_hours_after_22', 'no_loud_calls_at_night', 'no_main_light_at_night']);
        }

        if ($this->noSmoking) {
            $this->whereHasAnyRule($query, ['no_smoking']);
        }

        if ($this->petsAllowed) {
            $this->whereHasAnyRule($query, ['pets_by_request']);
        }

        if ($this->noPets) {
            $this->whereHasAnyRule($query, ['no_pets']);
        }

        if ($this->noMixedRoom) {
            $query->where('search_rooms.gender_policy', '!=', GenderType::Mixed->value);
        }

        if (($maxPeople = $this->integer($this->maxPeopleInRoom)) !== null) {
            $query->where('search_rooms.max_guests', '<=', $maxPeople);
        }

        if ($this->elevator) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.has_elevator', true);
                $this->orWhereHasAnyAmenity($builder, ['elevator']);
            });
        }

        if ($this->parking) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.has_parking', true);
                $this->orWhereHasAnyAmenity($builder, ['parking']);
            });
        }

        if ($this->noDeposit) {
            $query->where('sleeping_places.deposit_amount', '<=', 0);
        }

        if ($this->longStayAllowed) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('sleeping_places.max_nights')
                    ->orWhere('sleeping_places.max_nights', '>=', 28)
                    ->orWhereNotNull('sleeping_places.monthly_price');
            });
        }
    }

    private function applyTrustFilters(Builder $query): void
    {
        if ($this->highRating) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_host_profiles.rating_average', '>=', 4.5)
                    ->orWhereHas('property.host', fn (Builder $host) => $host->where('rating_as_host', '>=', 4.5));
            });
        }

        if ($this->verifiedHost) {
            $query->where(function (Builder $builder): void {
                $builder->whereNotNull('search_host_profiles.verified_at')
                    ->orWhereHas('property.host', fn (Builder $host) => $host->where('identity_verified', true));
            });
        }

        if ($this->hasReviews) {
            $query->where('search_host_profiles.reviews_count', '>', 0);
        }

        if ($this->freeCancellation) {
            $query->where('search_host_profiles.default_cancellation_policy', 'flexible');
        }
    }

    private function applySorting(Builder $query): void
    {
        match ($this->sort) {
            'cheapest' => $query->orderBy('sleeping_places.base_price_per_night')->orderByDesc('sleeping_places.id'),
            'highest_rating' => $query->orderByDesc('search_host_profiles.rating_average')->orderByDesc('search_host_profiles.reviews_count')->orderByDesc('sleeping_places.id'),
            'closest_to_center' => $query->orderBy('search_properties.distance_to_center_meters')->orderByDesc('sleeping_places.id'),
            'fewer_people' => $query->orderBy('search_rooms.max_guests')->orderBy('search_rooms.occupied_places_count')->orderByDesc('sleeping_places.id'),
            'newest' => $query->orderByDesc('sleeping_places.created_at')->orderByDesc('sleeping_places.id'),
            default => $query->orderByDesc('sleeping_places.instant_booking_enabled')
                ->orderByDesc('search_host_profiles.rating_average')
                ->orderBy('sleeping_places.base_price_per_night')
                ->orderByDesc('sleeping_places.id'),
        };
    }

    private function whereHasAnyAmenity(Builder $query, array $slugs): void
    {
        $query->where(function (Builder $builder) use ($slugs): void {
            $builder->whereHas('amenities', fn (Builder $amenity) => $amenity->whereIn('slug', $slugs))
                ->orWhereHas('room.amenities', fn (Builder $amenity) => $amenity->whereIn('slug', $slugs))
                ->orWhereHas('property.amenities', fn (Builder $amenity) => $amenity->whereIn('slug', $slugs));
        });
    }

    private function orWhereHasAnyAmenity(Builder $query, array $slugs): void
    {
        $query->orWhere(function (Builder $builder) use ($slugs): void {
            $builder->whereHas('amenities', fn (Builder $amenity) => $amenity->whereIn('slug', $slugs))
                ->orWhereHas('room.amenities', fn (Builder $amenity) => $amenity->whereIn('slug', $slugs))
                ->orWhereHas('property.amenities', fn (Builder $amenity) => $amenity->whereIn('slug', $slugs));
        });
    }

    private function whereHasAnyRule(Builder $query, array $slugs): void
    {
        $query->where(function (Builder $builder) use ($slugs): void {
            $builder->whereHas('rules', fn (Builder $rule) => $rule->whereIn('slug', $slugs))
                ->orWhereHas('room.rules', fn (Builder $rule) => $rule->whereIn('slug', $slugs))
                ->orWhereHas('property.rules', fn (Builder $rule) => $rule->whereIn('slug', $slugs));
        });
    }

    private function selectedCityId(): ?int
    {
        return ctype_digit($this->city) ? (int) $this->city : null;
    }

    private function selectedCity(): ?City
    {
        $cityId = $this->selectedCityId();

        if (! $cityId) {
            return null;
        }

        return City::query()
            ->select(['id', 'name', 'status', 'is_active'])
            ->visible()
            ->find($cityId);
    }

    /**
     * @return array{0:CarbonImmutable,1:CarbonImmutable}|null
     */
    private function dateRange(): ?array
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return null;
        }

        try {
            $start = CarbonImmutable::parse($this->checkIn)->startOfDay();
            $end = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        if ($start->isBefore(CarbonImmutable::today()) || $end->lessThanOrEqualTo($start)) {
            return null;
        }

        return [$start, $end];
    }

    /**
     * @return list<string>
     */
    private function translationLocales(): array
    {
        return app(SupportedContentLocales::class)->preferred();
    }

    private function resolver(): LocalizedModelContentResolver
    {
        return app(LocalizedModelContentResolver::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function toCard(SleepingPlace $place, ?User $guest): array
    {
        $room = $place->room;
        $property = $place->property;
        $media = $place->cardMedia ?: $room?->cardMedia ?: $property?->cardMedia;
        $quote = $this->priceQuote($place, $guest);
        $hostProfile = $property?->host?->hostProfile;
        $rating = $hostProfile?->rating_average ?: $property?->host?->rating_as_host;

        return [
            'id' => $place->id,
            'href' => route('places.show', ['locale' => app()->getLocale(), 'sleepingPlace' => $place]),
            'title' => $this->title($place),
            'location' => $this->location($property),
            'room_type' => $this->label($room?->type),
            'sleeping_place_type' => $this->label($place->type),
            'gender_policy' => $this->label($room?->gender_policy ?: $room?->gender_type),
            'price_per_night' => (float) $place->base_price_per_night,
            'currency' => $place->currency,
            'total_price' => $quote['total_amount'] ?? null,
            'nights' => $this->nights,
            'people_in_room' => $room?->max_guests,
            'rating' => $rating ? (float) $rating : null,
            'reviews_count' => $hostProfile?->reviews_count ?? 0,
            'image_url' => $media instanceof MediaItem ? $media->imageUrl('mobile') : null,
            'image_alt' => $media instanceof MediaItem ? ($media->localizedCaption() ?: $this->title($place)) : $this->title($place),
            'amenities' => $this->keyAmenityLabels($place),
            'hints' => $this->compatibilityHints($place, $guest),
            'instant_booking' => (bool) $place->instant_booking_enabled,
            'requires_approval' => (bool) $place->requires_host_approval,
            'verified_host' => $hostProfile?->verified_at !== null || (bool) $property?->host?->identity_verified,
        ];
    }

    private function priceQuote(SleepingPlace $place, ?User $guest): ?array
    {
        if (! $this->dateRange()) {
            return null;
        }

        $guest ??= new User(['name' => 'Guest']);

        return app(PricingService::class)
            ->calculate($guest, $place, $this->checkIn, $this->checkOut, max(1, $this->guestsCount))
            ->toArray();
    }

    private function title(SleepingPlace $place): string
    {
        $translation = $this->resolver()->resolve($place->translations, app()->getLocale());

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    private function location(?Property $property): string
    {
        $parts = array_filter([
            $property?->cityModel?->name ?: (is_string($property?->getAttribute('city')) ? $property->getAttribute('city') : null),
            $property?->district,
        ]);

        return $parts === [] ? __('search.card.location_missing') : implode(', ', $parts);
    }

    private function label(mixed $value): string
    {
        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        return $value ? __('search.card.unknown') : __('search.card.not_set');
    }

    /**
     * @return list<string>
     */
    private function keyAmenityLabels(SleepingPlace $place): array
    {
        $labels = $this->amenitiesForCard($place)
            ->unique('slug')
            ->filter(fn (Amenity $amenity): bool => in_array($amenity->slug, self::CARD_AMENITY_SLUGS, true))
            ->map(fn (Amenity $amenity): string => $this->amenityLabel($amenity))
            ->values();

        if ($place->has_bedding) {
            $labels->push(__('search.card.amenities.bedding'));
        }

        if ($place->has_towel) {
            $labels->push(__('search.card.amenities.towel'));
        }

        return $labels->unique()->take(4)->values()->all();
    }

    /**
     * @return Collection<int, Amenity>
     */
    private function amenitiesForCard(SleepingPlace $place): Collection
    {
        return collect()
            ->merge($place->property?->amenities ?? [])
            ->merge($place->room?->amenities ?? [])
            ->merge($place->amenities ?? []);
    }

    private function amenityLabel(Amenity $amenity): string
    {
        $translation = $this->resolver()->resolve($amenity->translations, app()->getLocale());

        return $translation?->name ?: __('listing.legacy_amenities.other');
    }

    /**
     * @return list<string>
     */
    private function compatibilityHints(SleepingPlace $place, ?User $guest): array
    {
        if ($guest?->guestPreference) {
            $result = app(CompatibilityService::class)->evaluate(
                $guest->guestPreference,
                $place->property,
                $place->room,
                $place,
            );

            return collect([
                __('search.card.compatibility_fit', ['level' => __('compatibility.fit_levels.'.$result['fit_level'])]),
                $result['positive_reasons'][0] ?? null,
                $result['warning_reasons'][0] ?? null,
            ])->filter()->take(3)->values()->all();
        }

        return collect([
            $place->deposit_amount <= 0 ? __('search.card.hints.no_deposit') : null,
            $place->instant_booking_enabled ? __('search.card.hints.instant') : null,
            $place->has_locker ? __('search.card.hints.locker') : null,
            $this->isLowerBunk($place) ? __('search.card.hints.lower_bunk') : null,
        ])->filter()->take(3)->values()->all();
    }

    private function isLowerBunk(SleepingPlace $place): bool
    {
        return $place->type === SleepingPlaceType::BunkBottom
            || in_array((string) $place->bunk_level, ['bottom', 'lower', '1'], true);
    }

    private function decimal(string $value): ?float
    {
        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return max(0.0, (float) $value);
    }

    private function integer(string $value): ?int
    {
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return max(1, (int) $value);
    }

    /**
     * @return list<string>
     */
    private function filterPropertyNames(): array
    {
        return [
            'city',
            'cityQuery',
            'district',
            'checkIn',
            'checkOut',
            'guestsCount',
            'priceMin',
            'priceMax',
            'currency',
            'propertyType',
            'roomType',
            'sleepingPlaceType',
            'roomGenderPolicy',
            'instantBooking',
            'hostApprovalRequired',
            'wifi',
            'kitchen',
            'washingMachine',
            'locker',
            'lowerBunkOnly',
            'notUpperBunk',
            'beddingIncluded',
            'towelIncluded',
            'workspace',
            'lateCheckIn',
            'selfCheckIn',
            'quietHours',
            'noSmoking',
            'petsAllowed',
            'noPets',
            'noMixedRoom',
            'maxPeopleInRoom',
            'elevator',
            'parking',
            'highRating',
            'verifiedHost',
            'hasReviews',
            'noDeposit',
            'freeCancellation',
            'longStayAllowed',
            'availableToday',
            'flexibleDates',
            'sort',
        ];
    }
}
