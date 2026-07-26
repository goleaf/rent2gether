<?php

namespace App\Livewire\Search;

use App\Data\Listings\ListingCardContext;
use App\Enums\GenderType;
use App\Enums\PropertyType;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Models\City;
use App\Models\CompatibilityResult;
use App\Models\Complaint;
use App\Models\ComplaintCase;
use App\Models\RoomOccupantSnapshot;
use App\Models\SleepingPlace;
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

    private const FIT_STATUS_RANKS = [
        'not_suitable' => 0,
        'uncomfortable' => 1,
        'attention' => 2,
        'good' => 3,
        'great' => 4,
    ];

    private const GOOD_STREET_LIGHTING_LEVELS = ['good', 'high', 'bright'];

    private const FLEXIBLE_DATE_DEFAULT_WINDOW_DAYS = 3;

    private const SHORT_STAY_MAX_NIGHTS = 7;

    private const LONG_STAY_MIN_NIGHTS = 30;

    private const LOW_DEPOSIT_MAX_AMOUNT = 50.0;

    private const PRICE_BASIS_NIGHTLY = 'nightly';

    private const PRICE_BASIS_WEEKLY = 'weekly';

    private const PRICE_BASIS_MONTHLY = 'monthly';

    private const FULL_REFUND_POLICIES = ['flexible'];

    private const PARTIAL_REFUND_POLICIES = ['flexible', 'moderate', 'strict'];

    private const NIGHT_CHECK_IN_TIME = '22:00';

    private const NIGHT_CHECK_OUT_TIME = '22:00';

    private const EARLY_MORNING_CHECK_IN_TIME = '08:00';

    private const LATE_EVENING_CHECK_OUT_TIME = '20:00';

    private const CLEAN_PROPERTY_LEVELS = ['good', 'high', 'clean'];

    private const NORMAL_HUMIDITY_LEVELS = ['normal', 'comfortable', 'dry'];

    private const COMFORTABLE_WINTER_LEVELS = ['normal', 'warm', 'comfortable'];

    private const COMFORTABLE_SUMMER_LEVELS = ['normal', 'cool', 'comfortable'];

    private const CURRENT_NEIGHBOR_STATUSES = [
        RoomOccupantSnapshot::STATUS_CURRENT,
        RoomOccupantSnapshot::STATUS_CHECKED_IN,
        RoomOccupantSnapshot::STATUS_IN_PROGRESS,
        RoomOccupantSnapshot::STATUS_LEAVING_SOON,
    ];

    private const EARLY_WAKE_VALUES = ['early', 'early_bird', 'wakes_up_early'];

    private const LATE_SLEEP_VALUES = ['late', 'night_owl', 'sleeps_late'];

    private const NIGHT_WORK_VALUES = ['works_at_night', 'night_work', 'night_owl'];

    private const OFTEN_HOME_VALUES = ['often_home', 'mostly_home', 'homebody'];

    private const RARELY_HOME_VALUES = ['rarely_home', 'mostly_out', 'often_away'];

    private const SOCIAL_NEIGHBOR_VALUES = ['social', 'friendly', 'outgoing', 'active'];

    private const QUIET_NEIGHBOR_VALUES = ['quiet', 'calm'];

    private const NEIGHBOR_AGE_RANGES = ['18-24', '25-34', '35-44', '45-54', '55+'];

    private const NEIGHBOR_LIFESTYLES = ['quiet', 'social', 'work_study', 'tourist', 'long_stay', 'often_home', 'rarely_home'];

    private const NEIGHBOR_LANGUAGES = ['en', 'ru', 'de', 'fr', 'es', 'pl', 'lt'];

    private const NEIGHBOR_BOOLEAN_FILTERS = [
        'neighborStudents' => ['label' => 'neighbors_students', 'icon' => 'academic-cap'],
        'neighborWorkers' => ['label' => 'neighbors_workers', 'icon' => 'briefcase'],
        'neighborTourists' => ['label' => 'neighbors_tourists', 'icon' => 'map'],
        'neighborLongTerm' => ['label' => 'neighbors_long_term', 'icon' => 'calendar-days'],
        'quietNeighbors' => ['label' => 'quiet_neighbors', 'icon' => 'speaker-x-mark'],
        'socialNeighbors' => ['label' => 'social_neighbors', 'icon' => 'users'],
        'neighborsOftenHome' => ['label' => 'neighbors_often_home', 'icon' => 'home-modern'],
        'neighborsRarelyHome' => ['label' => 'neighbors_rarely_home', 'icon' => 'arrows-right-left'],
        'neighborsWorkAtNight' => ['label' => 'neighbors_work_at_night', 'icon' => 'moon'],
        'neighborsWakeEarly' => ['label' => 'neighbors_wake_early', 'icon' => 'sun'],
        'neighborsSleepLate' => ['label' => 'neighbors_sleep_late', 'icon' => 'moon'],
        'neighborsSmoke' => ['label' => 'neighbors_smoke', 'icon' => 'sparkles'],
        'neighborsDoNotSmoke' => ['label' => 'neighbors_do_not_smoke', 'icon' => 'scale'],
        'neighborsWithPets' => ['label' => 'neighbors_with_pets', 'icon' => 'sparkles'],
        'neighborsWithoutPets' => ['label' => 'neighbors_without_pets', 'icon' => 'scale'],
        'maleNeighborsPresent' => ['label' => 'male_neighbors_present', 'icon' => 'user'],
        'femaleNeighborsPresent' => ['label' => 'female_neighbors_present', 'icon' => 'user'],
        'mixedNeighborGenders' => ['label' => 'mixed_neighbor_genders', 'icon' => 'users'],
    ];

    private const SAFETY_RATING_THRESHOLD = 4.5;

    private const URGENT_SUPPORT_RESPONSE_MINUTES = 30;

    private const VERIFIED_PROPERTY_REVIEW_STATUSES = ['auto_approved', 'approved', 'verified'];

    private const ACTIVE_COMPLAINT_STATUSES = ['created', 'submitted', 'open', 'investigating', 'under_review'];

    private const ACTIVE_COMPLAINT_CASE_STATUSES = [
        'created',
        'submitted',
        'open',
        'waiting_for_other_side',
        'needs_more_info',
        'under_review',
        'under_review_by_system',
        'awaiting_response',
        'investigating',
    ];

    private const SERIOUS_COMPLAINT_SEVERITIES = ['high', 'urgent', 'critical', 'emergency'];

    private const SERIOUS_COMPLAINT_TYPES = [
        'unsafe_situation',
        'unsafe',
        'safety',
        'theft',
        'fraud',
        'scam',
        'deception',
        'aggression',
        'aggressive_behavior',
        'violence',
        'dirty_room',
        'dirty',
    ];

    private const THEFT_COMPLAINT_TYPES = ['theft'];

    private const AGGRESSION_COMPLAINT_TYPES = ['aggression', 'aggressive_behavior', 'violence', 'unsafe_situation', 'unsafe'];

    private const DIRT_COMPLAINT_TYPES = ['dirty_room', 'dirty', 'guest_left_mess'];

    private const FRAUD_COMPLAINT_TYPES = ['fraud', 'scam', 'deception'];

    private const SAFETY_BOOLEAN_FILTERS = [
        'safetyVerifiedHost' => ['label' => 'safety_verified_host', 'icon' => 'shield-check'],
        'safetyVerifiedProperty' => ['label' => 'safety_verified_property', 'icon' => 'shield-check'],
        'safetyVerifiedGuests' => ['label' => 'safety_verified_guests', 'icon' => 'shield-check'],
        'safetyRoomLock' => ['label' => 'safety_room_lock', 'icon' => 'lock-closed'],
        'safetyEntranceLock' => ['label' => 'safety_entrance_lock', 'icon' => 'key'],
        'safetyPersonalLocker' => ['label' => 'safety_personal_locker', 'icon' => 'archive-box'],
        'safetySafe' => ['label' => 'safety_safe', 'icon' => 'lock-closed'],
        'safetySecurityGuard' => ['label' => 'safety_security_guard', 'icon' => 'shield-check'],
        'safetyIntercom' => ['label' => 'safety_intercom', 'icon' => 'key'],
        'safetyCctvCommonAreas' => ['label' => 'safety_cctv_common_areas', 'icon' => 'video-camera'],
        'safetyNoPrivateCameras' => ['label' => 'safety_no_private_cameras', 'icon' => 'shield-check'],
        'safetyFirstAidKit' => ['label' => 'safety_first_aid_kit', 'icon' => 'plus-circle'],
        'safetyFireExtinguisher' => ['label' => 'safety_fire_extinguisher', 'icon' => 'fire'],
        'safetySmokeDetector' => ['label' => 'safety_smoke_detector', 'icon' => 'shield-check'],
        'safetyGasDetector' => ['label' => 'safety_gas_detector', 'icon' => 'shield-check'],
        'safetyFireInstructions' => ['label' => 'safety_fire_instructions', 'icon' => 'document-check'],
        'safetyEmergencyExit' => ['label' => 'safety_emergency_exit', 'icon' => 'arrow-right'],
        'safetyExactAddressAfterBooking' => ['label' => 'safety_exact_address_after_booking', 'icon' => 'map-pin'],
        'safetyEmergencyContact' => ['label' => 'safety_emergency_contact', 'icon' => 'phone'],
        'safetyUrgentSupport' => ['label' => 'safety_urgent_support', 'icon' => 'clock'],
        'safetyGoodRating' => ['label' => 'safety_good_rating', 'icon' => 'star'],
        'safetyNoSeriousComplaints' => ['label' => 'safety_no_serious_complaints', 'icon' => 'shield-check'],
        'safetyNoTheftComplaints' => ['label' => 'safety_no_theft_complaints', 'icon' => 'shield-check'],
        'safetyNoAggressionComplaints' => ['label' => 'safety_no_aggression_complaints', 'icon' => 'shield-check'],
        'safetyNoDirtComplaints' => ['label' => 'safety_no_dirt_complaints', 'icon' => 'shield-check'],
        'safetyNoFraudComplaints' => ['label' => 'safety_no_fraud_complaints', 'icon' => 'shield-check'],
    ];

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

    #[Url(as: 'country_id', except: '')]
    public string $countryId = '';

    #[Url(as: 'city_id', except: '')]
    public string $cityId = '';

    #[Url(as: 'city_name', except: '')]
    public string $cityQuery = '';

    #[Url(as: 'district', except: '')]
    public string $district = '';

    #[Url(as: 'street', except: '')]
    public string $street = '';

    #[Url(as: 'landmark', except: '')]
    public string $landmark = '';

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

    #[Url(as: 'price_basis', except: '')]
    public string $priceBasis = '';

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

    #[Url(as: 'roommates_max', except: '')]
    public string $neighborRoommatesMax = '';

    #[Url(as: 'residents_max', except: '')]
    public string $propertyResidentsMax = '';

    #[Url(as: 'neighbor_age', except: '')]
    public string $neighborAgeRange = '';

    #[Url(as: 'neighbor_lifestyle', except: '')]
    public string $neighborLifestyle = '';

    #[Url(as: 'neighbor_language', except: '')]
    public string $neighborLanguage = '';

    #[Url(as: 'neighbor_rating', except: '')]
    public string $neighborMinRating = '';

    #[Url(as: 'n_students', except: false)]
    public bool $neighborStudents = false;

    #[Url(as: 'n_workers', except: false)]
    public bool $neighborWorkers = false;

    #[Url(as: 'n_tourists', except: false)]
    public bool $neighborTourists = false;

    #[Url(as: 'n_long_stay', except: false)]
    public bool $neighborLongTerm = false;

    #[Url(as: 'n_quiet', except: false)]
    public bool $quietNeighbors = false;

    #[Url(as: 'n_social', except: false)]
    public bool $socialNeighbors = false;

    #[Url(as: 'n_often_home', except: false)]
    public bool $neighborsOftenHome = false;

    #[Url(as: 'n_rarely_home', except: false)]
    public bool $neighborsRarelyHome = false;

    #[Url(as: 'n_work_night', except: false)]
    public bool $neighborsWorkAtNight = false;

    #[Url(as: 'n_early', except: false)]
    public bool $neighborsWakeEarly = false;

    #[Url(as: 'n_late_sleep', except: false)]
    public bool $neighborsSleepLate = false;

    #[Url(as: 'n_smoke', except: false)]
    public bool $neighborsSmoke = false;

    #[Url(as: 'n_no_smoke', except: false)]
    public bool $neighborsDoNotSmoke = false;

    #[Url(as: 'n_pets', except: false)]
    public bool $neighborsWithPets = false;

    #[Url(as: 'n_no_pets', except: false)]
    public bool $neighborsWithoutPets = false;

    #[Url(as: 'n_male', except: false)]
    public bool $maleNeighborsPresent = false;

    #[Url(as: 'n_female', except: false)]
    public bool $femaleNeighborsPresent = false;

    #[Url(as: 'n_mixed', except: false)]
    public bool $mixedNeighborGenders = false;

    #[Url(as: 's_v_host', except: false)]
    public bool $safetyVerifiedHost = false;

    #[Url(as: 's_v_property', except: false)]
    public bool $safetyVerifiedProperty = false;

    #[Url(as: 's_v_guests', except: false)]
    public bool $safetyVerifiedGuests = false;

    #[Url(as: 's_room_lock', except: false)]
    public bool $safetyRoomLock = false;

    #[Url(as: 's_entry_lock', except: false)]
    public bool $safetyEntranceLock = false;

    #[Url(as: 's_locker', except: false)]
    public bool $safetyPersonalLocker = false;

    #[Url(as: 's_safe', except: false)]
    public bool $safetySafe = false;

    #[Url(as: 's_guard', except: false)]
    public bool $safetySecurityGuard = false;

    #[Url(as: 's_intercom', except: false)]
    public bool $safetyIntercom = false;

    #[Url(as: 's_cctv', except: false)]
    public bool $safetyCctvCommonAreas = false;

    #[Url(as: 's_no_private_cams', except: false)]
    public bool $safetyNoPrivateCameras = false;

    #[Url(as: 's_first_aid', except: false)]
    public bool $safetyFirstAidKit = false;

    #[Url(as: 's_fire_ext', except: false)]
    public bool $safetyFireExtinguisher = false;

    #[Url(as: 's_smoke_det', except: false)]
    public bool $safetySmokeDetector = false;

    #[Url(as: 's_gas_det', except: false)]
    public bool $safetyGasDetector = false;

    #[Url(as: 's_fire_instr', except: false)]
    public bool $safetyFireInstructions = false;

    #[Url(as: 's_exit', except: false)]
    public bool $safetyEmergencyExit = false;

    #[Url(as: 's_address_after', except: false)]
    public bool $safetyExactAddressAfterBooking = false;

    #[Url(as: 's_emergency_contact', except: false)]
    public bool $safetyEmergencyContact = false;

    #[Url(as: 's_urgent_help', except: false)]
    public bool $safetyUrgentSupport = false;

    #[Url(as: 's_good_rating', except: false)]
    public bool $safetyGoodRating = false;

    #[Url(as: 's_no_serious', except: false)]
    public bool $safetyNoSeriousComplaints = false;

    #[Url(as: 's_no_theft', except: false)]
    public bool $safetyNoTheftComplaints = false;

    #[Url(as: 's_no_aggression', except: false)]
    public bool $safetyNoAggressionComplaints = false;

    #[Url(as: 's_no_dirt', except: false)]
    public bool $safetyNoDirtComplaints = false;

    #[Url(as: 's_no_fraud', except: false)]
    public bool $safetyNoFraudComplaints = false;

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

    #[Url(as: 'near_bus_stop', except: false)]
    public bool $nearBusStop = false;

    #[Url(as: 'near_shop', except: false)]
    public bool $nearShop = false;

    #[Url(as: 'near_pharmacy', except: false)]
    public bool $nearPharmacy = false;

    #[Url(as: 'near_hospital', except: false)]
    public bool $nearHospital = false;

    #[Url(as: 'near_university', except: false)]
    public bool $nearUniversity = false;

    #[Url(as: 'near_train_station', except: false)]
    public bool $nearRailwayStation = false;

    #[Url(as: 'near_park', except: false)]
    public bool $nearPark = false;

    #[Url(as: 'near_shopping_center', except: false)]
    public bool $nearShoppingCenter = false;

    #[Url(as: 'near_gym', except: false)]
    public bool $nearGym = false;

    #[Url(as: 'near_coworking', except: false)]
    public bool $nearCoworking = false;

    #[Url(as: 'near_work', except: false)]
    public bool $nearWork = false;

    #[Url(as: 'near_sea', except: false)]
    public bool $nearSea = false;

    #[Url(as: 'near_nightlife', except: false)]
    public bool $nearNightlife = false;

    #[Url(as: 'near_airport', except: false)]
    public bool $nearAirport = false;

    #[Url(as: 'transport', except: false)]
    public bool $easyTransport = false;

    #[Url(as: 'area_quiet', except: false)]
    public bool $quietDistrict = false;

    #[Url(as: 'area_safe', except: false)]
    public bool $safeDistrict = false;

    #[Url(as: 'area_residential', except: false)]
    public bool $areaResidential = false;

    #[Url(as: 'area_city_center', except: false)]
    public bool $areaCityCenter = false;

    #[Url(as: 'area_suburb', except: false)]
    public bool $areaSuburb = false;

    #[Url(as: 'area_industrial', except: false)]
    public bool $areaIndustrial = false;

    #[Url(as: 'area_tourist', except: false)]
    public bool $areaTourist = false;

    #[Url(as: 'area_students', except: false)]
    public bool $areaStudents = false;

    #[Url(as: 'area_workers', except: false)]
    public bool $areaWorkers = false;

    #[Url(as: 'area_long_stay', except: false)]
    public bool $areaLongStay = false;

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

    #[Url(as: 'with_deposit', except: false)]
    public bool $withDeposit = false;

    #[Url(as: 'low_deposit', except: false)]
    public bool $lowDeposit = false;

    #[Url(as: 'no_cleaning_fee', except: false)]
    public bool $noCleaningFee = false;

    #[Url(as: 'free_cancel', except: false)]
    public bool $freeCancellation = false;

    #[Url(as: 'partial_refund', except: false)]
    public bool $partialRefund = false;

    #[Url(as: 'full_refund', except: false)]
    public bool $fullRefund = false;

    #[Url(as: 'installments', except: false)]
    public bool $installmentPayment = false;

    #[Url(as: 'pay_later', except: false)]
    public bool $payLater = false;

    #[Url(as: 'pay_on_arrival', except: false)]
    public bool $payOnArrival = false;

    #[Url(as: 'has_discount', except: false)]
    public bool $hasDiscount = false;

    #[Url(as: 'has_promo', except: false)]
    public bool $hasPromoCode = false;

    #[Url(as: 'below_avg_price', except: false)]
    public bool $belowAveragePrice = false;

    #[Url(as: 'all_fees', except: false)]
    public bool $priceIncludesAllFees = false;

    #[Url(as: 'show_total_now', except: false)]
    public bool $showTotalPriceImmediately = false;

    #[Url(as: 'no_hidden_fees', except: false)]
    public bool $hideHiddenFees = false;

    #[Url(as: 'long_stay', except: false)]
    public bool $longStayAllowed = false;

    #[Url(as: 'today', except: false)]
    public bool $availableToday = false;

    #[Url(as: 'tomorrow', except: false)]
    public bool $availableTomorrow = false;

    #[Url(as: 'weekend', except: false)]
    public bool $availableWeekend = false;

    #[Url(as: 'flexible', except: false)]
    public bool $flexibleDates = false;

    #[Url(as: 'flex_1', except: false)]
    public bool $flexiblePlusMinusOneDay = false;

    #[Url(as: 'flex_3', except: false)]
    public bool $flexiblePlusMinusThreeDays = false;

    #[Url(as: 'flex_7', except: false)]
    public bool $flexiblePlusMinusSevenDays = false;

    #[Url(as: 'short_stay', except: false)]
    public bool $shortStayAllowed = false;

    #[Url(as: 'min_nights', except: '')]
    public string $minimumStayNights = '';

    #[Url(as: 'max_nights', except: '')]
    public string $maximumStayNights = '';

    #[Url(as: 'can_extend', except: false)]
    public bool $canExtendStay = false;

    #[Url(as: 'no_extend', except: false)]
    public bool $cannotExtendStay = false;

    #[Url(as: 'free_after_checkout', except: false)]
    public bool $availableAfterCheckout = false;

    #[Url(as: 'night_checkin', except: false)]
    public bool $nightCheckIn = false;

    #[Url(as: 'night_checkout', except: false)]
    public bool $nightCheckOut = false;

    #[Url(as: 'early_morning_checkin', except: false)]
    public bool $earlyMorningCheckIn = false;

    #[Url(as: 'late_evening_checkout', except: false)]
    public bool $lateEveningCheckOut = false;

    #[Url(as: 'fit', except: '')]
    public string $minimumCompatibilityFit = '';

    #[Url(as: 'hide_bad_fit', except: false)]
    public bool $hideNotSuitableCompatibility = false;

    #[Url(as: 'compat_warnings', except: true)]
    public bool $showCompatibilityWarnings = true;

    #[Url(as: 'sort', except: 'recommended')]
    public string $sort = 'recommended';

    public bool $filtersOpen = false;

    public bool $cityOpen = false;

    public int $visibleCount = self::INITIAL_VISIBLE_COUNT;

    public function mount(): void
    {
        $this->normalizeLocationQueryState();

        if ($this->cityQuery === '' && $this->city !== '') {
            $this->cityQuery = $this->selectedCity()?->name ?: $this->city;
        }
    }

    public function updated(string $property): void
    {
        if ($property === 'cityQuery') {
            $this->city = $this->cityQuery;
            $this->cityId = '';
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
        $this->cityId = (string) $city->id;
        $this->cityQuery = $city->name;
        $this->cityOpen = false;
        $this->visibleCount = self::INITIAL_VISIBLE_COUNT;
    }

    public function clearCity(): void
    {
        $this->city = '';
        $this->cityId = '';
        $this->cityQuery = '';
        $this->cityOpen = false;
        $this->visibleCount = self::INITIAL_VISIBLE_COUNT;
    }

    public function clearFilters(): void
    {
        $this->reset([
            'city',
            'countryId',
            'cityId',
            'cityQuery',
            'district',
            'street',
            'landmark',
            'checkIn',
            'checkOut',
            'guestsCount',
            'priceMin',
            'priceMax',
            'currency',
            'priceBasis',
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
            'neighborRoommatesMax',
            'propertyResidentsMax',
            'neighborAgeRange',
            'neighborLifestyle',
            'neighborLanguage',
            'neighborMinRating',
            ...array_keys(self::NEIGHBOR_BOOLEAN_FILTERS),
            ...array_keys(self::SAFETY_BOOLEAN_FILTERS),
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
            'nearPark',
            'nearShoppingCenter',
            'nearGym',
            'nearCoworking',
            'nearWork',
            'nearSea',
            'nearNightlife',
            'nearAirport',
            'easyTransport',
            'quietDistrict',
            'safeDistrict',
            'areaResidential',
            'areaCityCenter',
            'areaSuburb',
            'areaIndustrial',
            'areaTourist',
            'areaStudents',
            'areaWorkers',
            'areaLongStay',
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
            'withDeposit',
            'lowDeposit',
            'noCleaningFee',
            'freeCancellation',
            'partialRefund',
            'fullRefund',
            'installmentPayment',
            'payLater',
            'payOnArrival',
            'hasDiscount',
            'hasPromoCode',
            'belowAveragePrice',
            'priceIncludesAllFees',
            'showTotalPriceImmediately',
            'hideHiddenFees',
            'longStayAllowed',
            'availableToday',
            'availableTomorrow',
            'availableWeekend',
            'flexibleDates',
            'flexiblePlusMinusOneDay',
            'flexiblePlusMinusThreeDays',
            'flexiblePlusMinusSevenDays',
            'shortStayAllowed',
            'minimumStayNights',
            'maximumStayNights',
            'canExtendStay',
            'cannotExtendStay',
            'availableAfterCheckout',
            'nightCheckIn',
            'nightCheckOut',
            'earlyMorningCheckIn',
            'lateEveningCheckOut',
            'minimumCompatibilityFit',
            'hideNotSuitableCompatibility',
            'showCompatibilityWarnings',
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

    #[Computed]
    public function propertyTypeOptions(): array
    {
        return collect(PropertyType::cases())
            ->mapWithKeys(fn (PropertyType $type): array => [$type->value => $type->label()])
            ->all();
    }

    #[Computed]
    public function roomTypeOptions(): array
    {
        return collect(RoomType::cases())
            ->mapWithKeys(fn (RoomType $type): array => [$type->value => $type->label()])
            ->all();
    }

    #[Computed]
    public function sleepingPlaceTypeOptions(): array
    {
        return collect(SleepingPlaceType::cases())
            ->mapWithKeys(fn (SleepingPlaceType $type): array => [$type->value => $type->label()])
            ->all();
    }

    #[Computed]
    public function genderOptions(): array
    {
        return collect(GenderType::cases())
            ->mapWithKeys(fn (GenderType $gender): array => [$gender->value => $gender->label()])
            ->all();
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    #[Computed]
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
    #[Computed]
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
            ['property' => 'nearPark', 'label' => __('search.filters_flags.near_park'), 'icon' => 'map-pin'],
            ['property' => 'nearShoppingCenter', 'label' => __('search.filters_flags.near_shopping_center'), 'icon' => 'shopping-bag'],
            ['property' => 'nearGym', 'label' => __('search.filters_flags.near_gym'), 'icon' => 'sparkles'],
            ['property' => 'nearCoworking', 'label' => __('search.filters_flags.near_coworking'), 'icon' => 'briefcase'],
            ['property' => 'nearWork', 'label' => __('search.filters_flags.near_work'), 'icon' => 'briefcase'],
            ['property' => 'nearSea', 'label' => __('search.filters_flags.near_sea'), 'icon' => 'map'],
            ['property' => 'nearNightlife', 'label' => __('search.filters_flags.near_nightlife'), 'icon' => 'moon'],
            ['property' => 'nearAirport', 'label' => __('search.filters_flags.near_airport'), 'icon' => 'paper-airplane'],
            ['property' => 'easyTransport', 'label' => __('search.filters_flags.easy_transport'), 'icon' => 'arrows-right-left'],
            ['property' => 'quietDistrict', 'label' => __('search.filters_flags.quiet_district'), 'icon' => 'speaker-x-mark'],
            ['property' => 'safeDistrict', 'label' => __('search.filters_flags.safe_district'), 'icon' => 'shield-check'],
            ['property' => 'areaResidential', 'label' => __('search.filters_flags.area_residential'), 'icon' => 'home-modern'],
            ['property' => 'areaCityCenter', 'label' => __('search.filters_flags.area_city_center'), 'icon' => 'map-pin'],
            ['property' => 'areaSuburb', 'label' => __('search.filters_flags.area_suburb'), 'icon' => 'map'],
            ['property' => 'areaIndustrial', 'label' => __('search.filters_flags.area_industrial'), 'icon' => 'building-office'],
            ['property' => 'areaTourist', 'label' => __('search.filters_flags.area_tourist'), 'icon' => 'map'],
            ['property' => 'areaStudents', 'label' => __('search.filters_flags.area_students'), 'icon' => 'academic-cap'],
            ['property' => 'areaWorkers', 'label' => __('search.filters_flags.area_workers'), 'icon' => 'briefcase'],
            ['property' => 'areaLongStay', 'label' => __('search.filters_flags.area_long_stay'), 'icon' => 'calendar-days'],
            ['property' => 'goodStreetLighting', 'label' => __('search.filters_flags.good_street_lighting'), 'icon' => 'sun'],
            ['property' => 'freeParking', 'label' => __('search.filters_flags.free_parking'), 'icon' => 'truck'],
            ['property' => 'paidParking', 'label' => __('search.filters_flags.paid_parking'), 'icon' => 'truck'],
        ];
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    #[Computed]
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
    #[Computed]
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
    #[Computed]
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
    #[Computed]
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

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    #[Computed]
    public function neighborFilterOptions(): array
    {
        return collect(self::NEIGHBOR_BOOLEAN_FILTERS)
            ->map(fn (array $config, string $property): array => [
                'property' => $property,
                'label' => __('search.filters_flags.'.$config['label']),
                'icon' => $config['icon'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{property:string,label:string,icon:string}>
     */
    #[Computed]
    public function safetyFilterOptions(): array
    {
        return collect(self::SAFETY_BOOLEAN_FILTERS)
            ->map(fn (array $config, string $property): array => [
                'property' => $property,
                'label' => __('search.filters_flags.'.$config['label']),
                'icon' => $config['icon'],
            ])
            ->values()
            ->all();
    }

    #[Computed]
    public function neighborAgeRangeOptions(): array
    {
        return [
            '18-24' => __('search.neighbor_options.age_ranges.18_24'),
            '25-34' => __('search.neighbor_options.age_ranges.25_34'),
            '35-44' => __('search.neighbor_options.age_ranges.35_44'),
            '45-54' => __('search.neighbor_options.age_ranges.45_54'),
            '55+' => __('search.neighbor_options.age_ranges.55_plus'),
        ];
    }

    #[Computed]
    public function neighborLifestyleOptions(): array
    {
        return [
            'quiet' => __('search.neighbor_options.lifestyles.quiet'),
            'social' => __('search.neighbor_options.lifestyles.social'),
            'work_study' => __('search.neighbor_options.lifestyles.work_study'),
            'tourist' => __('search.neighbor_options.lifestyles.tourist'),
            'long_stay' => __('search.neighbor_options.lifestyles.long_stay'),
            'often_home' => __('search.neighbor_options.lifestyles.often_home'),
            'rarely_home' => __('search.neighbor_options.lifestyles.rarely_home'),
        ];
    }

    #[Computed]
    public function neighborLanguageOptions(): array
    {
        return [
            'en' => __('search.neighbor_options.languages.en'),
            'ru' => __('search.neighbor_options.languages.ru'),
            'de' => __('search.neighbor_options.languages.de'),
            'fr' => __('search.neighbor_options.languages.fr'),
            'es' => __('search.neighbor_options.languages.es'),
            'pl' => __('search.neighbor_options.languages.pl'),
            'lt' => __('search.neighbor_options.languages.lt'),
        ];
    }

    #[Computed]
    public function sortOptions(): array
    {
        return [
            'recommended' => __('search.sort_options.recommended'),
            'cheapest' => __('search.sort_options.cheapest'),
            'best_value' => __('search.sort_options.best_value'),
            'highest_rating' => __('search.sort_options.highest_rating'),
            'closest_to_center' => __('search.sort_options.closest_to_center'),
            'fewer_people' => __('search.sort_options.fewer_people'),
            'newest' => __('search.sort_options.newest'),
        ];
    }

    #[Computed]
    public function priceBasisOptions(): array
    {
        return [
            '' => __('search.options.any'),
            self::PRICE_BASIS_NIGHTLY => __('search.price_basis.nightly'),
            self::PRICE_BASIS_WEEKLY => __('search.price_basis.weekly'),
            self::PRICE_BASIS_MONTHLY => __('search.price_basis.monthly'),
        ];
    }

    #[Computed]
    public function compatibilityFitOptions(): array
    {
        return [
            '' => __('compatibility.filter.any_fit'),
            'great' => __('compatibility.fit_statuses.great'),
            'good' => __('compatibility.fit_statuses.good'),
            'attention' => __('compatibility.fit_statuses.attention'),
            'uncomfortable' => __('compatibility.fit_statuses.uncomfortable'),
        ];
    }

    #[Computed]
    public function minimumStayOptions(): array
    {
        return [
            '' => __('search.options.any'),
            '1' => __('search.stay_length.minimum_1'),
            '3' => __('search.stay_length.minimum_3'),
            '7' => __('search.stay_length.minimum_7'),
            '30' => __('search.stay_length.minimum_30'),
        ];
    }

    #[Computed]
    public function maximumStayOptions(): array
    {
        return [
            '' => __('search.options.any'),
            '7' => __('search.stay_length.maximum_7'),
            '30' => __('search.stay_length.maximum_30'),
            '90' => __('search.stay_length.maximum_90'),
        ];
    }

    #[Computed]
    public function dateFilterOptions(): array
    {
        return [
            ['property' => 'availableToday', 'label' => __('search.filters_flags.available_today'), 'icon' => 'calendar-days'],
            ['property' => 'availableTomorrow', 'label' => __('search.filters_flags.available_tomorrow'), 'icon' => 'calendar-days'],
            ['property' => 'availableWeekend', 'label' => __('search.filters_flags.available_weekend'), 'icon' => 'calendar-days'],
            ['property' => 'flexibleDates', 'label' => __('search.filters_flags.flexible_dates'), 'icon' => 'arrows-right-left'],
            ['property' => 'flexiblePlusMinusOneDay', 'label' => __('search.filters_flags.flexible_plus_minus_1'), 'icon' => 'arrows-right-left'],
            ['property' => 'flexiblePlusMinusThreeDays', 'label' => __('search.filters_flags.flexible_plus_minus_3'), 'icon' => 'arrows-right-left'],
            ['property' => 'flexiblePlusMinusSevenDays', 'label' => __('search.filters_flags.flexible_plus_minus_7'), 'icon' => 'arrows-right-left'],
            ['property' => 'shortStayAllowed', 'label' => __('search.filters_flags.short_stay_allowed'), 'icon' => 'clock'],
            ['property' => 'longStayAllowed', 'label' => __('search.filters_flags.long_stay_allowed'), 'icon' => 'calendar-days'],
            ['property' => 'canExtendStay', 'label' => __('search.filters_flags.can_extend_stay'), 'icon' => 'arrow-path'],
            ['property' => 'cannotExtendStay', 'label' => __('search.filters_flags.cannot_extend_stay'), 'icon' => 'x-circle'],
            ['property' => 'availableAfterCheckout', 'label' => __('search.filters_flags.available_after_checkout'), 'icon' => 'calendar-days'],
            ['property' => 'nightCheckIn', 'label' => __('search.filters_flags.night_check_in'), 'icon' => 'moon'],
            ['property' => 'nightCheckOut', 'label' => __('search.filters_flags.night_check_out'), 'icon' => 'moon'],
            ['property' => 'earlyMorningCheckIn', 'label' => __('search.filters_flags.early_morning_check_in'), 'icon' => 'sun'],
            ['property' => 'lateEveningCheckOut', 'label' => __('search.filters_flags.late_evening_check_out'), 'icon' => 'clock'],
        ];
    }

    #[Computed]
    public function priceFilterOptions(): array
    {
        return [
            ['property' => 'noDeposit', 'label' => __('search.filters_flags.no_deposit'), 'icon' => 'banknotes'],
            ['property' => 'withDeposit', 'label' => __('search.filters_flags.with_deposit'), 'icon' => 'banknotes'],
            ['property' => 'lowDeposit', 'label' => __('search.filters_flags.low_deposit'), 'icon' => 'banknotes'],
            ['property' => 'noCleaningFee', 'label' => __('search.filters_flags.no_cleaning_fee'), 'icon' => 'sparkles'],
            ['property' => 'freeCancellation', 'label' => __('search.filters_flags.free_cancellation'), 'icon' => 'scale'],
            ['property' => 'partialRefund', 'label' => __('search.filters_flags.partial_refund'), 'icon' => 'receipt-refund'],
            ['property' => 'fullRefund', 'label' => __('search.filters_flags.full_refund'), 'icon' => 'receipt-refund'],
            ['property' => 'installmentPayment', 'label' => __('search.filters_flags.installment_payment'), 'icon' => 'credit-card'],
            ['property' => 'payLater', 'label' => __('search.filters_flags.pay_later'), 'icon' => 'clock'],
            ['property' => 'payOnArrival', 'label' => __('search.filters_flags.pay_on_arrival'), 'icon' => 'key'],
            ['property' => 'hasDiscount', 'label' => __('search.filters_flags.has_discount'), 'icon' => 'tag'],
            ['property' => 'hasPromoCode', 'label' => __('search.filters_flags.has_promo_code'), 'icon' => 'ticket'],
            ['property' => 'belowAveragePrice', 'label' => __('search.filters_flags.below_average_price'), 'icon' => 'arrow-trending-down'],
            ['property' => 'priceIncludesAllFees', 'label' => __('search.filters_flags.price_includes_all_fees'), 'icon' => 'document-check'],
            ['property' => 'showTotalPriceImmediately', 'label' => __('search.filters_flags.show_total_price_immediately'), 'icon' => 'calculator'],
            ['property' => 'hideHiddenFees', 'label' => __('search.filters_flags.hide_hidden_fees'), 'icon' => 'eye-slash'],
        ];
    }

    public function activeFilterCount(): int
    {
        return collect($this->filterPropertyNames())
            ->reject(fn (string $property): bool => in_array($property, ['cityQuery', 'checkIn', 'checkOut', 'sort'], true))
            ->filter(function (string $property): bool {
                $value = $this->{$property};

                if ($property === 'city' && $this->cityId !== '') {
                    return false;
                }

                if ($property === 'showCompatibilityWarnings') {
                    return $value === false;
                }

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
        return $this->selectedCityId();
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
        $this->applyConditionCriteriaFilters($query);
        $this->applyAccessCriteriaFilters($query);
        $this->applyRoomCriteriaFilters($query);
        $this->applyNeighborCriteriaFilters($query);
        $this->applySafetyCriteriaFilters($query);
        $this->applyRuleCriteriaFilters($query);
        $this->applyComfortFilters($query);
        $this->applyCompatibilityFilters($query);
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
                'search_filtered_available' => $this->dateRange() !== null && $this->flexibleDateWindowDays() === 0,
                'comparison_ids' => session('comparison_places', []),
                'show_compatibility_warnings' => $this->showCompatibilityWarnings,
            ],
        );
    }

    private function applyLocationFilters(Builder $query): void
    {
        if ($countryId = $this->selectedCountryId()) {
            $query->where('search_properties.country_id', $countryId);
        }

        if ($cityId = $this->selectedCityId()) {
            $query->where('search_properties.city_id', $cityId);
        } elseif ($this->city !== '') {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.city', 'like', '%'.$this->city.'%')
                    ->orWhereHas('property.cityModel', fn (Builder $city) => $city->nameContainsInLocale($this->city, app()->getLocale()));
            });
        }

        if ($this->district !== '') {
            $this->wherePrefixText($query, ['search_properties.district'], $this->district);
        }

        if ($this->street !== '') {
            $this->wherePrefixText($query, [
                'search_properties.street',
                'search_properties.street_name',
            ], $this->street);
        }

        if ($this->landmark !== '') {
            $this->wherePrefixText($query, [
                'search_property_location_details.nearest_landmark',
            ], $this->landmark);
        }

        $this->applyLocationDetailFilters($query);
    }

    private function applyLocationDetailFilters(Builder $query): void
    {
        if ($this->nearCenter) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_location_details.distance_to_center_meters', '<=', self::NEAR_CENTER_DISTANCE_METERS)
                    ->orWhere('search_properties.distance_to_center_meters', '<=', self::NEAR_CENTER_DISTANCE_METERS)
                    ->orWhere('search_property_location_details.walk_minutes_to_center', '<=', self::NEAR_CENTER_WALK_MINUTES)
                    ->orWhere('search_property_location_details.transport_minutes_to_center', '<=', self::NEAR_CENTER_TRANSPORT_MINUTES);
            });
        }

        if ($this->nearMetro) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_metro');
                $builder->orWhere('search_property_location_details.nearest_metro_distance_meters', '<=', self::NEAR_METRO_DISTANCE_METERS);
            });
        }

        if ($this->nearBusStop) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_bus_stop');
                $builder->orWhere('search_property_location_details.nearest_bus_stop_distance_meters', '<=', self::NEAR_BUS_DISTANCE_METERS);
            });
        }

        if ($this->nearShop) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_shop');
                $this->orWhereFilledText($builder, 'search_property_location_details.nearest_supermarket');
            });
        }

        if ($this->nearPharmacy) {
            $this->whereFilledText($query, 'search_property_location_details.nearest_pharmacy');
        }

        if ($this->nearHospital) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_hospital');
                $this->orWhereFilledText($builder, 'search_property_location_details.nearest_clinic');
            });
        }

        if ($this->nearUniversity) {
            $this->whereFilledText($query, 'search_property_location_details.nearest_university');
        }

        if ($this->nearRailwayStation) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_railway_station');
                $this->orWhereFilledText($builder, 'search_property_location_details.nearest_train_station');
                $builder->orWhere('search_property_location_details.railway_station_distance_meters', '<=', self::NEAR_RAILWAY_DISTANCE_METERS);
            });
        }

        if ($this->nearPark) {
            $this->whereFilledText($query, 'search_property_location_details.nearest_park');
        }

        if ($this->nearShoppingCenter) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_mall');
                $this->orWhereFilledText($builder, 'search_property_location_details.nearest_shop');
                $this->orWhereFilledText($builder, 'search_property_location_details.nearest_supermarket');
            });
        }

        if ($this->nearGym) {
            $this->whereFilledText($query, 'search_property_location_details.nearest_gym');
        }

        if ($this->nearCoworking) {
            $this->whereFilledText($query, 'search_property_location_details.nearest_coworking');
        }

        if ($this->nearWork) {
            $query->where('search_property_location_details.near_work_area', true);
        }

        if ($this->nearSea) {
            $query->where('search_property_location_details.near_sea', true);
        }

        if ($this->nearNightlife) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_location_details.near_nightlife', true)
                    ->orWhere('search_property_location_details.has_bar_noise', true);
                $this->orWhereFilledText($builder, 'search_property_location_details.nearest_cafe');
            });
        }

        if ($this->nearAirport) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_property_location_details.nearest_airport');
                $builder->orWhere('search_property_location_details.airport_distance_meters', '<=', self::NEAR_AIRPORT_DISTANCE_METERS);
            });
        }

        if ($this->easyTransport) {
            $query->whereIn('search_property_location_details.transport_convenience_level', self::GOOD_TRANSPORT_LEVELS);
        }

        if ($this->quietDistrict) {
            $query->whereIn('search_property_location_details.district_noise_level', self::QUIET_DISTRICT_LEVELS);
        }

        if ($this->safeDistrict) {
            $query->whereIn('search_property_location_details.district_safety_level', self::SAFE_DISTRICT_LEVELS);
        }

        if ($this->areaResidential) {
            $query->where('search_property_location_details.area_residential', true);
        }

        if ($this->areaCityCenter) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_location_details.area_city_center', true)
                    ->orWhere('search_property_location_details.distance_to_center_meters', '<=', self::NEAR_CENTER_DISTANCE_METERS)
                    ->orWhere('search_properties.distance_to_center_meters', '<=', self::NEAR_CENTER_DISTANCE_METERS);
            });
        }

        if ($this->areaSuburb) {
            $query->where('search_property_location_details.area_suburb', true);
        }

        if ($this->areaIndustrial) {
            $query->where('search_property_location_details.area_industrial', true);
        }

        if ($this->areaTourist) {
            $query->where('search_property_location_details.area_tourist', true);
        }

        if ($this->areaStudents) {
            $query->where('search_property_location_details.area_students', true);
        }

        if ($this->areaWorkers) {
            $query->where('search_property_location_details.area_workers', true);
        }

        if ($this->areaLongStay) {
            $query->where('search_property_location_details.area_long_stay', true);
        }

        if ($this->goodStreetLighting) {
            $query->whereIn('search_property_location_details.street_lighting_level', self::GOOD_STREET_LIGHTING_LEVELS);
        }

        if ($this->freeParking) {
            $query->where('search_property_location_details.has_free_parking', true);
        }

        if ($this->paidParking) {
            $query->where('search_property_location_details.has_paid_parking', true);
        }
    }

    private function applyDateFilters(Builder $query): void
    {
        $dates = $this->dateRange();

        if ($dates) {
            $flexibleWindowDays = $this->flexibleDateWindowDays();

            if ($flexibleWindowDays > 0) {
                $this->applyFlexibleDateAvailability($query, $dates[0], $dates[1], $flexibleWindowDays);
            } else {
                $this->applyStayAvailability($query, $dates[0], $dates[1]);
            }
        }

        if ($this->availableToday) {
            $today = CarbonImmutable::today();

            $this->applyStayAvailability($query, $today, $today->addDay());
        }

        if ($this->availableTomorrow) {
            $tomorrow = CarbonImmutable::tomorrow();

            $this->applyStayAvailability($query, $tomorrow, $tomorrow->addDay());
        }

        if ($this->availableWeekend) {
            [$weekendStart, $weekendEnd] = $this->nextWeekendRange();

            $this->applyStayAvailability($query, $weekendStart, $weekendEnd);
        }

        if ($this->availableAfterCheckout && ($checkOut = $this->selectedCheckoutDate())) {
            $this->applyStayAvailability($query, $checkOut, $checkOut->addDay());
        }

        $this->applyStayLengthCriteria($query);
        $this->applyDateCapabilityCriteria($query);
    }

    private function applyStayAvailability(Builder $query, CarbonImmutable $start, CarbonImmutable $end): void
    {
        $nights = (int) $start->diffInDays($end);

        if ($nights < 1) {
            $query->where('sleeping_places.id', '<', 0);

            return;
        }

        $query->availableBetween($start->toDateString(), $end->toDateString());
        $this->applyStayLengthSupport($query, $nights);
    }

    private function applyFlexibleDateAvailability(Builder $query, CarbonImmutable $start, CarbonImmutable $end, int $windowDays): void
    {
        $today = CarbonImmutable::today();

        $query->where(function (Builder $builder) use ($end, $start, $today, $windowDays): void {
            $hasCandidate = false;

            for ($offset = -$windowDays; $offset <= $windowDays; $offset++) {
                $candidateStart = $start->addDays($offset);
                $candidateEnd = $end->addDays($offset);

                if ($candidateStart->isBefore($today) || $candidateEnd->lessThanOrEqualTo($candidateStart)) {
                    continue;
                }

                $method = $hasCandidate ? 'orWhere' : 'where';
                $builder->{$method}(function (Builder $candidate) use ($candidateEnd, $candidateStart): void {
                    $this->applyStayAvailability($candidate, $candidateStart, $candidateEnd);
                });

                $hasCandidate = true;
            }

            if (! $hasCandidate) {
                $builder->where('sleeping_places.id', '<', 0);
            }
        });
    }

    private function applyStayLengthSupport(Builder $query, int $nights): void
    {
        $query->where('sleeping_places.min_nights', '<=', $nights)
            ->where(function (Builder $builder) use ($nights): void {
                $builder->whereNull('sleeping_places.max_nights')
                    ->orWhere('sleeping_places.max_nights', '>=', $nights);
            });
    }

    private function applyStayLengthCriteria(Builder $query): void
    {
        $minimumStayNights = $this->selectedMinimumStayNights();
        $maximumStayNights = $this->selectedMaximumStayNights();

        if ($minimumStayNights !== null && $maximumStayNights !== null && $minimumStayNights > $maximumStayNights) {
            $query->where('sleeping_places.id', '<', 0);

            return;
        }

        if ($this->shortStayAllowed) {
            $query->where('sleeping_places.min_nights', '<=', self::SHORT_STAY_MAX_NIGHTS);
        }

        if ($minimumStayNights !== null) {
            $query->where(function (Builder $builder) use ($minimumStayNights): void {
                $builder->whereNull('sleeping_places.max_nights')
                    ->orWhere('sleeping_places.max_nights', '>=', $minimumStayNights);
            });
        }

        if ($maximumStayNights !== null) {
            $query->where('sleeping_places.min_nights', '<=', $maximumStayNights);
        }
    }

    private function applyDateCapabilityCriteria(Builder $query): void
    {
        if ($this->canExtendStay && $this->cannotExtendStay) {
            $query->where('sleeping_places.id', '<', 0);

            return;
        }

        if ($this->canExtendStay) {
            $query->where(function (Builder $builder): void {
                $builder->where('sleeping_places.can_extend', true)
                    ->orWhere('sleeping_places.extensions_allowed', true)
                    ->orWhereHas('calendarSettings', fn (Builder $settings) => $settings->where('can_extend', true));
            });
        }

        if ($this->cannotExtendStay) {
            $query->where('sleeping_places.can_extend', false)
                ->where('sleeping_places.extensions_allowed', false)
                ->whereDoesntHave('calendarSettings', fn (Builder $settings) => $settings->where('can_extend', true));
        }

        if ($this->nightCheckIn) {
            $query->whereHas('calendarSettings', function (Builder $settings): void {
                $this->whereAnyCalendarTimeAtOrAfter($settings, ['check_in_time_until', 'default_check_in_time'], self::NIGHT_CHECK_IN_TIME);
            });
        }

        if ($this->nightCheckOut) {
            $query->whereHas('calendarSettings', function (Builder $settings): void {
                $this->whereAnyCalendarTimeAtOrAfter($settings, ['check_out_time_until', 'latest_check_out_time', 'default_check_out_time'], self::NIGHT_CHECK_OUT_TIME);
            });
        }

        if ($this->earlyMorningCheckIn) {
            $query->where(function (Builder $builder): void {
                $builder->where('sleeping_places.early_check_in_allowed', true)
                    ->orWhereHas('calendarSettings', function (Builder $settings): void {
                        $this->whereAnyCalendarTimeAtOrBefore($settings, ['earliest_check_in_time', 'check_in_time_from', 'default_check_in_time'], self::EARLY_MORNING_CHECK_IN_TIME);
                    });
            });
        }

        if ($this->lateEveningCheckOut) {
            $query->where(function (Builder $builder): void {
                $builder->where('sleeping_places.late_check_out_allowed', true)
                    ->orWhereHas('calendarSettings', function (Builder $settings): void {
                        $this->whereAnyCalendarTimeAtOrAfter($settings, ['check_out_time_until', 'latest_check_out_time', 'default_check_out_time'], self::LATE_EVENING_CHECK_OUT_TIME);
                    });
            });
        }
    }

    /**
     * @param  list<string>  $columns
     */
    private function whereAnyCalendarTimeAtOrAfter(Builder $query, array $columns, string $time): void
    {
        $query->where(function (Builder $builder) use ($columns, $time): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $builder->{$method}(function (Builder $timeQuery) use ($column, $time): void {
                    $timeQuery->whereNotNull($column)
                        ->where($column, '>=', $time);
                });
            }
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function whereAnyCalendarTimeAtOrBefore(Builder $query, array $columns, string $time): void
    {
        $query->where(function (Builder $builder) use ($columns, $time): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $builder->{$method}(function (Builder $timeQuery) use ($column, $time): void {
                    $timeQuery->whereNotNull($column)
                        ->where($column, '<=', $time);
                });
            }
        });
    }

    private function applyPriceAndTypeFilters(Builder $query): void
    {
        if (($priceMin = $this->decimal($this->priceMin)) !== null) {
            $this->wherePriceAmount($query, '>=', $priceMin);
        }

        if (($priceMax = $this->decimal($this->priceMax)) !== null) {
            $this->wherePriceAmount($query, '<=', $priceMax);
        }

        if ($this->currency !== '') {
            $currency = strtoupper($this->currency);

            $query->where(function (Builder $builder) use ($currency): void {
                $builder->where('sleeping_places.currency', $currency)
                    ->orWhereHas('pricingSettings', fn (Builder $settings): Builder => $settings->where('currency', $currency));
            });
        }

        $this->applyExtendedPriceFilters($query);

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

    private function applyExtendedPriceFilters(Builder $query): void
    {
        $this->applyPriceBasisFilter($query);
        $this->applyDepositFilters($query);

        if ($this->noCleaningFee) {
            $this->whereNoCleaningFee($query);
        }

        if ($this->partialRefund) {
            $this->whereCancellationPolicyIn($query, self::PARTIAL_REFUND_POLICIES);
        }

        if ($this->fullRefund) {
            $this->whereCancellationPolicyIn($query, self::FULL_REFUND_POLICIES);
        }

        if ($this->installmentPayment) {
            $this->wherePricingSettingFlag($query, 'installment_payment_allowed');
        }

        if ($this->payLater) {
            $this->wherePricingSettingFlag($query, 'pay_later_allowed');
        }

        if ($this->payOnArrival) {
            $this->wherePricingSettingFlag($query, 'pay_on_arrival_allowed');
        }

        if ($this->hasDiscount) {
            $this->whereHasActiveDiscount($query);
        }

        if ($this->hasPromoCode) {
            $this->whereHasActivePromoCode($query);
        }

        if ($this->belowAveragePrice) {
            $average = $this->averageVisibleNightlyPrice();

            if ($average !== null) {
                $query->where('sleeping_places.base_price_per_night', '<=', $average);
            }
        }

        if ($this->priceIncludesAllFees) {
            $this->wherePricingSettingFlag($query, 'all_fees_included');
        }

        if ($this->showTotalPriceImmediately) {
            $this->wherePricingSettingFlag($query, 'show_total_price_upfront');
        }

        if ($this->hideHiddenFees) {
            $this->wherePricingSettingFlag($query, 'hidden_fees_disclosed');
        }
    }

    private function applyPriceBasisFilter(Builder $query): void
    {
        $basis = $this->explicitPriceBasis();

        if ($basis === null) {
            return;
        }

        [$placeColumn, $settingsColumn] = $this->priceColumnsForBasis($basis);

        $query->where(function (Builder $builder) use ($basis, $placeColumn, $settingsColumn): void {
            $builder->where($placeColumn, '>', 0)
                ->orWhereHas('pricingSettings', function (Builder $settings) use ($basis, $settingsColumn): void {
                    $settings->where(function (Builder $pricing) use ($basis, $settingsColumn): void {
                        $pricing->where($settingsColumn, '>', 0);

                        if ($basis === self::PRICE_BASIS_WEEKLY) {
                            $pricing->orWhereIn('pricing_strategy', [
                                'weekly_package',
                                'best_price',
                            ]);
                        }

                        if ($basis === self::PRICE_BASIS_MONTHLY) {
                            $pricing->orWhereIn('pricing_strategy', [
                                'monthly_package',
                                'best_price',
                            ]);
                        }
                    });
                });
        });
    }

    private function applyDepositFilters(Builder $query): void
    {
        if ($this->noDeposit && ($this->withDeposit || $this->lowDeposit)) {
            $query->where('sleeping_places.id', -1);

            return;
        }

        if ($this->noDeposit) {
            $this->whereNoDeposit($query);
        }

        if ($this->withDeposit) {
            $this->whereWithDeposit($query);
        }

        if ($this->lowDeposit) {
            $this->whereLowDeposit($query);
        }
    }

    private function wherePriceAmount(Builder $query, string $operator, float $amount): void
    {
        [$placeColumn, $settingsColumn] = $this->priceColumnsForBasis($this->selectedPriceBasis());

        $query->where(function (Builder $builder) use ($amount, $operator, $placeColumn, $settingsColumn): void {
            $builder->where($placeColumn, $operator, $amount)
                ->orWhereHas('pricingSettings', fn (Builder $settings): Builder => $settings->where($settingsColumn, $operator, $amount));
        });
    }

    private function whereNoDeposit(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->whereNull('sleeping_places.deposit_amount')
                ->orWhere('sleeping_places.deposit_amount', '<=', 0)
                ->orWhereHas('pricingSettings', function (Builder $settings): void {
                    $settings->where(function (Builder $pricing): void {
                        $pricing->where('deposit_required', false)
                            ->orWhereNull('deposit_amount')
                            ->orWhere('deposit_amount', '<=', 0);
                    });
                });
        });
    }

    private function whereWithDeposit(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->where('sleeping_places.deposit_amount', '>', 0)
                ->orWhereHas('pricingSettings', function (Builder $settings): void {
                    $settings->where('deposit_required', true)
                        ->where('deposit_amount', '>', 0);
                });
        });
    }

    private function whereLowDeposit(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->where(function (Builder $place): void {
                $place->where('sleeping_places.deposit_amount', '>', 0)
                    ->where('sleeping_places.deposit_amount', '<=', self::LOW_DEPOSIT_MAX_AMOUNT);
            })->orWhereHas('pricingSettings', function (Builder $settings): void {
                $settings->where('deposit_required', true)
                    ->where('deposit_amount', '>', 0)
                    ->where('deposit_amount', '<=', self::LOW_DEPOSIT_MAX_AMOUNT);
            });
        });
    }

    private function whereNoCleaningFee(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->whereNull('sleeping_places.cleaning_fee')
                ->orWhere('sleeping_places.cleaning_fee', '<=', 0)
                ->orWhereHas('pricingSettings', function (Builder $settings): void {
                    $settings->where(function (Builder $pricing): void {
                        $pricing->whereNull('cleaning_fee')
                            ->orWhere('cleaning_fee', '<=', 0);
                    });
                });
        });
    }

    private function whereCancellationPolicyIn(Builder $query, array $policies): void
    {
        $query->where(function (Builder $builder) use ($policies): void {
            $builder->whereIn('sleeping_places.cancellation_policy', $policies)
                ->orWhere(function (Builder $fallback) use ($policies): void {
                    $fallback->whereNull('sleeping_places.cancellation_policy')
                        ->whereIn('search_host_profiles.default_cancellation_policy', $policies);
                });
        });
    }

    private function wherePricingSettingFlag(Builder $query, string $column): void
    {
        $query->whereHas('pricingSettings', fn (Builder $settings): Builder => $settings->where($column, true));
    }

    private function whereHasActiveDiscount(Builder $query): void
    {
        $now = CarbonImmutable::now();
        $today = $now->toDateString();

        $query->where(function (Builder $builder) use ($now, $today): void {
            $builder->where('sleeping_places.weekly_price', '>', 0)
                ->orWhere('sleeping_places.monthly_price', '>', 0)
                ->orWhereHas('pricingSettings', function (Builder $settings): void {
                    $settings->where(function (Builder $pricing): void {
                        $pricing->where('weekly_price', '>', 0)
                            ->orWhere('monthly_price', '>', 0);
                    });
                })
                ->orWhereHas('pricingDiscountRules', function (Builder $discount) use ($now): void {
                    $discount->where('active', true)
                        ->where(function (Builder $dates) use ($now): void {
                            $dates->whereNull('starts_at')
                                ->orWhere('starts_at', '<=', $now);
                        })
                        ->where(function (Builder $dates) use ($now): void {
                            $dates->whereNull('ends_at')
                                ->orWhere('ends_at', '>=', $now);
                        });
                })
                ->orWhereHas('discountRules', function (Builder $discount) use ($today): void {
                    $discount->where('status', 'active')
                        ->where(function (Builder $dates) use ($today): void {
                            $dates->whereNull('starts_on')
                                ->orWhere('starts_on', '<=', $today);
                        })
                        ->where(function (Builder $dates) use ($today): void {
                            $dates->whereNull('ends_on')
                                ->orWhere('ends_on', '>=', $today);
                        });
                });
        });
    }

    private function whereHasActivePromoCode(Builder $query): void
    {
        $now = CarbonImmutable::now();

        $query->whereHas('promoCodes', function (Builder $promoCode) use ($now): void {
            $promoCode->active()
                ->where(function (Builder $dates) use ($now): void {
                    $dates->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', $now);
                })
                ->where(function (Builder $dates) use ($now): void {
                    $dates->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', $now);
                });
        });
    }

    private function averageVisibleNightlyPrice(): ?float
    {
        $average = SleepingPlace::query()
            ->where('status', SleepingPlaceStatus::Active->value)
            ->whereNotNull('base_price_per_night')
            ->avg('base_price_per_night');

        return $average === null ? null : (float) $average;
    }

    /**
     * @return array{0:string,1:string}
     */
    private function priceColumnsForBasis(string $basis): array
    {
        return match ($basis) {
            self::PRICE_BASIS_WEEKLY => ['sleeping_places.weekly_price', 'weekly_price'],
            self::PRICE_BASIS_MONTHLY => ['sleeping_places.monthly_price', 'monthly_price'],
            default => ['sleeping_places.base_price_per_night', 'base_nightly_price'],
        };
    }

    private function selectedPriceBasis(): string
    {
        return $this->explicitPriceBasis() ?? self::PRICE_BASIS_NIGHTLY;
    }

    private function explicitPriceBasis(): ?string
    {
        return in_array($this->priceBasis, [
            self::PRICE_BASIS_NIGHTLY,
            self::PRICE_BASIS_WEEKLY,
            self::PRICE_BASIS_MONTHLY,
        ], true) ? $this->priceBasis : null;
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

    private function applyConditionCriteriaFilters(Builder $query): void
    {
        if ($this->cleanProperty) {
            $query->whereIn('search_property_condition_details.cleanliness_level', self::CLEAN_PROPERTY_LEVELS);
        }

        if ($this->noInsects) {
            $query->where('search_property_condition_details.has_insects', false);
        }

        if ($this->noMold) {
            $query->where('search_property_condition_details.has_mold', false);
        }

        if ($this->normalHumidity) {
            $query->whereIn('search_property_condition_details.humidity_level', self::NORMAL_HUMIDITY_LEVELS);
        }

        if ($this->comfortableWinter) {
            $query->whereIn('search_property_condition_details.winter_temperature_level', self::COMFORTABLE_WINTER_LEVELS);
        }

        if ($this->comfortableSummer) {
            $query->whereIn('search_property_condition_details.summer_temperature_level', self::COMFORTABLE_SUMMER_LEVELS);
        }

        if ($this->quietProperty) {
            $query->whereIn('search_property_condition_details.indoor_noise_level', self::QUIET_WINDOW_NOISE_LEVELS);
        }

        if ($this->brightProperty) {
            $query->whereIn('search_property_condition_details.light_level', self::BRIGHT_ROOM_LIGHT_LEVELS);
        }
    }

    private function applyAccessCriteriaFilters(Builder $query): void
    {
        if ($this->doorCodeAccess) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.has_door_code', true)
                    ->orWhere('search_property_access_details.entrance_type', 'code_door');
            });
        }

        if ($this->electronicLockAccess) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.has_electronic_lock', true)
                    ->orWhere('search_property_access_details.entrance_type', 'electronic_lock');
            });
        }

        if ($this->keySafeAccess) {
            $query->where('search_property_access_details.has_key_safe', true);
        }

        if ($this->access247) {
            $query->where('search_property_access_details.access_24_7', true);
        }

        if ($this->noNightEntryRestrictions) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.has_night_entry_restrictions', false)
                    ->orWhereNull('search_property_access_details.has_night_entry_restrictions');
            });
        }

        if ($this->guestRules) {
            $query->where('search_property_access_details.guest_rules_enabled', true);
        }

        if ($this->courierRules) {
            $query->where('search_property_access_details.courier_rules_enabled', true);
        }

        if ($this->deliveryAvailable) {
            $query->where('search_property_access_details.delivery_allowed', true);
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
            $this->whereRoomHasLock($query);
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

    private function applyNeighborCriteriaFilters(Builder $query): void
    {
        if (($maxRoommates = $this->nonNegativeInteger($this->neighborRoommatesMax)) !== null) {
            $query->where(function (Builder $builder) use ($maxRoommates): void {
                $builder->where('search_room_occupancy_snapshots.current_occupants_count', '<=', $maxRoommates)
                    ->orWhere('search_rooms.current_guests_count', '<=', $maxRoommates)
                    ->orWhere('search_rooms.occupied_sleeping_places_count', '<=', $maxRoommates);
            });
        }

        if (($maxResidents = $this->nonNegativeInteger($this->propertyResidentsMax)) !== null) {
            $query->where(function (Builder $builder) use ($maxResidents): void {
                $builder->where('search_property_occupancy_snapshots.current_occupants_count', '<=', $maxResidents)
                    ->orWhere('search_properties.current_residents_count', '<=', $maxResidents)
                    ->orWhere('search_properties.current_guests_count', '<=', $maxResidents);
            });
        }

        if (in_array($this->neighborAgeRange, self::NEIGHBOR_AGE_RANGES, true)) {
            $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->where('age_range_snapshot', $this->neighborAgeRange),
            );
        }

        if (in_array($this->neighborLanguage, self::NEIGHBOR_LANGUAGES, true)) {
            $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->whereJsonContains('languages_json_snapshot', $this->neighborLanguage),
            );
        }

        if (in_array($this->neighborLifestyle, self::NEIGHBOR_LIFESTYLES, true)) {
            $this->applyNeighborLifestyleFilter($query, $this->neighborLifestyle);
        }

        if (($rating = $this->neighborRatingThreshold()) !== null) {
            $query->where('search_room_rating_snapshots.reviews_count', '>', 0)
                ->where('search_room_rating_snapshots.roommate_experience_rating', '>=', $rating);
        }

        if ($this->neighborStudents) {
            $this->whereNeighborCountOrSnapshot($query, 'students_count', 'student_snapshot');
        }

        if ($this->neighborWorkers) {
            $this->whereNeighborCountOrSnapshot($query, 'workers_count', 'working_snapshot');
        }

        if ($this->neighborTourists) {
            $this->whereNeighborCountOrSnapshot($query, 'tourists_count', 'tourist_snapshot');
        }

        if ($this->neighborLongTerm) {
            $this->whereNeighborCountOrSnapshot($query, 'long_term_residents_count', 'long_term_guest_snapshot');
        }

        if ($this->quietNeighbors) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.quiet_preferring_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot($builder, function (Builder $snapshot): Builder {
                    return $snapshot->where('prefers_quiet_snapshot', true)
                        ->orWhereIn('social_level_snapshot', self::QUIET_NEIGHBOR_VALUES);
                });
            });
        }

        if ($this->socialNeighbors) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.social_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->whereIn('social_level_snapshot', self::SOCIAL_NEIGHBOR_VALUES),
                );
            });
        }

        if ($this->neighborsOftenHome) {
            $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->whereIn('home_presence_level_snapshot', self::OFTEN_HOME_VALUES),
            );
        }

        if ($this->neighborsRarelyHome) {
            $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->whereIn('home_presence_level_snapshot', self::RARELY_HOME_VALUES),
            );
        }

        if ($this->neighborsWorkAtNight) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.night_work_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->whereIn('sleep_schedule_snapshot', self::NIGHT_WORK_VALUES),
                );
            });
        }

        if ($this->neighborsWakeEarly) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.early_wakeup_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot($builder, function (Builder $snapshot): Builder {
                    return $snapshot->whereIn('wake_schedule_snapshot', self::EARLY_WAKE_VALUES)
                        ->orWhereIn('sleep_schedule_snapshot', self::EARLY_WAKE_VALUES);
                });
            });
        }

        if ($this->neighborsSleepLate) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.late_sleep_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->whereIn('sleep_schedule_snapshot', self::LATE_SLEEP_VALUES),
                );
            });
        }

        if ($this->neighborsSmoke) {
            $this->whereNeighborCountOrSnapshot($query, 'smokers_count', 'smokes_snapshot');
        }

        if ($this->neighborsDoNotSmoke) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.current_occupants_count', 0)
                    ->orWhere(function (Builder $nonSmokingNeighbors): void {
                        $nonSmokingNeighbors->where('search_room_occupancy_snapshots.non_smokers_count', '>', 0)
                            ->where(function (Builder $smokers): void {
                                $smokers->whereNull('search_room_occupancy_snapshots.smokers_count')
                                    ->orWhere('search_room_occupancy_snapshots.smokers_count', 0);
                            });
                    });
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->where('smokes_snapshot', false),
                );
            });
            $this->whereRoomDoesntHaveVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->where('smokes_snapshot', true),
            );
        }

        if ($this->neighborsWithPets) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_compatibility_profiles.pets_present', true);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->where('has_pet_snapshot', true),
                );
            });
        }

        if ($this->neighborsWithoutPets) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_compatibility_profiles.pets_present', false)
                    ->orWhere('search_room_occupancy_snapshots.current_occupants_count', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->where('has_pet_snapshot', false),
                );
            });
            $this->whereRoomDoesntHaveVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->where('has_pet_snapshot', true),
            );
        }

        if ($this->maleNeighborsPresent) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.male_occupants_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->where('gender_for_room_policy_snapshot', GenderType::Male->value),
                );
            });
        }

        if ($this->femaleNeighborsPresent) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.female_occupants_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->where('gender_for_room_policy_snapshot', GenderType::Female->value),
                );
            });
        }

        if ($this->mixedNeighborGenders) {
            $query->where(function (Builder $builder): void {
                $builder->where(function (Builder $counts): void {
                    $counts->where('search_room_occupancy_snapshots.male_occupants_count', '>', 0)
                        ->where('search_room_occupancy_snapshots.female_occupants_count', '>', 0);
                })->orWhere(function (Builder $room): void {
                    $room->where('search_rooms.gender_policy', GenderType::Mixed->value)
                        ->orWhere('search_rooms.gender_type', GenderType::Mixed->value);
                });
            });
        }
    }

    private function applySafetyCriteriaFilters(Builder $query): void
    {
        if ($this->safetyVerifiedHost) {
            $query->where(function (Builder $builder): void {
                $builder->whereNotNull('search_host_profiles.verified_at')
                    ->orWhere('search_host_profiles.verified_host', true)
                    ->orWhereHas('property.host', fn (Builder $host) => $host->where('identity_verified', true));
            });
        }

        if ($this->safetyVerifiedProperty) {
            $query->where(function (Builder $builder): void {
                $builder->whereNotNull('search_properties.reviewed_at')
                    ->orWhereIn('search_properties.review_status', self::VERIFIED_PROPERTY_REVIEW_STATUSES);
            });
        }

        if ($this->safetyVerifiedGuests) {
            $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->whereHas('user', function (Builder $user): void {
                    $user->where('identity_verified', true)
                        ->orWhereNotNull('identity_verified_at');
                }),
            );
        }

        if ($this->safetyRoomLock) {
            $this->whereRoomHasLock($query);
        }

        if ($this->safetyEntranceLock) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.has_key', true)
                    ->orWhere('search_property_access_details.has_door_code', true)
                    ->orWhere('search_property_access_details.has_electronic_lock', true)
                    ->orWhere('search_property_access_details.has_keycard', true)
                    ->orWhere('search_property_access_details.has_smart_lock', true)
                    ->orWhereIn('search_property_access_details.entrance_type', ['locked_door', 'code_door', 'electronic_lock']);
                $this->orWhereHasAnyAmenity($builder, ['front_door_lock', 'electronic_lock']);
            });
        }

        if ($this->safetyPersonalLocker) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_rooms.has_lockers', true)
                    ->orWhere('sleeping_places.has_locker', true)
                    ->orWhere('sleeping_places.has_lockable_locker', true)
                    ->orWhere('sleeping_places.locker_has_lock', true)
                    ->orWhereHas('storageDetails', function (Builder $details): void {
                        $details->where('has_personal_locker', true)
                            ->orWhere('has_lockable_locker', true);
                    });
                $this->orWhereHasAnyAmenity($builder, ['personal_locker', 'locker_with_lock']);
            });
        }

        if ($this->safetySafe) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.has_key_safe', true);
                $this->orWhereHasAnyAmenity($builder, ['safe', 'key_safe']);
            });
        }

        if ($this->safetySecurityGuard) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.has_security', true);
                $this->orWhereHasAnyAmenity($builder, ['security']);
            });
        }

        if ($this->safetyIntercom) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.has_intercom', true)
                    ->orWhere('search_property_access_details.has_intercom_code', true);
                $this->orWhereHasAnyAmenity($builder, ['intercom']);
            });
        }

        if ($this->safetyCctvCommonAreas) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.has_cctv_common_areas', true);
                $this->orWhereHasAnyAmenity($builder, ['cctv_common_areas']);
            });
        }

        if ($this->safetyNoPrivateCameras) {
            $this->whereHasAnyAmenity($query, ['no_private_area_cameras']);
        }

        if ($this->safetyFirstAidKit) {
            $this->whereHasAnyAmenity($query, ['first_aid_kit']);
        }

        if ($this->safetyFireExtinguisher) {
            $this->whereHasAnyAmenity($query, ['fire_extinguisher']);
        }

        if ($this->safetySmokeDetector) {
            $this->whereHasAnyAmenity($query, ['smoke_detector']);
        }

        if ($this->safetyGasDetector) {
            $this->whereHasAnyAmenity($query, ['gas_detector']);
        }

        if ($this->safetyFireInstructions) {
            $this->whereHasAnyAmenity($query, ['fire_safety_instructions']);
        }

        if ($this->safetyEmergencyExit) {
            $this->whereHasAnyAmenity($query, ['emergency_exit']);
        }

        if ($this->safetyExactAddressAfterBooking) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_properties.show_exact_address_after_confirmation', true)
                    ->orWhere('search_properties.show_exact_address_after_payment', true)
                    ->orWhereHas('property.address', fn (Builder $address) => $address->where('show_exact_address_after_booking', true));
            });
        }

        if ($this->safetyEmergencyContact) {
            $query->where(function (Builder $builder): void {
                $this->whereFilledText($builder, 'search_properties.emergency_contact_phone');
                $builder->orWhere('search_property_access_details.emergency_contact_available', true)
                    ->orWhere('search_host_profiles.emergency_contact_available', true);
            });
        }

        if ($this->safetyUrgentSupport) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_host_profiles.response_time_minutes', '<=', self::URGENT_SUPPORT_RESPONSE_MINUTES)
                    ->orWhere('search_host_profiles.can_help_with_check_in', true)
                    ->orWhere('search_host_profiles.emergency_contact_available', true);
                $this->orWhereHasAnyAmenity($builder, ['urgent_help_available']);
            });
        }

        if ($this->safetyGoodRating) {
            $this->whereGoodSafetyRating($query);
        }

        if ($this->safetyNoSeriousComplaints) {
            $this->whereNoActiveComplaints($query, self::SERIOUS_COMPLAINT_TYPES, true);
        }

        if ($this->safetyNoTheftComplaints) {
            $this->whereNoActiveComplaints($query, self::THEFT_COMPLAINT_TYPES);
        }

        if ($this->safetyNoAggressionComplaints) {
            $this->whereNoActiveComplaints($query, self::AGGRESSION_COMPLAINT_TYPES);
        }

        if ($this->safetyNoDirtComplaints) {
            $this->whereNoActiveComplaints($query, self::DIRT_COMPLAINT_TYPES);
        }

        if ($this->safetyNoFraudComplaints) {
            $this->whereNoActiveComplaints($query, self::FRAUD_COMPLAINT_TYPES);
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
                $builder->where('search_property_access_details.access_24_7', true)
                    ->orWhere('search_property_access_details.self_check_in_available_at_night', true)
                    ->orWhere('search_property_access_details.can_return_at_night', true);
                $this->orWhereHasAnyAmenity($builder, ['self_check_in', 'key_safe', 'electronic_lock']);
                $builder->orWhereHas('property.host.hostProfile', fn (Builder $host) => $host->where('can_help_with_check_in', true));
            });
        }

        if ($this->selfCheckIn) {
            $query->where(function (Builder $builder): void {
                $builder->where('search_property_access_details.self_check_in_available', true)
                    ->orWhere('search_property_access_details.has_key_safe', true)
                    ->orWhere('search_property_access_details.has_electronic_lock', true);
                $this->orWhereHasAnyAmenity($builder, ['self_check_in', 'key_safe', 'electronic_lock']);
            });
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
                $builder->where('search_properties.has_parking', true)
                    ->orWhere('search_property_location_details.has_parking_nearby', true)
                    ->orWhere('search_property_location_details.has_free_parking', true)
                    ->orWhere('search_property_location_details.has_paid_parking', true)
                    ->orWhere('search_property_location_details.has_private_parking', true);
                $this->orWhereHasAnyAmenity($builder, ['parking']);
            });
        }

        if ($this->longStayAllowed) {
            $query->where(function (Builder $builder): void {
                $builder->whereNull('sleeping_places.max_nights')
                    ->orWhere('sleeping_places.max_nights', '>=', self::LONG_STAY_MIN_NIGHTS)
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
            $this->whereCancellationPolicyIn($query, self::FULL_REFUND_POLICIES);
        }
    }

    private function applyCompatibilityFilters(Builder $query): void
    {
        $statuses = $this->allowedCompatibilityStatuses();
        $dates = $this->dateRange();
        $userId = auth()->id();

        if ($statuses === null || ! $dates || ! $userId) {
            return;
        }

        $subquery = CompatibilityResult::query()
            ->select('sleeping_place_id')
            ->fresh()
            ->where('user_id', $userId)
            ->where('check_in_date', '>=', $dates[0]->toDateString())
            ->where('check_in_date', '<', $dates[0]->addDay()->toDateString())
            ->where('check_out_date', '>=', $dates[1]->toDateString())
            ->where('check_out_date', '<', $dates[1]->addDay()->toDateString())
            ->whereIn('fit_status', $statuses);

        $query->whereIn('sleeping_places.id', $subquery);
    }

    private function applySorting(Builder $query): void
    {
        match ($this->sort) {
            'cheapest' => $query->orderBy('sleeping_places.base_price_per_night')->orderByDesc('sleeping_places.id'),
            'best_value' => $query->orderByDesc('search_host_profiles.rating_average')->orderByDesc('search_host_profiles.reviews_count')->orderBy('sleeping_places.base_price_per_night')->orderByDesc('sleeping_places.id'),
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

    private function whereFilledText(Builder $query, string $column): void
    {
        $query->whereNotNull($column)
            ->where($column, '!=', '');
    }

    /**
     * @param  list<string>  $columns
     */
    private function wherePrefixText(Builder $query, array $columns, string $value): void
    {
        $pattern = $this->prefixSearchPattern($value);

        if ($pattern === null) {
            return;
        }

        $query->where(function (Builder $builder) use ($columns, $pattern): void {
            foreach ($columns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                $builder->{$method}($column, 'like', $pattern);
            }
        });
    }

    private function prefixSearchPattern(string $value): ?string
    {
        $term = trim(str_replace(['%', '_'], ' ', $value));

        if ($term === '') {
            return null;
        }

        return $term.'%';
    }

    private function orWhereFilledText(Builder $query, string $column): void
    {
        $query->orWhere(function (Builder $builder) use ($column): void {
            $this->whereFilledText($builder, $column);
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

    private function whereRoomHasLock(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->where('search_rooms.has_lock', true)
                ->orWhere('search_rooms.has_lockable_door', true)
                ->orWhere('search_rooms.has_room_key', true);
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

    private function applyNeighborLifestyleFilter(Builder $query, string $lifestyle): void
    {
        match ($lifestyle) {
            'quiet' => $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.quiet_preferring_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot($builder, function (Builder $snapshot): Builder {
                    return $snapshot->where('prefers_quiet_snapshot', true)
                        ->orWhereIn('social_level_snapshot', self::QUIET_NEIGHBOR_VALUES);
                });
            }),
            'social' => $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.social_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->whereIn('social_level_snapshot', self::SOCIAL_NEIGHBOR_VALUES),
                );
            }),
            'work_study' => $query->where(function (Builder $builder): void {
                $builder->where('search_room_occupancy_snapshots.students_count', '>', 0)
                    ->orWhere('search_room_occupancy_snapshots.workers_count', '>', 0);
                $this->orWhereRoomHasVisibleNeighborSnapshot($builder, function (Builder $snapshot): Builder {
                    return $snapshot->where('student_snapshot', true)
                        ->orWhere('working_snapshot', true)
                        ->orWhere('remote_worker_snapshot', true);
                });
            }),
            'tourist' => $this->whereNeighborCountOrSnapshot($query, 'tourists_count', 'tourist_snapshot'),
            'long_stay' => $this->whereNeighborCountOrSnapshot($query, 'long_term_residents_count', 'long_term_guest_snapshot'),
            'often_home' => $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->whereIn('home_presence_level_snapshot', self::OFTEN_HOME_VALUES),
            ),
            'rarely_home' => $this->whereRoomHasVisibleNeighborSnapshot(
                $query,
                fn (Builder $snapshot): Builder => $snapshot->whereIn('home_presence_level_snapshot', self::RARELY_HOME_VALUES),
            ),
        };
    }

    private function whereNeighborCountOrSnapshot(Builder $query, string $countColumn, ?string $snapshotColumn): void
    {
        $query->where(function (Builder $builder) use ($countColumn, $snapshotColumn): void {
            $builder->where('search_room_occupancy_snapshots.'.$countColumn, '>', 0);

            if ($snapshotColumn !== null) {
                $this->orWhereRoomHasVisibleNeighborSnapshot(
                    $builder,
                    fn (Builder $snapshot): Builder => $snapshot->where($snapshotColumn, true),
                );
            }
        });
    }

    private function whereRoomHasVisibleNeighborSnapshot(Builder $query, callable $callback): void
    {
        $query->whereHas('room.occupantSnapshots', function (Builder $snapshot) use ($callback): void {
            $this->applyVisibleNeighborSnapshotScope($snapshot);
            $snapshot->where(function (Builder $criteria) use ($callback): void {
                $callback($criteria);
            });
        });
    }

    private function orWhereRoomHasVisibleNeighborSnapshot(Builder $query, callable $callback): void
    {
        $query->orWhereHas('room.occupantSnapshots', function (Builder $snapshot) use ($callback): void {
            $this->applyVisibleNeighborSnapshotScope($snapshot);
            $snapshot->where(function (Builder $criteria) use ($callback): void {
                $callback($criteria);
            });
        });
    }

    private function whereRoomDoesntHaveVisibleNeighborSnapshot(Builder $query, callable $callback): void
    {
        $query->whereDoesntHave('room.occupantSnapshots', function (Builder $snapshot) use ($callback): void {
            $this->applyVisibleNeighborSnapshotScope($snapshot);
            $snapshot->where(function (Builder $criteria) use ($callback): void {
                $callback($criteria);
            });
        });
    }

    private function applyVisibleNeighborSnapshotScope(Builder $query): void
    {
        $query->where('can_show_before_booking', true);

        $dates = $this->dateRange();

        if ($dates) {
            $query->visibleOccupants()
                ->overlapping($dates[0]->toDateString(), $dates[1]->toDateString());

            return;
        }

        $query->whereIn('status', self::CURRENT_NEIGHBOR_STATUSES);
    }

    private function whereGoodSafetyRating(Builder $query): void
    {
        $query->where(function (Builder $builder): void {
            $builder->where(function (Builder $place): void {
                $place->where('search_sleeping_place_rating_snapshots.reviews_count', '>', 0)
                    ->where('search_sleeping_place_rating_snapshots.safety_rating', '>=', self::SAFETY_RATING_THRESHOLD);
            })->orWhere(function (Builder $room): void {
                $room->where('search_room_rating_snapshots.reviews_count', '>', 0)
                    ->where('search_room_rating_snapshots.safety_rating', '>=', self::SAFETY_RATING_THRESHOLD);
            })->orWhere(function (Builder $property): void {
                $property->where('search_property_rating_snapshots.reviews_count', '>', 0)
                    ->where('search_property_rating_snapshots.safety_rating', '>=', self::SAFETY_RATING_THRESHOLD);
            });
        });
    }

    /**
     * @param  list<string>  $types
     */
    private function whereNoActiveComplaints(Builder $query, array $types, bool $seriousOnly = false): void
    {
        $this->whereNoActiveComplaintCases($query, $types, $seriousOnly);
        $this->whereNoActiveLegacyComplaints($query, $types, $seriousOnly);
    }

    /**
     * @param  list<string>  $types
     */
    private function whereNoActiveComplaintCases(Builder $query, array $types, bool $seriousOnly): void
    {
        $query
            ->whereNotIn('sleeping_places.id', $this->activeComplaintCaseSubquery('sleeping_place_id', $types, $seriousOnly))
            ->whereNotIn('search_rooms.id', $this->activeComplaintCaseSubquery('room_id', $types, $seriousOnly))
            ->whereNotIn('search_properties.id', $this->activeComplaintCaseSubquery('property_id', $types, $seriousOnly))
            ->whereNotIn('search_properties.host_user_id', $this->activeComplaintCaseSubquery('host_user_id', $types, $seriousOnly));
    }

    /**
     * @param  list<string>  $types
     * @return Builder<ComplaintCase>
     */
    private function activeComplaintCaseSubquery(string $column, array $types, bool $seriousOnly): Builder
    {
        $subquery = ComplaintCase::query()
            ->select($column)
            ->whereNotNull($column)
            ->whereIn('status', self::ACTIVE_COMPLAINT_CASE_STATUSES);

        if ($types !== []) {
            $subquery->whereIn('complaint_type', $types);
        }

        if ($seriousOnly) {
            $subquery->where(function (Builder $case): void {
                $case->whereIn('severity', self::SERIOUS_COMPLAINT_SEVERITIES)
                    ->orWhereIn('complaint_type', self::SERIOUS_COMPLAINT_TYPES);
            });
        }

        return $subquery;
    }

    /**
     * @param  list<string>  $types
     */
    private function whereNoActiveLegacyComplaints(Builder $query, array $types, bool $seriousOnly): void
    {
        $query
            ->whereNotIn('sleeping_places.id', $this->activeLegacyComplaintSubquery('sleeping_place_id', $types, $seriousOnly))
            ->whereNotIn('search_rooms.id', $this->activeLegacyComplaintSubquery('room_id', $types, $seriousOnly))
            ->whereNotIn('search_properties.id', $this->activeLegacyComplaintSubquery('property_id', $types, $seriousOnly))
            ->whereNotIn('search_properties.host_user_id', $this->activeLegacyComplaintSubquery('reported_user_id', $types, $seriousOnly));
    }

    /**
     * @param  list<string>  $types
     * @return Builder<Complaint>
     */
    private function activeLegacyComplaintSubquery(string $column, array $types, bool $seriousOnly): Builder
    {
        $subquery = Complaint::query()
            ->select($column)
            ->whereNotNull($column)
            ->whereIn('status', self::ACTIVE_COMPLAINT_STATUSES);

        if ($types !== []) {
            $subquery->whereIn('type', $types);
        }

        if ($seriousOnly) {
            $subquery->where(function (Builder $complaint): void {
                $complaint->whereIn('urgency', self::SERIOUS_COMPLAINT_SEVERITIES)
                    ->orWhereIn('priority', self::SERIOUS_COMPLAINT_SEVERITIES)
                    ->orWhereIn('type', self::SERIOUS_COMPLAINT_TYPES);
            });
        }

        return $subquery;
    }

    private function selectedCityId(): ?int
    {
        if (ctype_digit($this->cityId)) {
            return (int) $this->cityId;
        }

        return ctype_digit($this->city) ? (int) $this->city : null;
    }

    private function selectedCountryId(): ?int
    {
        return ctype_digit($this->countryId) ? (int) $this->countryId : null;
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

    private function selectedCheckoutDate(): ?CarbonImmutable
    {
        if ($this->checkOut === '') {
            return null;
        }

        try {
            $checkOut = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        return $checkOut->isBefore(CarbonImmutable::today()) ? null : $checkOut;
    }

    private function selectedMinimumStayNights(): ?int
    {
        return in_array($this->minimumStayNights, ['1', '3', '7', '30'], true) ? (int) $this->minimumStayNights : null;
    }

    private function selectedMaximumStayNights(): ?int
    {
        return in_array($this->maximumStayNights, ['7', '30', '90'], true) ? (int) $this->maximumStayNights : null;
    }

    private function flexibleDateWindowDays(): int
    {
        if ($this->flexiblePlusMinusSevenDays) {
            return 7;
        }

        if ($this->flexiblePlusMinusThreeDays) {
            return 3;
        }

        if ($this->flexiblePlusMinusOneDay) {
            return 1;
        }

        return $this->flexibleDates ? self::FLEXIBLE_DATE_DEFAULT_WINDOW_DAYS : 0;
    }

    /**
     * @return array{0:CarbonImmutable,1:CarbonImmutable}
     */
    private function nextWeekendRange(): array
    {
        $today = CarbonImmutable::today();
        $daysUntilFriday = (5 - (int) $today->dayOfWeek + 7) % 7;
        $checkIn = $today->addDays($daysUntilFriday);

        return [$checkIn, $checkIn->addDays(2)];
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

    private function nonNegativeInteger(string $value): ?int
    {
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return min(1000, (int) $value);
    }

    private function neighborRatingThreshold(): ?float
    {
        if ($this->neighborMinRating === '' || ! is_numeric($this->neighborMinRating)) {
            return null;
        }

        $rating = (float) $this->neighborMinRating;

        if ($rating < 0.0 || $rating > 5.0) {
            return null;
        }

        return $rating;
    }

    /**
     * @return list<string>|null
     */
    private function allowedCompatibilityStatuses(): ?array
    {
        $minimum = $this->minimumCompatibilityFit;

        if ($minimum !== '' && ! array_key_exists($minimum, self::FIT_STATUS_RANKS)) {
            return null;
        }

        if ($minimum === '' && ! $this->hideNotSuitableCompatibility) {
            return null;
        }

        $minimumRank = $minimum !== '' ? self::FIT_STATUS_RANKS[$minimum] : self::FIT_STATUS_RANKS['uncomfortable'];

        return collect(self::FIT_STATUS_RANKS)
            ->filter(fn (int $rank): bool => $rank >= $minimumRank)
            ->keys()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function filterPropertyNames(): array
    {
        return [
            'city',
            'countryId',
            'cityId',
            'cityQuery',
            'district',
            'street',
            'landmark',
            'checkIn',
            'checkOut',
            'guestsCount',
            'priceMin',
            'priceMax',
            'currency',
            'priceBasis',
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
            'neighborRoommatesMax',
            'propertyResidentsMax',
            'neighborAgeRange',
            'neighborLifestyle',
            'neighborLanguage',
            'neighborMinRating',
            ...array_keys(self::NEIGHBOR_BOOLEAN_FILTERS),
            ...array_keys(self::SAFETY_BOOLEAN_FILTERS),
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
            'nearPark',
            'nearShoppingCenter',
            'nearGym',
            'nearCoworking',
            'nearWork',
            'nearSea',
            'nearNightlife',
            'nearAirport',
            'easyTransport',
            'quietDistrict',
            'safeDistrict',
            'areaResidential',
            'areaCityCenter',
            'areaSuburb',
            'areaIndustrial',
            'areaTourist',
            'areaStudents',
            'areaWorkers',
            'areaLongStay',
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
            'withDeposit',
            'lowDeposit',
            'noCleaningFee',
            'freeCancellation',
            'partialRefund',
            'fullRefund',
            'installmentPayment',
            'payLater',
            'payOnArrival',
            'hasDiscount',
            'hasPromoCode',
            'belowAveragePrice',
            'priceIncludesAllFees',
            'showTotalPriceImmediately',
            'hideHiddenFees',
            'longStayAllowed',
            'availableToday',
            'availableTomorrow',
            'availableWeekend',
            'flexibleDates',
            'flexiblePlusMinusOneDay',
            'flexiblePlusMinusThreeDays',
            'flexiblePlusMinusSevenDays',
            'shortStayAllowed',
            'minimumStayNights',
            'maximumStayNights',
            'canExtendStay',
            'cannotExtendStay',
            'availableAfterCheckout',
            'nightCheckIn',
            'nightCheckOut',
            'earlyMorningCheckIn',
            'lateEveningCheckOut',
            'minimumCompatibilityFit',
            'hideNotSuitableCompatibility',
            'showCompatibilityWarnings',
            'sort',
        ];
    }

    private function normalizeLocationQueryState(): void
    {
        if ($this->cityId === '' && ctype_digit($this->city)) {
            $this->cityId = $this->city;
        }

        if ($this->city === '' && $this->cityId !== '') {
            $this->city = $this->cityId;
        }

        $legacyBooleanAliases = [
            'near_bus' => 'nearBusStop',
            'near_railway' => 'nearRailwayStation',
            'quiet_district' => 'quietDistrict',
            'safe_district' => 'safeDistrict',
        ];

        foreach ($legacyBooleanAliases as $queryKey => $property) {
            if (! $this->{$property} && request()->boolean($queryKey)) {
                $this->{$property} = true;
            }
        }
    }
}
