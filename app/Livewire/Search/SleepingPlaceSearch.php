<?php

namespace App\Livewire\Search;

use App\Data\Listings\ListingCardContext;
use App\Enums\GenderType;
use App\Enums\PropertyType;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceType;
use App\Models\City;
use App\Services\Geo\GeoSearchService;
use App\Services\Listings\ListingCardQueryService;
use App\Services\Listings\ListingCardService;
use App\Support\Geo\GeoNameNormalizer;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class SleepingPlaceSearch extends Component
{
    private const INITIAL_VISIBLE_COUNT = 12;

    private const MAX_VISIBLE_COUNT = 60;

    private const NEW_HOME_REPAIR_STATES = ['new'];

    private const OLD_HOME_REPAIR_STATES = ['old', 'worn', 'dated', 'needs_repair'];

    private const GOOD_REPAIR_STATES = ['good', 'high', 'new', 'renovated'];

    private const SIMPLE_REPAIR_STATES = ['simple', 'basic', 'cosmetic'];

    private const QUIET_WINDOW_NOISE_LEVELS = ['quiet', 'low'];

    private const COURTYARD_WINDOW_VALUES = ['courtyard', 'yard', 'inner_yard', 'garden'];

    private const EMPTY_WINDOW_VIEW_VALUES = ['', 'none', 'no_view'];

    private const WORKER_ROOM_FORMATS = ['worker', 'remote_work'];

    private const BRIGHT_ROOM_LIGHT_LEVELS = ['bright', 'good', 'high'];

    private const NEAR_CENTER_DISTANCE_METERS = 2000;

    private const NEAR_CENTER_WALK_MINUTES = 25;

    private const NEAR_CENTER_TRANSPORT_MINUTES = 20;

    private const NEAR_METRO_DISTANCE_METERS = 1500;

    private const NEAR_BUS_DISTANCE_METERS = 800;

    private const NEAR_RAILWAY_DISTANCE_METERS = 3000;

    private const NEAR_AIRPORT_DISTANCE_METERS = 20000;

    private const GOOD_TRANSPORT_LEVELS = ['good', 'high', 'excellent'];

    private const QUIET_DISTRICT_LEVELS = ['quiet', 'low'];

    private const SAFE_DISTRICT_LEVELS = ['good', 'high', 'safe'];

    private const GOOD_STREET_LIGHTING_LEVELS = ['good', 'high', 'bright'];

    private const CLEAN_PROPERTY_LEVELS = ['good', 'high', 'clean'];

    private const NORMAL_HUMIDITY_LEVELS = ['normal', 'comfortable', 'dry'];

    private const COMFORTABLE_WINTER_LEVELS = ['normal', 'warm', 'comfortable'];

    private const COMFORTABLE_SUMMER_LEVELS = ['normal', 'cool', 'comfortable'];

    private const RULE_FILTER_SLUGS = [
        'smokingAllowed' => ['smoking_allowed', 'smoking_only_outside', 'smoking_only_on_balcony'],
        'noSmoking' => ['no_smoking'],
        'petsAllowed' => ['pets_allowed', 'pets_by_request'],
        'noPets' => ['no_pets'],
        'visitorsAllowed' => ['visitors_allowed', 'visitors_by_agreement'],
        'noVisitors' => ['no_visitors', 'no_overnight_visitors'],
        'couplesAllowed' => ['couples_allowed'],
        'childrenAllowed' => ['children_allowed'],
        'adultsOnly' => ['adults_only'],
        'cookingAllowed' => ['cooking_allowed', 'clean_dishes_after_use'],
        'nightCookingAllowed' => ['night_cooking_allowed'],
        'noNoiseAfterTime' => ['no_noise_after_time', 'quiet_hours_after_22', 'no_loud_calls_at_night', 'no_loud_music'],
        'quietHours' => ['quiet_hours_after_22'],
        'washingAtNightAllowed' => ['washing_machine_at_night_allowed'],
        'noWashingAtNight' => ['no_washing_machine_at_night'],
        'nightWorkAllowed' => ['night_work_allowed'],
        'noMainLightAtNight' => ['no_main_light_at_night'],
        'lateReturnAllowed' => ['late_entry_allowed'],
        'entryTimeLimit' => ['entry_time_limit'],
        'cleaningRules' => ['cleaning_rules', 'clean_dishes_after_use', 'take_out_trash'],
        'cleaningSchedule' => ['cleaning_schedule'],
        'removeShoes' => ['remove_shoes_inside'],
        'noAlcohol' => ['no_alcohol'],
        'noParties' => ['no_parties'],
        'noOutsiders' => ['no_unregistered_people'],
        'noLoudMusic' => ['no_loud_music'],
        'noFoodStorageInRoom' => ['no_food_storage_in_room'],
        'noEatingOnBed' => ['no_eating_on_bed'],
        'noSleepingPlaceChange' => ['no_sleeping_place_changes_without_permission'],
        'noOtherShelves' => ['do_not_occupy_other_shelves'],
        'noOtherPeopleThings' => ['do_not_use_other_guests_things', 'do_not_use_other_residents_things'],
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

    #[Url(as: 'private_room', except: false)]
    public bool $privateRoom = false;

    #[Url(as: 'shared_room', except: false)]
    public bool $sharedRoom = false;

    #[Url(as: 'male_room', except: false)]
    public bool $maleRoom = false;

    #[Url(as: 'female_room', except: false)]
    public bool $femaleRoom = false;

    #[Url(as: 'mixed_room', except: false)]
    public bool $mixedRoom = false;

    #[Url(as: 'student_room', except: false)]
    public bool $studentRoom = false;

    #[Url(as: 'tourist_room', except: false)]
    public bool $touristRoom = false;

    #[Url(as: 'worker_room', except: false)]
    public bool $workerRoom = false;

    #[Url(as: 'long_stay_room', except: false)]
    public bool $longStayRoom = false;

    #[Url(as: 'one_guest_room', except: false)]
    public bool $oneGuestRoom = false;

    #[Url(as: 'room_up_to_2', except: false)]
    public bool $roomUpToTwoGuests = false;

    #[Url(as: 'room_up_to_4', except: false)]
    public bool $roomUpToFourGuests = false;

    #[Url(as: 'room_up_to_6', except: false)]
    public bool $roomUpToSixGuests = false;

    #[Url(as: 'room_over_6', except: false)]
    public bool $roomMoreThanSixGuests = false;

    #[Url(as: 'room_window', except: false)]
    public bool $roomWithWindow = false;

    #[Url(as: 'room_no_window', except: false)]
    public bool $roomWithoutWindow = false;

    #[Url(as: 'room_lock', except: false)]
    public bool $roomWithLock = false;

    #[Url(as: 'room_no_lock', except: false)]
    public bool $roomWithoutLock = false;

    #[Url(as: 'room_ac', except: false)]
    public bool $roomAirConditioning = false;

    #[Url(as: 'room_heating', except: false)]
    public bool $roomHeating = false;

    #[Url(as: 'room_desk', except: false)]
    public bool $roomDesk = false;

    #[Url(as: 'room_wardrobe', except: false)]
    public bool $roomWardrobe = false;

    #[Url(as: 'room_locker', except: false)]
    public bool $roomLocker = false;

    #[Url(as: 'room_balcony', except: false)]
    public bool $roomBalcony = false;

    #[Url(as: 'quiet_room', except: false)]
    public bool $quietRoom = false;

    #[Url(as: 'bright_room', except: false)]
    public bool $brightRoom = false;

    #[Url(as: 'non_pass_through', except: false)]
    public bool $nonPassThroughRoom = false;

    #[Url(as: 'pass_through', except: false)]
    public bool $passThroughRoom = false;

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

    #[Url(as: 'smoking', except: false)]
    public bool $smokingAllowed = false;

    #[Url(as: 'quiet', except: false)]
    public bool $quietHours = false;

    #[Url(as: 'no_smoking', except: false)]
    public bool $noSmoking = false;

    #[Url(as: 'pets', except: false)]
    public bool $petsAllowed = false;

    #[Url(as: 'no_pets', except: false)]
    public bool $noPets = false;

    #[Url(as: 'visitors', except: false)]
    public bool $visitorsAllowed = false;

    #[Url(as: 'no_visitors', except: false)]
    public bool $noVisitors = false;

    #[Url(as: 'couples', except: false)]
    public bool $couplesAllowed = false;

    #[Url(as: 'children', except: false)]
    public bool $childrenAllowed = false;

    #[Url(as: 'adults_only', except: false)]
    public bool $adultsOnly = false;

    #[Url(as: 'cook', except: false)]
    public bool $cookingAllowed = false;

    #[Url(as: 'cook_night', except: false)]
    public bool $nightCookingAllowed = false;

    #[Url(as: 'no_noise_after_time', except: false)]
    public bool $noNoiseAfterTime = false;

    #[Url(as: 'wash_night', except: false)]
    public bool $washingAtNightAllowed = false;

    #[Url(as: 'no_wash_night', except: false)]
    public bool $noWashingAtNight = false;

    #[Url(as: 'work_night', except: false)]
    public bool $nightWorkAllowed = false;

    #[Url(as: 'no_light_night', except: false)]
    public bool $noMainLightAtNight = false;

    #[Url(as: 'late_return', except: false)]
    public bool $lateReturnAllowed = false;

    #[Url(as: 'entry_time_limit', except: false)]
    public bool $entryTimeLimit = false;

    #[Url(as: 'cleaning_rules', except: false)]
    public bool $cleaningRules = false;

    #[Url(as: 'cleaning_schedule', except: false)]
    public bool $cleaningSchedule = false;

    #[Url(as: 'shoes_off', except: false)]
    public bool $removeShoes = false;

    #[Url(as: 'no_alcohol', except: false)]
    public bool $noAlcohol = false;

    #[Url(as: 'no_parties', except: false)]
    public bool $noParties = false;

    #[Url(as: 'no_outsiders', except: false)]
    public bool $noOutsiders = false;

    #[Url(as: 'no_loud_music', except: false)]
    public bool $noLoudMusic = false;

    #[Url(as: 'no_food_room', except: false)]
    public bool $noFoodStorageInRoom = false;

    #[Url(as: 'no_eating_bed', except: false)]
    public bool $noEatingOnBed = false;

    #[Url(as: 'no_place_change', except: false)]
    public bool $noSleepingPlaceChange = false;

    #[Url(as: 'no_other_shelves', except: false)]
    public bool $noOtherShelves = false;

    #[Url(as: 'no_other_things', except: false)]
    public bool $noOtherPeopleThings = false;

    #[Url(as: 'no_mixed', except: false)]
    public bool $noMixedRoom = false;

    #[Url(as: 'max_people', except: '')]
    public string $maxPeopleInRoom = '';

    #[Url(as: 'elevator', except: false)]
    public bool $elevator = false;

    #[Url(as: 'no_elevator', except: false)]
    public bool $withoutElevator = false;

    #[Url(as: 'new_home', except: false)]
    public bool $newHome = false;

    #[Url(as: 'old_home', except: false)]
    public bool $oldHome = false;

    #[Url(as: 'good_repair', except: false)]
    public bool $goodRepair = false;

    #[Url(as: 'simple_repair', except: false)]
    public bool $simpleRepair = false;

    #[Url(as: 'private_entrance', except: false)]
    public bool $privateEntrance = false;

    #[Url(as: 'shared_entrance', except: false)]
    public bool $sharedEntrance = false;

    #[Url(as: 'first_floor', except: false)]
    public bool $firstFloor = false;

    #[Url(as: 'last_floor', except: false)]
    public bool $lastFloor = false;

    #[Url(as: 'not_first_floor', except: false)]
    public bool $notFirstFloor = false;

    #[Url(as: 'not_last_floor', except: false)]
    public bool $notLastFloor = false;

    #[Url(as: 'balcony', except: false)]
    public bool $withBalcony = false;

    #[Url(as: 'no_balcony', except: false)]
    public bool $withoutBalcony = false;

    #[Url(as: 'window_view', except: false)]
    public bool $windowView = false;

    #[Url(as: 'quiet_windows', except: false)]
    public bool $quietWindows = false;

    #[Url(as: 'courtyard_windows', except: false)]
    public bool $courtyardWindows = false;

    #[Url(as: 'parking', except: false)]
    public bool $parking = false;

    #[Url(as: 'near_center', except: false)]
    public bool $nearCenter = false;

    #[Url(as: 'near_metro', except: false)]
    public bool $nearMetro = false;

    #[Url(as: 'near_bus', except: false)]
    public bool $nearBusStop = false;

    #[Url(as: 'near_shop', except: false)]
    public bool $nearShop = false;

    #[Url(as: 'near_pharmacy', except: false)]
    public bool $nearPharmacy = false;

    #[Url(as: 'near_hospital', except: false)]
    public bool $nearHospital = false;

    #[Url(as: 'near_university', except: false)]
    public bool $nearUniversity = false;

    #[Url(as: 'near_railway', except: false)]
    public bool $nearRailwayStation = false;

    #[Url(as: 'near_airport', except: false)]
    public bool $nearAirport = false;

    #[Url(as: 'transport', except: false)]
    public bool $easyTransport = false;

    #[Url(as: 'quiet_district', except: false)]
    public bool $quietDistrict = false;

    #[Url(as: 'safe_district', except: false)]
    public bool $safeDistrict = false;

    #[Url(as: 'street_light', except: false)]
    public bool $goodStreetLighting = false;

    #[Url(as: 'free_parking', except: false)]
    public bool $freeParking = false;

    #[Url(as: 'paid_parking', except: false)]
    public bool $paidParking = false;

    #[Url(as: 'clean_property', except: false)]
    public bool $cleanProperty = false;

    #[Url(as: 'no_insects', except: false)]
    public bool $noInsects = false;

    #[Url(as: 'no_mold', except: false)]
    public bool $noMold = false;

    #[Url(as: 'normal_humidity', except: false)]
    public bool $normalHumidity = false;

    #[Url(as: 'warm_winter', except: false)]
    public bool $comfortableWinter = false;

    #[Url(as: 'cool_summer', except: false)]
    public bool $comfortableSummer = false;

    #[Url(as: 'quiet_property', except: false)]
    public bool $quietProperty = false;

    #[Url(as: 'bright_property', except: false)]
    public bool $brightProperty = false;

    #[Url(as: 'door_code', except: false)]
    public bool $doorCodeAccess = false;

    #[Url(as: 'electronic_lock', except: false)]
    public bool $electronicLockAccess = false;

    #[Url(as: 'key_safe', except: false)]
    public bool $keySafeAccess = false;

    #[Url(as: 'access_24_7', except: false)]
    public bool $access247 = false;

    #[Url(as: 'no_night_restrictions', except: false)]
    public bool $noNightEntryRestrictions = false;

    #[Url(as: 'guest_rules', except: false)]
    public bool $guestRules = false;

    #[Url(as: 'courier_rules', except: false)]
    public bool $courierRules = false;

    #[Url(as: 'delivery', except: false)]
    public bool $deliveryAvailable = false;

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
            'privateRoom',
            'sharedRoom',
            'maleRoom',
            'femaleRoom',
            'mixedRoom',
            'studentRoom',
            'touristRoom',
            'workerRoom',
            'longStayRoom',
            'oneGuestRoom',
            'roomUpToTwoGuests',
            'roomUpToFourGuests',
            'roomUpToSixGuests',
            'roomMoreThanSixGuests',
            'roomWithWindow',
            'roomWithoutWindow',
            'roomWithLock',
            'roomWithoutLock',
            'roomAirConditioning',
            'roomHeating',
            'roomDesk',
            'roomWardrobe',
            'roomLocker',
            'roomBalcony',
            'quietRoom',
            'brightRoom',
            'nonPassThroughRoom',
            'passThroughRoom',
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
            ...array_keys(self::RULE_FILTER_SLUGS),
            'noMixedRoom',
            'maxPeopleInRoom',
            'elevator',
            'withoutElevator',
            'newHome',
            'oldHome',
            'goodRepair',
            'simpleRepair',
            'privateEntrance',
            'sharedEntrance',
            'firstFloor',
            'lastFloor',
            'notFirstFloor',
            'notLastFloor',
            'withBalcony',
            'withoutBalcony',
            'windowView',
            'quietWindows',
            'courtyardWindows',
            'parking',
            'nearCenter',
            'nearMetro',
            'nearBusStop',
            'nearShop',
            'nearPharmacy',
            'nearHospital',
            'nearUniversity',
            'nearRailwayStation',
            'nearAirport',
            'easyTransport',
            'quietDistrict',
            'safeDistrict',
            'goodStreetLighting',
            'freeParking',
            'paidParking',
            'cleanProperty',
            'noInsects',
            'noMold',
            'normalHumidity',
            'comfortableWinter',
            'comfortableSummer',
            'quietProperty',
            'brightProperty',
            'doorCodeAccess',
            'electronicLockAccess',
            'keySafeAccess',
            'access247',
            'noNightEntryRestrictions',
            'guestRules',
            'courierRules',
            'deliveryAvailable',
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
     * @return array{cards:list<array<string,mixed>>,has_more:bool,showing:int,total:int,total_is_exact:bool}
     */
    #[Computed]
    public function searchResults(): array
    {
        $context = $this->listingCardContext();
        $query = $this->searchQuery();
        $exactTotal = $this->shouldComputeExactSearchTotal()
            ? (clone $query)->reorder()->count('sleeping_places.id')
            : null;

        $canProbeForMore = $this->visibleCount < self::MAX_VISIBLE_COUNT;
        $probeLimit = $canProbeForMore ? $this->visibleCount + 1 : $this->visibleCount;
        $places = $query
            ->limit($probeLimit)
            ->get();
        $hasAdditionalRow = $canProbeForMore && $places->count() > $this->visibleCount;
        $visiblePlaces = $places->take($this->visibleCount);

        $cards = app(ListingCardService::class)
            ->buildMany($visiblePlaces, $context)
            ->map(fn ($card): array => $card->toArray())
            ->values()
            ->all();
        $totalIsExact = $exactTotal !== null || ! $hasAdditionalRow;
        $total = $exactTotal ?? ($hasAdditionalRow ? $this->visibleCount + 1 : count($cards));

        return [
            'cards' => $cards,
            'has_more' => $hasAdditionalRow,
            'showing' => count($cards),
            'total' => $total,
            'total_is_exact' => $totalIsExact,
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

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    public function premiseFilterOptions(): array
    {
        return [
            ['property' => 'newHome', 'label' => __('search.filters_flags.new_home'), 'icon' => 'home-modern'],
            ['property' => 'oldHome', 'label' => __('search.filters_flags.old_home'), 'icon' => 'home-modern'],
            ['property' => 'goodRepair', 'label' => __('search.filters_flags.good_repair'), 'icon' => 'wrench-screwdriver'],
            ['property' => 'simpleRepair', 'label' => __('search.filters_flags.simple_repair'), 'icon' => 'wrench-screwdriver'],
            ['property' => 'privateEntrance', 'label' => __('search.filters_flags.private_entrance'), 'icon' => 'key'],
            ['property' => 'sharedEntrance', 'label' => __('search.filters_flags.shared_entrance'), 'icon' => 'key'],
            ['property' => 'withoutElevator', 'label' => __('search.filters_flags.without_elevator'), 'icon' => 'building-office'],
            ['property' => 'firstFloor', 'label' => __('search.filters_flags.first_floor'), 'icon' => 'building-office'],
            ['property' => 'lastFloor', 'label' => __('search.filters_flags.last_floor'), 'icon' => 'building-office'],
            ['property' => 'notFirstFloor', 'label' => __('search.filters_flags.not_first_floor'), 'icon' => 'building-office'],
            ['property' => 'notLastFloor', 'label' => __('search.filters_flags.not_last_floor'), 'icon' => 'building-office'],
            ['property' => 'withBalcony', 'label' => __('search.filters_flags.with_balcony'), 'icon' => 'sparkles'],
            ['property' => 'withoutBalcony', 'label' => __('search.filters_flags.without_balcony'), 'icon' => 'sparkles'],
            ['property' => 'windowView', 'label' => __('search.filters_flags.window_view'), 'icon' => 'sun'],
            ['property' => 'quietWindows', 'label' => __('search.filters_flags.quiet_windows'), 'icon' => 'speaker-x-mark'],
            ['property' => 'courtyardWindows', 'label' => __('search.filters_flags.courtyard_windows'), 'icon' => 'squares-2x2'],
        ];
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    public function locationFilterOptions(): array
    {
        return [
            ['property' => 'nearCenter', 'label' => __('search.filters_flags.near_center'), 'icon' => 'map-pin'],
            ['property' => 'nearMetro', 'label' => __('search.filters_flags.near_metro'), 'icon' => 'map-pin'],
            ['property' => 'nearBusStop', 'label' => __('search.filters_flags.near_bus_stop'), 'icon' => 'map-pin'],
            ['property' => 'nearShop', 'label' => __('search.filters_flags.near_shop'), 'icon' => 'shopping-bag'],
            ['property' => 'nearPharmacy', 'label' => __('search.filters_flags.near_pharmacy'), 'icon' => 'plus-circle'],
            ['property' => 'nearHospital', 'label' => __('search.filters_flags.near_hospital'), 'icon' => 'plus-circle'],
            ['property' => 'nearUniversity', 'label' => __('search.filters_flags.near_university'), 'icon' => 'academic-cap'],
            ['property' => 'nearRailwayStation', 'label' => __('search.filters_flags.near_railway_station'), 'icon' => 'map-pin'],
            ['property' => 'nearAirport', 'label' => __('search.filters_flags.near_airport'), 'icon' => 'paper-airplane'],
            ['property' => 'easyTransport', 'label' => __('search.filters_flags.easy_transport'), 'icon' => 'arrows-right-left'],
            ['property' => 'quietDistrict', 'label' => __('search.filters_flags.quiet_district'), 'icon' => 'speaker-x-mark'],
            ['property' => 'safeDistrict', 'label' => __('search.filters_flags.safe_district'), 'icon' => 'shield-check'],
            ['property' => 'goodStreetLighting', 'label' => __('search.filters_flags.good_street_lighting'), 'icon' => 'sun'],
            ['property' => 'freeParking', 'label' => __('search.filters_flags.free_parking'), 'icon' => 'truck'],
            ['property' => 'paidParking', 'label' => __('search.filters_flags.paid_parking'), 'icon' => 'truck'],
        ];
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    public function conditionFilterOptions(): array
    {
        return [
            ['property' => 'cleanProperty', 'label' => __('search.filters_flags.clean_property'), 'icon' => 'sparkles'],
            ['property' => 'noInsects', 'label' => __('search.filters_flags.no_insects'), 'icon' => 'shield-check'],
            ['property' => 'noMold', 'label' => __('search.filters_flags.no_mold'), 'icon' => 'shield-check'],
            ['property' => 'normalHumidity', 'label' => __('search.filters_flags.normal_humidity'), 'icon' => 'sparkles'],
            ['property' => 'comfortableWinter', 'label' => __('search.filters_flags.comfortable_winter'), 'icon' => 'fire'],
            ['property' => 'comfortableSummer', 'label' => __('search.filters_flags.comfortable_summer'), 'icon' => 'sun'],
            ['property' => 'quietProperty', 'label' => __('search.filters_flags.quiet_property'), 'icon' => 'speaker-x-mark'],
            ['property' => 'brightProperty', 'label' => __('search.filters_flags.bright_property'), 'icon' => 'sun'],
        ];
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    public function accessFilterOptions(): array
    {
        return [
            ['property' => 'doorCodeAccess', 'label' => __('search.filters_flags.door_code_access'), 'icon' => 'key'],
            ['property' => 'electronicLockAccess', 'label' => __('search.filters_flags.electronic_lock_access'), 'icon' => 'lock-closed'],
            ['property' => 'keySafeAccess', 'label' => __('search.filters_flags.key_safe_access'), 'icon' => 'key'],
            ['property' => 'access247', 'label' => __('search.filters_flags.access_24_7'), 'icon' => 'clock'],
            ['property' => 'noNightEntryRestrictions', 'label' => __('search.filters_flags.no_night_entry_restrictions'), 'icon' => 'moon'],
            ['property' => 'guestRules', 'label' => __('search.filters_flags.guest_rules'), 'icon' => 'document-check'],
            ['property' => 'courierRules', 'label' => __('search.filters_flags.courier_rules'), 'icon' => 'document-check'],
            ['property' => 'deliveryAvailable', 'label' => __('search.filters_flags.delivery_available'), 'icon' => 'truck'],
        ];
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    public function roomFilterOptions(): array
    {
        return [
            ['property' => 'privateRoom', 'label' => __('search.filters_flags.private_room'), 'icon' => 'user'],
            ['property' => 'sharedRoom', 'label' => __('search.filters_flags.shared_room'), 'icon' => 'users'],
            ['property' => 'maleRoom', 'label' => __('search.filters_flags.male_room'), 'icon' => 'user'],
            ['property' => 'femaleRoom', 'label' => __('search.filters_flags.female_room'), 'icon' => 'user'],
            ['property' => 'mixedRoom', 'label' => __('search.filters_flags.mixed_room'), 'icon' => 'users'],
            ['property' => 'studentRoom', 'label' => __('search.filters_flags.student_room'), 'icon' => 'academic-cap'],
            ['property' => 'touristRoom', 'label' => __('search.filters_flags.tourist_room'), 'icon' => 'map'],
            ['property' => 'workerRoom', 'label' => __('search.filters_flags.worker_room'), 'icon' => 'briefcase'],
            ['property' => 'longStayRoom', 'label' => __('search.filters_flags.long_stay_room'), 'icon' => 'calendar-days'],
            ['property' => 'oneGuestRoom', 'label' => __('search.filters_flags.one_guest_room'), 'icon' => 'user'],
            ['property' => 'roomUpToTwoGuests', 'label' => __('search.filters_flags.room_up_to_2'), 'icon' => 'users'],
            ['property' => 'roomUpToFourGuests', 'label' => __('search.filters_flags.room_up_to_4'), 'icon' => 'users'],
            ['property' => 'roomUpToSixGuests', 'label' => __('search.filters_flags.room_up_to_6'), 'icon' => 'users'],
            ['property' => 'roomMoreThanSixGuests', 'label' => __('search.filters_flags.room_more_than_6'), 'icon' => 'users'],
            ['property' => 'roomWithWindow', 'label' => __('search.filters_flags.room_window'), 'icon' => 'window'],
            ['property' => 'roomWithoutWindow', 'label' => __('search.filters_flags.room_without_window'), 'icon' => 'window'],
            ['property' => 'roomWithLock', 'label' => __('search.filters_flags.room_lock'), 'icon' => 'key'],
            ['property' => 'roomWithoutLock', 'label' => __('search.filters_flags.room_without_lock'), 'icon' => 'key'],
            ['property' => 'roomAirConditioning', 'label' => __('search.filters_flags.room_ac'), 'icon' => 'sparkles'],
            ['property' => 'roomHeating', 'label' => __('search.filters_flags.room_heating'), 'icon' => 'fire'],
            ['property' => 'roomDesk', 'label' => __('search.filters_flags.room_desk'), 'icon' => 'computer-desktop'],
            ['property' => 'roomWardrobe', 'label' => __('search.filters_flags.room_wardrobe'), 'icon' => 'archive-box'],
            ['property' => 'roomLocker', 'label' => __('search.filters_flags.room_locker'), 'icon' => 'lock-closed'],
            ['property' => 'roomBalcony', 'label' => __('search.filters_flags.room_balcony'), 'icon' => 'sparkles'],
            ['property' => 'quietRoom', 'label' => __('search.filters_flags.quiet_room'), 'icon' => 'speaker-x-mark'],
            ['property' => 'brightRoom', 'label' => __('search.filters_flags.bright_room'), 'icon' => 'sun'],
            ['property' => 'nonPassThroughRoom', 'label' => __('search.filters_flags.non_pass_through_room'), 'icon' => 'arrows-right-left'],
            ['property' => 'passThroughRoom', 'label' => __('search.filters_flags.pass_through_room'), 'icon' => 'arrows-right-left'],
        ];
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    public function ruleFilterOptions(): array
    {
        return [
            ['property' => 'smokingAllowed', 'label' => __('search.filters_flags.smoking_allowed'), 'icon' => 'sparkles'],
            ['property' => 'noSmoking', 'label' => __('search.filters_flags.no_smoking'), 'icon' => 'scale'],
            ['property' => 'petsAllowed', 'label' => __('search.filters_flags.pets_allowed'), 'icon' => 'sparkles'],
            ['property' => 'noPets', 'label' => __('search.filters_flags.no_pets'), 'icon' => 'scale'],
            ['property' => 'visitorsAllowed', 'label' => __('search.filters_flags.visitors_allowed'), 'icon' => 'users'],
            ['property' => 'noVisitors', 'label' => __('search.filters_flags.no_visitors'), 'icon' => 'scale'],
            ['property' => 'couplesAllowed', 'label' => __('search.filters_flags.couples_allowed'), 'icon' => 'users'],
            ['property' => 'childrenAllowed', 'label' => __('search.filters_flags.children_allowed'), 'icon' => 'users'],
            ['property' => 'adultsOnly', 'label' => __('search.filters_flags.adults_only'), 'icon' => 'users'],
            ['property' => 'cookingAllowed', 'label' => __('search.filters_flags.cooking_allowed'), 'icon' => 'sparkles'],
            ['property' => 'nightCookingAllowed', 'label' => __('search.filters_flags.night_cooking_allowed'), 'icon' => 'moon'],
            ['property' => 'noNoiseAfterTime', 'label' => __('search.filters_flags.no_noise_after_time'), 'icon' => 'speaker-x-mark'],
            ['property' => 'quietHours', 'label' => __('search.filters_flags.quiet_hours'), 'icon' => 'speaker-x-mark'],
            ['property' => 'washingAtNightAllowed', 'label' => __('search.filters_flags.washing_at_night_allowed'), 'icon' => 'sparkles'],
            ['property' => 'noWashingAtNight', 'label' => __('search.filters_flags.no_washing_at_night'), 'icon' => 'scale'],
            ['property' => 'nightWorkAllowed', 'label' => __('search.filters_flags.night_work_allowed'), 'icon' => 'computer-desktop'],
            ['property' => 'noMainLightAtNight', 'label' => __('search.filters_flags.no_main_light_at_night'), 'icon' => 'scale'],
            ['property' => 'lateReturnAllowed', 'label' => __('search.filters_flags.late_return_allowed'), 'icon' => 'clock'],
            ['property' => 'entryTimeLimit', 'label' => __('search.filters_flags.entry_time_limit'), 'icon' => 'clock'],
            ['property' => 'cleaningRules', 'label' => __('search.filters_flags.cleaning_rules'), 'icon' => 'sparkles'],
            ['property' => 'cleaningSchedule', 'label' => __('search.filters_flags.cleaning_schedule'), 'icon' => 'calendar-days'],
            ['property' => 'removeShoes', 'label' => __('search.filters_flags.remove_shoes'), 'icon' => 'home-modern'],
            ['property' => 'noAlcohol', 'label' => __('search.filters_flags.no_alcohol'), 'icon' => 'scale'],
            ['property' => 'noParties', 'label' => __('search.filters_flags.no_parties'), 'icon' => 'scale'],
            ['property' => 'noOutsiders', 'label' => __('search.filters_flags.no_outsiders'), 'icon' => 'scale'],
            ['property' => 'noLoudMusic', 'label' => __('search.filters_flags.no_loud_music'), 'icon' => 'speaker-x-mark'],
            ['property' => 'noFoodStorageInRoom', 'label' => __('search.filters_flags.no_food_storage_in_room'), 'icon' => 'archive-box'],
            ['property' => 'noEatingOnBed', 'label' => __('search.filters_flags.no_eating_on_bed'), 'icon' => 'scale'],
            ['property' => 'noSleepingPlaceChange', 'label' => __('search.filters_flags.no_sleeping_place_change'), 'icon' => 'arrows-right-left'],
            ['property' => 'noOtherShelves', 'label' => __('search.filters_flags.no_other_shelves'), 'icon' => 'archive-box'],
            ['property' => 'noOtherPeopleThings', 'label' => __('search.filters_flags.no_other_people_things'), 'icon' => 'lock-closed'],
        ];
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
     * @return array{cards:list<array<string,mixed>>,has_more:bool,showing:int,total:int,total_is_exact:bool}
     */
    private function resultsForView(): array
    {
        return array_merge([
            'cards' => [],
            'has_more' => false,
            'showing' => 0,
            'total' => 0,
            'total_is_exact' => true,
        ], $this->searchResults);
    }

    private function shouldComputeExactSearchTotal(): bool
    {
        return $this->filtersOpen;
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
        $this->applyPremiseCriteriaFilters($query);
        $this->applyRoomCriteriaFilters($query);
        $this->applyRuleCriteriaFilters($query);
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
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.type', $this->propertyType)
                    ->orWhere('search_properties.property_type', $this->propertyType);
            });
        }

        if ($this->roomType !== '') {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.type', $this->roomType)
                    ->orWhere('search_rooms.room_type', $this->roomType);
            });
        }

        if ($this->sleepingPlaceType !== '') {
            $query->where(function (Builder $builder): void {
                $builder->where('sleeping_places.type', $this->sleepingPlaceType)
                    ->orWhere('sleeping_places.sleeping_place_type', $this->sleepingPlaceType);
            });
        }

        if ($this->roomGenderPolicy !== '') {
            $query->where('search_rooms.gender_policy', $this->roomGenderPolicy);
        }

        $query->where('sleeping_places.max_guests', '>=', max(1, $this->guestsCount));
    }

    private function applyPremiseCriteriaFilters(Builder $query): void
    {
        if ($this->newHome) {
            $this->wherePropertyRepairStates($query, self::NEW_HOME_REPAIR_STATES);
        }

        if ($this->oldHome) {
            $this->wherePropertyRepairStates($query, self::OLD_HOME_REPAIR_STATES);
        }

        if ($this->goodRepair) {
            $this->wherePropertyRepairStates($query, self::GOOD_REPAIR_STATES);
        }

        if ($this->simpleRepair) {
            $this->wherePropertyRepairStates($query, self::SIMPLE_REPAIR_STATES);
        }

        if ($this->privateEntrance) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.entrance_type', 'private_entrance')
                    ->orWhere('search_property_access_details.has_private_entrance', true);
            });
        }

        if ($this->sharedEntrance) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.entrance_type', 'shared_entrance')
                    ->orWhere('search_property_access_details.has_shared_entrance', true);
            });
        }

        if ($this->withoutElevator) {
            $query->where('search_properties.has_elevator', false)
                ->whereDoesntHave('property.amenities', fn (Builder $amenity) => $amenity->where('slug', 'elevator'));
        }

        if ($this->firstFloor) {
            $query->where('search_properties.floor', 1);
        }

        if ($this->lastFloor) {
            $query->whereNotNull('search_properties.floor')
                ->where(function (Builder $builder): void {
                    $builder->whereColumn('search_properties.floor', 'search_properties.floors_count')
                        ->orWhereColumn('search_properties.floor', 'search_properties.total_floors');
                });
        }

        if ($this->notFirstFloor) {
            $query->where('search_properties.floor', '>', 1);
        }

        if ($this->notLastFloor) {
            $query->whereNotNull('search_properties.floor')
                ->where(function (Builder $builder): void {
                    $builder->whereNull('search_properties.floors_count')
                        ->whereNull('search_properties.total_floors')
                        ->orWhereColumn('search_properties.floor', '<', 'search_properties.floors_count')
                        ->orWhereColumn('search_properties.floor', '<', 'search_properties.total_floors');
                });
        }

        if ($this->withBalcony) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.balconies_count', '>', 0)
                    ->orWhere('search_rooms.has_balcony', true);
            });
        }

        if ($this->withoutBalcony) {
            $query->where('search_properties.balconies_count', '<=', 0)
                ->where('search_rooms.has_balcony', false);
        }

        if ($this->windowView) {
            $query->whereNotNull('search_rooms.window_view')
                ->whereNotIn('search_rooms.window_view', self::EMPTY_WINDOW_VIEW_VALUES);
        }

        if ($this->quietWindows) {
            $query->whereIn('search_rooms.noise_level', self::QUIET_WINDOW_NOISE_LEVELS);
        }

        if ($this->courtyardWindows) {
            $query->whereIn('search_rooms.window_view', self::COURTYARD_WINDOW_VALUES);
        }
    }

    private function applyRoomCriteriaFilters(Builder $query): void
    {
        if ($this->privateRoom) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.is_private', true)
                    ->orWhere('search_rooms.type', RoomType::Private->value)
                    ->orWhere('search_rooms.room_type', RoomType::Private->value);
            });
        }

        if ($this->sharedRoom) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.is_shared', true)
                    ->orWhereIn('search_rooms.type', [RoomType::Shared->value, RoomType::Dormitory->value])
                    ->orWhereIn('search_rooms.room_type', [RoomType::Shared->value, RoomType::Dormitory->value]);
            });
        }

        if ($this->maleRoom) {
            $this->whereRoomGender($query, GenderType::Male);
        }

        if ($this->femaleRoom) {
            $this->whereRoomGender($query, GenderType::Female);
        }

        if ($this->mixedRoom) {
            $this->whereRoomGender($query, GenderType::Mixed);
        }

        if ($this->studentRoom) {
            $this->whereRoomLivingFormats($query, ['student']);
        }

        if ($this->touristRoom) {
            $this->whereRoomLivingFormats($query, ['tourist']);
        }

        if ($this->workerRoom) {
            $this->whereRoomLivingFormats($query, self::WORKER_ROOM_FORMATS);
        }

        if ($this->longStayRoom) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.living_format', 'long_stay')
                    ->orWhere('search_rooms.is_for_long_stay', true);
            });
        }

        if ($this->oneGuestRoom) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.is_for_one_person', true)
                    ->orWhere('search_rooms.max_guests', '<=', 1)
                    ->orWhere('search_rooms.capacity', '<=', 1);
            });
        }

        if ($this->roomUpToTwoGuests) {
            $this->whereRoomCapacityAtMost($query, 2);
        }

        if ($this->roomUpToFourGuests) {
            $this->whereRoomCapacityAtMost($query, 4);
        }

        if ($this->roomUpToSixGuests) {
            $this->whereRoomCapacityAtMost($query, 6);
        }

        if ($this->roomMoreThanSixGuests) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.max_guests', '>', 6)
                    ->orWhere('search_rooms.capacity', '>', 6);
            });
        }

        if ($this->roomWithWindow) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.has_window', true)
                    ->orWhere('search_rooms.windows_count', '>', 0);
            });
        }

        if ($this->roomWithoutWindow) {
            $query->where('search_rooms.has_window', false)
                ->where('search_rooms.windows_count', '<=', 0);
        }

        if ($this->roomWithLock) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.has_lock', true)
                    ->orWhere('search_rooms.has_lockable_door', true)
                    ->orWhere('search_rooms.has_room_key', true);
            });
        }

        if ($this->roomWithoutLock) {
            $query->where('search_rooms.has_lock', false)
                ->where('search_rooms.has_lockable_door', false)
                ->where('search_rooms.has_room_key', false);
        }

        if ($this->roomAirConditioning) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.has_air_conditioning', true)
                    ->orWhere('search_rooms.has_ac', true);
            });
        }

        if ($this->roomHeating) {
            $query->where('search_rooms.has_heating', true);
        }

        if ($this->roomDesk) {
            $query->where('search_rooms.has_desk', true);
        }

        if ($this->roomWardrobe) {
            $query->where('search_rooms.has_wardrobe', true);
        }

        if ($this->roomLocker) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.has_lockers', true)
                    ->orWhere('sleeping_places.has_locker', true)
                    ->orWhereHas('room.amenities', fn (Builder $amenity) => $amenity->whereIn('slug', ['personal_locker', 'locker_with_lock']));
            });
        }

        if ($this->roomBalcony) {
            $query->where('search_rooms.has_balcony', true);
        }

        if ($this->quietRoom) {
            $query->whereIn('search_rooms.noise_level', self::QUIET_WINDOW_NOISE_LEVELS);
        }

        if ($this->brightRoom) {
            $query->whereIn('search_rooms.light_level', self::BRIGHT_ROOM_LIGHT_LEVELS);
        }

        if ($this->nonPassThroughRoom) {
            $query->where('search_rooms.is_pass_through', false);
        }

        if ($this->passThroughRoom) {
            $query->where('search_rooms.is_pass_through', true);
        }
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

    private function applyRuleCriteriaFilters(Builder $query): void
    {
        foreach (self::RULE_FILTER_SLUGS as $property => $slugs) {
            if ($this->{$property}) {
                $this->whereHasAnyRule($query, $slugs);
            }
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

    private function whereRoomGender(Builder $query, GenderType $gender): void
    {
        $query->where(function (Builder $builder) use ($gender): void {
            $builder->where('search_rooms.gender_policy', $gender->value)
                ->orWhere('search_rooms.gender_type', $gender->value);
        });
    }

    /**
     * @param  list<string>  $formats
     */
    private function whereRoomLivingFormats(Builder $query, array $formats): void
    {
        $query->whereIn('search_rooms.living_format', $formats);
    }

    private function whereRoomCapacityAtMost(Builder $query, int $maxGuests): void
    {
        $query->where(function (Builder $builder) use ($maxGuests): void {
            $builder->where('search_rooms.max_guests', '<=', $maxGuests)
                ->orWhere('search_rooms.capacity', '<=', $maxGuests);
        });
    }

    /**
     * @param  list<string>  $states
     */
    private function wherePropertyRepairStates(Builder $query, array $states): void
    {
        $query->where(function (Builder $builder) use ($states): void {
            $builder->whereIn('search_properties.repair_state', $states)
                ->orWhereIn('search_property_condition_details.repair_state', $states);
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
            'privateRoom',
            'sharedRoom',
            'maleRoom',
            'femaleRoom',
            'mixedRoom',
            'studentRoom',
            'touristRoom',
            'workerRoom',
            'longStayRoom',
            'oneGuestRoom',
            'roomUpToTwoGuests',
            'roomUpToFourGuests',
            'roomUpToSixGuests',
            'roomMoreThanSixGuests',
            'roomWithWindow',
            'roomWithoutWindow',
            'roomWithLock',
            'roomWithoutLock',
            'roomAirConditioning',
            'roomHeating',
            'roomDesk',
            'roomWardrobe',
            'roomLocker',
            'roomBalcony',
            'quietRoom',
            'brightRoom',
            'nonPassThroughRoom',
            'passThroughRoom',
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
            ...array_keys(self::RULE_FILTER_SLUGS),
            'noMixedRoom',
            'maxPeopleInRoom',
            'elevator',
            'withoutElevator',
            'newHome',
            'oldHome',
            'goodRepair',
            'simpleRepair',
            'privateEntrance',
            'sharedEntrance',
            'firstFloor',
            'lastFloor',
            'notFirstFloor',
            'notLastFloor',
            'withBalcony',
            'withoutBalcony',
            'windowView',
            'quietWindows',
            'courtyardWindows',
            'parking',
            'nearCenter',
            'nearMetro',
            'nearBusStop',
            'nearShop',
            'nearPharmacy',
            'nearHospital',
            'nearUniversity',
            'nearRailwayStation',
            'nearAirport',
            'easyTransport',
            'quietDistrict',
            'safeDistrict',
            'goodStreetLighting',
            'freeParking',
            'paidParking',
            'cleanProperty',
            'noInsects',
            'noMold',
            'normalHumidity',
            'comfortableWinter',
            'comfortableSummer',
            'quietProperty',
            'brightProperty',
            'doorCodeAccess',
            'electronicLockAccess',
            'keySafeAccess',
            'access247',
            'noNightEntryRestrictions',
            'guestRules',
            'courierRules',
            'deliveryAvailable',
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
