<?php

namespace App\Livewire\Places;

use App\Data\Favorites\FavoriteContext;
use App\Data\Occupants\DateRange as OccupantDateRange;
use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\MessageThreadType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Favorites\FavoriteService;
use App\Services\Listings\ListingDetailContentService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\Localization\SupportedContentLocales;
use App\Services\Messaging\MessageService;
use App\Services\Occupants\RoomOccupantSummaryService;
use App\Services\Pricing\PricingService;
use App\Services\Privacy\ListingAddressVisibilityService;
use App\Services\Properties\PropertyGuestSummaryService;
use App\Services\Rooms\RoomGuestSummaryService;
use App\Services\SleepingPlaces\SleepingPlaceGuestSummaryService;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowSleepingPlace extends Component
{
    #[Locked]
    public int $sleepingPlaceId;

    #[Url(as: 'in', except: '')]
    public string $checkIn = '';

    #[Url(as: 'out', except: '')]
    public string $checkOut = '';

    #[Url(as: 'guests', except: 1)]
    public int $guestsCount = 1;

    public bool $isFavorited = false;

    public bool $contactOpen = false;

    public string $messageBody = '';

    public function mount(SleepingPlace $sleepingPlace): void
    {
        $this->sleepingPlaceId = $sleepingPlace->id;

        abort_unless(
            $sleepingPlace->status === SleepingPlaceStatus::Active
            && $sleepingPlace->room?->status === RoomStatus::Active
            && $sleepingPlace->property?->status === PropertyStatus::Active,
            404,
        );

        $user = auth()->user();
        $this->isFavorited = $user instanceof User && $user->hasFavoritedSleepingPlace($sleepingPlace);
        $this->refreshQuote();
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
        unset($this->place);
    }

    /**
     * @return array{quote:array<string,mixed>|null,availabilityWarning:string|null,unavailableDates:list<string>}
     */
    #[Computed]
    public function dateEvaluation(): array
    {
        $empty = $this->emptyDateEvaluation();

        if ($this->checkIn === '' || $this->checkOut === '') {
            return $empty;
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->startOfDay();
            $checkOut = CarbonImmutable::parse($this->checkOut)->startOfDay();
        } catch (\Throwable) {
            return [
                ...$empty,
                'availabilityWarning' => __('listing.detail.booking.use_valid_dates'),
            ];
        }

        if ($checkIn->isBefore(CarbonImmutable::today())) {
            return [
                ...$empty,
                'availabilityWarning' => __('listing.detail.booking.past_dates'),
            ];
        }

        if ($checkOut->lessThanOrEqualTo($checkIn)) {
            return [
                ...$empty,
                'availabilityWarning' => __('listing.detail.booking.checkout_after_checkin'),
            ];
        }

        $place = $this->place;
        $nights = (int) $checkIn->diffInDays($checkOut);
        $guestsCount = max(1, $this->guestsCount);

        if ($guestsCount > $place->max_guests) {
            return [
                ...$empty,
                'availabilityWarning' => trans_choice('listing.detail.booking.max_guests', $place->max_guests, [
                    'count' => $place->max_guests,
                ]),
            ];
        }

        if ($place->min_nights && $nights < $place->min_nights) {
            return [
                ...$empty,
                'availabilityWarning' => trans_choice('listing.detail.booking.min_nights', (int) $place->min_nights, [
                    'count' => (int) $place->min_nights,
                ]),
            ];
        }

        if ($place->max_nights && $nights > $place->max_nights) {
            return [
                ...$empty,
                'availabilityWarning' => trans_choice('listing.detail.booking.max_nights', (int) $place->max_nights, [
                    'count' => (int) $place->max_nights,
                ]),
            ];
        }

        $availability = app(AvailabilityService::class);

        if (! $availability->isAvailable($place, $checkIn, $checkOut, usePrefetchedAvailabilityDays: true)) {
            return [
                'quote' => null,
                'availabilityWarning' => __('listing.detail.booking.unavailable_title'),
                'unavailableDates' => $availability->unavailableDates($place, $checkIn, $checkOut),
            ];
        }

        $guest = auth()->user();
        $guest = $guest instanceof User ? $guest : new User;

        return [
            'quote' => app(PricingService::class)
                ->calculate($guest, $place, $checkIn, $checkOut, $guestsCount)
                ->toArray(),
            'availabilityWarning' => null,
            'unavailableDates' => [],
        ];
    }

    /**
     * @return array{quote:null,availabilityWarning:null,unavailableDates:list<string>}
     */
    private function emptyDateEvaluation(): array
    {
        return [
            'quote' => null,
            'availabilityWarning' => null,
            'unavailableDates' => [],
        ];
    }

    /**
     * @return array{0:CarbonImmutable,1:CarbonImmutable}|null
     */
    private function validatedPrefetchDateRange(): ?array
    {
        if ($this->checkIn === '') {
            return null;
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        $checkOut = $checkIn->addDays(14);

        if ($this->checkOut !== '') {
            try {
                $selectedCheckOut = CarbonImmutable::parse($this->checkOut)->startOfDay();

                if ($selectedCheckOut->greaterThan($checkIn)) {
                    $checkOut = $selectedCheckOut->max($checkOut);
                }
            } catch (\Throwable) {
                return [$checkIn, $checkOut];
            }
        }

        return [$checkIn, $checkOut];
    }

    public function toggleFavorite(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $result = app(FavoriteService::class)->toggle(
            user: $user,
            sleepingPlaceId: $this->sleepingPlaceId,
            context: new FavoriteContext(
                source: 'listing_detail',
                checkIn: $this->checkIn ?: null,
                checkOut: $this->checkOut ?: null,
                guestsCount: max(1, $this->guestsCount),
                notifyPriceDrop: true,
                notifyAvailableAgain: true,
                notifyUnavailable: true,
            ),
        );

        $this->isFavorited = $result->selected;
    }

    public function startRequest(): void
    {
        $this->openContact();

        if ($this->contactOpen && $this->messageBody === '') {
            $this->messageBody = __('listing.detail.contact.default_message', [
                'title' => $this->title($this->place),
            ]);
        }
    }

    public function openContact(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $this->contactOpen = true;
    }

    public function closeContact(): void
    {
        $this->contactOpen = false;
        $this->resetValidation('messageBody');
    }

    public function sendMessage(): void
    {
        $validated = $this->validate([
            'messageBody' => ['required', 'string', 'min:2', 'max:1000'],
        ], attributes: [
            'messageBody' => __('listing.detail.contact.message_label'),
        ]);

        $guest = auth()->user();

        if (! $guest instanceof User) {
            $this->redirect(route('auth.login', ['locale' => app()->getLocale()]), navigate: true);

            return;
        }

        $place = $this->place;
        $host = $place->property?->host;

        if (! $host instanceof User || $host->id === $guest->id) {
            $this->addError('messageBody', __('listing.detail.contact.unavailable'));

            return;
        }

        $thread = app(MessageService::class)->getOrCreateThread(
            guest: $guest,
            host: $host,
            type: MessageThreadType::PreBooking,
            property: $place->property,
            sleepingPlace: $place,
        );

        app(MessageService::class)->send($thread, $guest, $validated['messageBody']);

        $this->messageBody = '';
        $this->contactOpen = false;
        session()->flash('listing-contact-status', __('listing.detail.contact.sent'));
    }

    public function render(): View
    {
        $place = $this->place;
        $title = $this->title($place);
        $gallery = $this->gallery();
        $dateEvaluation = $this->dateEvaluation;
        $quote = $dateEvaluation['quote'];
        $nearbySummary = $this->nearbySummary($place);

        return view('livewire.places.show-sleeping-place', [
            'place' => $place,
            'title' => $title,
            'pageOverview' => $this->pageOverview($place, $quote, $nearbySummary),
            'summary' => $this->summary($place),
            'gallery' => $gallery,
            'primaryImage' => $gallery[0] ?? null,
            'secondaryImages' => array_slice($gallery, 1),
            'decisionFlow' => $this->decisionFlow($place, $quote),
            'priceBreakdown' => $this->priceBreakdown($place, $quote),
            'availabilityWarning' => $dateEvaluation['availabilityWarning'],
            'unavailableDates' => $dateEvaluation['unavailableDates'],
            'extendedContent' => app(ListingDetailContentService::class)->forSleepingPlace($place, auth()->user()),
            'exactFeatures' => $this->exactFeatures($place),
            'sleepingPlaceProfile' => app(SleepingPlaceGuestSummaryService::class)->build($place, auth()->user()),
            'roomDetails' => $this->roomDetails($place, $nearbySummary['count']),
            'propertyDetails' => $this->propertyDetails($place),
            'amenityGroups' => $this->amenityGroups($place),
            'nearbySummary' => $nearbySummary,
            'rulesByGroup' => $this->rulesByGroup($place),
            'calendarPreview' => $this->calendarPreview($place, $quote),
            'mapDetails' => $this->mapDetails($place),
            'safetyDetails' => $this->safetyDetails($place),
            'cancellationDetails' => $this->cancellationDetails($place, $quote),
            'faqItems' => $this->faqItems($place),
        ])->layout('layouts.app', ['title' => $title]);
    }

    #[Computed]
    public function place(): SleepingPlace
    {
        $locales = $this->translationLocales();
        $translationScope = fn ($query) => $query->whereIn('locale', $locales);
        $amenityTranslationScope = fn ($query) => $query
            ->select(['id', 'amenity_id', 'locale', 'name'])
            ->whereIn('locale', $locales);
        $ruleTranslationScope = fn ($query) => $query
            ->select(['id', 'rule_id', 'locale', 'name'])
            ->whereIn('locale', $locales);
        $dateRange = $this->validatedPrefetchDateRange();

        return SleepingPlace::query()
            ->select([
                'id',
                'room_id',
                'property_id',
                'type',
                'sleeping_place_type',
                'sleeping_place_subtype',
                'status',
                'place_number',
                'display_name',
                'internal_name',
                'bunk_level',
                'is_top_bunk',
                'is_bottom_bunk',
                'is_single',
                'is_double',
                'is_for_one_person',
                'is_for_couple',
                'length_cm',
                'width_cm',
                'height_cm',
                'mattress_type',
                'mattress_firmness',
                'has_pillow',
                'has_blanket',
                'has_bedding',
                'has_towel',
                'has_curtain',
                'has_lamp',
                'has_power_socket',
                'has_usb',
                'has_shelf',
                'has_hook',
                'has_locker',
                'locker_has_lock',
                'has_luggage_space',
                'privacy_level',
                'noise_level',
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
                'instant_booking_enabled',
                'requires_host_approval',
                'extensions_allowed',
                'can_extend',
                'early_check_in_allowed',
                'late_check_out_allowed',
                'second_guest_allowed',
                'second_guest_fee',
                'cancellation_policy',
            ])
            ->withCount([
                'reviews as published_reviews_count' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ])
            ->withAvg([
                'reviews as published_reviews_rating' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ], 'overall_rating')
            ->with([
                'translations' => fn ($query) => $query
                    ->select([
                        'id',
                        'sleeping_place_id',
                        'locale',
                        'title',
                        'short_description',
                        'full_description',
                        'summary',
                        'description',
                        'special_conditions',
                        'main_pros',
                        'important_cons',
                        'special_notes',
                        'what_is_included',
                        'what_guest_should_bring',
                        'storage_instructions',
                        'safety_notes',
                        'sleeping_place_title',
                        'sleeping_place_description',
                        'sleeping_place_pros',
                        'sleeping_place_cons',
                        'sleeping_place_special_notes',
                        'what_is_included_for_place',
                        'what_guest_should_bring_for_place',
                    ])
                    ->whereIn('locale', $locales),
                'physicalDetails:id,sleeping_place_id,length_cm,width_cm,height_cm,height_from_floor_cm,clearance_above_cm,ladder_available,ladder_comfort_level,safety_rail_available,safety_rail_height_cm,max_weight_kg,suitable_for_tall_person,suitable_for_heavy_person,suitable_for_elderly,suitable_for_limited_mobility,not_suitable_for_limited_mobility,frame_material,frame_stability_level,squeak_level',
                'comfortDetails:id,sleeping_place_id,mattress_type,mattress_firmness,mattress_thickness_cm,mattress_condition,mattress_newness,has_mattress_protector,mattress_has_stains,mattress_has_smell,mattress_sags,has_pillow,has_blanket,has_bedding,bedding_included,bedding_changed_before_guest,has_towel,towel_included',
                'storageDetails:id,sleeping_place_id,has_shoe_space,has_luggage_space,has_backpack_space,has_personal_locker,locker_has_lock,lock_provided,guest_should_bring_lock,can_store_valuables,can_store_documents,can_store_laptop,locker_size,can_leave_luggage_before_checkin,can_leave_luggage_after_checkout,storage_responsibility_note',
                'positionDetails:id,sleeping_place_id,privacy_level,has_curtain,has_personal_lamp,lamp_adjustable,has_power_socket,power_sockets_count,has_usb_charger,has_usb_c_charger,has_shelf,has_hook,near_door,near_window,near_radiator,near_air_conditioner,near_power_socket,near_passage,in_room_corner,near_wall,noise_level_near_place,light_level_near_place,morning_light,draft_nearby',
                'conditionDetails:id,sleeping_place_id,condition_state,frame_condition,mattress_condition,bedding_condition,curtain_condition,lamp_condition,socket_condition,locker_condition,has_damage,has_stains,has_smell,squeaks,needs_repair,needs_mattress_replacement,needs_bedding_replacement,last_cleaned_at,last_bedding_changed_at,last_checked_at,host_condition_note',
                'amenities' => fn ($query) => $query->select(['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status']),
                'amenities.translations' => $amenityTranslationScope,
                'rules' => fn ($query) => $query->select(['rules.id', 'rules.slug', 'rules.category', 'rules.status']),
                'rules.translations' => $ruleTranslationScope,
                'calendarSettings:id,sleeping_place_id,active,booking_mode,request_only,default_status,same_day_turnover_allowed,default_check_in_time,default_check_out_time,earliest_check_in_time,latest_check_out_time,check_in_time_from,check_in_time_until,check_out_time_until',
                'turnoverRules:id,sleeping_place_id,min_gap_minutes,cleaning_required_between_guests,cleaning_gap_minutes,inspection_required_after_checkout,inspection_gap_minutes,same_day_turnover_allowed,morning_checkout_evening_checkin_allowed,same_day_turnover_requires_cleaning_done,same_day_turnover_requires_inspection_done,earliest_new_check_in_time,latest_previous_check_out_time',
                'room' => fn ($query) => $query
                    ->select([
                        'id',
                        'property_id',
                        'type',
                        'room_type',
                        'living_format',
                        'status',
                        'title',
                        'gender_policy',
                        'gender_type',
                        'room_number',
                        'is_private',
                        'is_shared',
                        'is_pass_through',
                        'area',
                        'beds_count',
                        'sleeping_places_count',
                        'active_sleeping_places_count',
                        'max_guests',
                        'current_guests_count',
                        'permanent_residents_count',
                        'short_term_guests_count',
                        'occupied_places_count',
                        'available_places_count',
                        'occupied_sleeping_places_count',
                        'free_sleeping_places_count',
                        'unavailable_sleeping_places_count',
                        'noise_level',
                        'light_level',
                        'has_window',
                        'has_lock',
                        'has_wardrobe',
                        'has_desk',
                        'has_chair',
                        'has_mirror',
                        'has_heating',
                        'has_air_conditioning',
                        'has_balcony',
                    ])
                    ->withCount(['sleepingPlaces as active_sleeping_places_count' => fn (Builder $places) => $places->active()])
                    ->with([
                        'translations' => fn ($translation) => $translation
                            ->select([
                                'id',
                                'room_id',
                                'locale',
                                'title',
                                'short_description',
                                'full_description',
                                'summary',
                                'description',
                                'notes',
                                'room_description',
                                'room_rules_text',
                                'room_pros',
                                'room_cons',
                                'who_lives_nearby_text',
                                'quiet_hours_text',
                                'storage_instructions',
                                'work_study_instructions',
                                'food_rules_text',
                                'conflict_instructions',
                                'special_notes',
                                'shared_space_instructions',
                            ])
                            ->whereIn('locale', $locales),
                        'layoutDetails:id,room_id,area,windows_count,window_view,cardinal_direction,has_balcony,has_free_passage_space,narrow_passages',
                        'comfortDetails:id,room_id,has_heating,has_air_conditioning,has_fan,ventilation_level,can_open_window,can_close_window,light_level,has_blackout_curtains,can_turn_light_at_night,can_use_personal_lamp_at_night,noise_level,soundproofing_level,quiet_hours_enabled,quiet_hours_start,quiet_hours_end,has_draft,has_damp_smell,has_tobacco_smell',
                        'accessDetails:id,room_id,has_door,has_lock,has_key,privacy_level,has_wardrobe,has_personal_lockers,personal_lockers_count,lockers_have_locks,has_luggage_space,has_desk,has_chairs,has_mirror,can_store_food,food_storage_allowed_type',
                        'conditionDetails:id,room_id,condition_state,repair_state,cleanliness_level,floor_condition,walls_condition,window_condition,door_condition,furniture_condition,has_mold,has_insects,has_bad_smell,has_damp_marks,needs_repair,last_cleaned_at,last_checked_at',
                        'amenities' => fn ($amenity) => $amenity->select(['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status']),
                        'amenities.translations' => $amenityTranslationScope,
                        'rules' => fn ($rule) => $rule->select(['rules.id', 'rules.slug', 'rules.category', 'rules.status']),
                        'rules.translations' => $ruleTranslationScope,
                    ]),
                'property' => fn ($query) => $query
                    ->select([
                        'id',
                        'host_user_id',
                        'city_id',
                        'type',
                        'property_type',
                        'property_subtype',
                        'status',
                        'title',
                        'city',
                        'district',
                        'street',
                        'building',
                        'apartment',
                        'floor',
                        'nearest_transport',
                        'address_line_1',
                        'address_line_2',
                        'house_number',
                        'apartment_number',
                        'access_instructions',
                        'show_exact_address_before_booking',
                        'show_exact_address_after_confirmation',
                        'show_exact_address_after_payment',
                        'show_only_approximate_location',
                        'distance_to_center_meters',
                        'rooms_count',
                        'living_area',
                        'max_residents',
                        'current_residents_count',
                        'active_sleeping_places_count',
                        'free_sleeping_places_count',
                        'occupied_sleeping_places_count',
                        'bathrooms_count',
                        'showers_count',
                        'kitchens_count',
                        'has_elevator',
                        'has_parking',
                        'has_security',
                        'has_cctv_common_areas',
                        'has_hot_water',
                        'safety_level',
                        'cleanliness_level',
                    ])
                    ->with([
                        'translations' => fn ($translation) => $translation
                            ->select([
                                'id',
                                'property_id',
                                'locale',
                                'title',
                                'short_description',
                                'summary',
                                'full_description',
                                'description',
                                'location_description',
                                'transport_description',
                                'neighborhood_description',
                                'parking_description',
                                'condition_description',
                                'access_description',
                                'self_check_in_instructions',
                                'delivery_instructions',
                                'guest_visitor_rules_text',
                                'courier_rules_text',
                                'important_notes',
                                'getting_there',
                                'why_convenient',
                                'suitable_for',
                                'not_suitable_for',
                                'main_pros',
                                'important_cons',
                                'what_to_know_beforehand',
                                'what_is_included',
                                'what_is_not_included',
                                'what_to_bring',
                                'where_to_store_belongings',
                                'where_to_store_food',
                                'kitchen_instructions',
                                'bathroom_instructions',
                                'laundry_instructions',
                                'key_pickup_instructions',
                                'night_entry_instructions',
                                'host_contact_instructions',
                                'problem_instructions',
                                'lost_key_instructions',
                                'neighbor_conflict_instructions',
                                'repair_problem_instructions',
                                'what_to_know',
                                'check_in_instructions',
                                'house_rules_text',
                                'safety_notes',
                            ])
                            ->whereIn('locale', $locales),
                        'cityModel:id,name',
                        'host:id,name,avatar,languages,rating_as_host,identity_verified',
                        'host.setting:id,user_id,privacy_preferences_json',
                        'host.hostProfile:id,user_id,display_name,avatar_path,about,languages_json,response_time_minutes,response_rate,rating_average,reviews_count,verified_at,default_cancellation_policy,can_help_with_check_in,lives_nearby,lives_in_property,emergency_contact_available',
                        'locationDetails:id,property_id,nearest_metro,nearest_bus_stop,nearest_shop,nearest_supermarket,nearest_pharmacy,nearest_railway_station,nearest_airport,distance_to_center_meters,walk_minutes_to_center,transport_minutes_to_center,transport_convenience_level,has_night_transport,easy_to_reach_with_luggage,district_noise_level,district_safety_level,street_lighting_level,has_parking_nearby,has_free_parking,has_paid_parking,has_private_parking,has_bicycle_parking,parking_permit_required,parking_usually_full',
                        'conditionDetails:id,property_id,repair_state,cleanliness_level,smell_level,humidity_level,winter_temperature_level,summer_temperature_level,indoor_noise_level,light_level,furniture_condition,plumbing_condition,kitchen_condition,bathroom_condition,has_insects,has_mold,has_heating_problems,has_hot_water_problems,has_damp_marks,last_checked_at',
                        'accessDetails:id,property_id,entrance_type,has_intercom,has_key,has_keycard,has_electronic_lock,has_key_safe,self_check_in_available,meet_host_required,meet_host_representative_required,access_24_7,can_return_at_night,has_night_entry_restrictions,delivery_allowed,delivery_dropoff_location',
                        'amenities' => fn ($amenity) => $amenity->select(['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status']),
                        'amenities.translations' => $amenityTranslationScope,
                        'rules' => fn ($rule) => $rule->select(['rules.id', 'rules.slug', 'rules.category', 'rules.status']),
                        'rules.translations' => $ruleTranslationScope,
                    ]),
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
     * @param  array<string, mixed>|null  $quote
     * @param  array{count:int,summary:string,privacy:string,privacy_note:string,badges:list<string>,messages:list<string>,warnings:list<array<string,mixed>>}  $nearbySummary
     * @return array{description:string,facts:list<array{label:string,value:string}>,sections:list<array{key:string,label:string,href:string}>}
     */
    private function pageOverview(SleepingPlace $place, ?array $quote, array $nearbySummary): array
    {
        return [
            'description' => $this->shortDescription($place),
            'facts' => [
                $this->row('listing.detail.overview.facts.location', $this->location($place->property)),
                $this->row('listing.detail.overview.facts.dates', $this->staySummary()),
                $this->row('listing.detail.overview.facts.price', $this->priceSummary($place, $quote)),
                $this->row('listing.detail.overview.facts.nearby', trans_choice('listing.detail.nearby.count', $nearbySummary['count'], [
                    'count' => $nearbySummary['count'],
                ])),
                $this->row('listing.detail.overview.facts.booking', $this->bookingModeLabel($place)),
                $this->row('listing.detail.overview.facts.cancellation', $this->cancellationPolicyLabel((string) ($place->property?->host?->hostProfile?->default_cancellation_policy ?: 'flexible'))),
            ],
            'sections' => $this->detailSectionIndex(),
        ];
    }

    private function shortDescription(SleepingPlace $place): string
    {
        $placeTranslation = $this->translation($place->translations);
        $roomTranslation = $this->translation($place->room?->translations ?? collect());
        $propertyTranslation = $this->translation($place->property?->translations ?? collect());

        return $this->firstText([
            $placeTranslation?->short_description,
            $placeTranslation?->summary,
            $placeTranslation?->description,
            $roomTranslation?->summary,
            $propertyTranslation?->short_description,
            $propertyTranslation?->summary,
        ]) ?? __('listing.detail.overview.description_missing');
    }

    /**
     * @return list<array{key:string,label:string,href:string}>
     */
    private function detailSectionIndex(): array
    {
        return collect([
            'photos' => '#place-photos',
            'booking' => '#booking-panel',
            'sleeping_place' => '#sleeping-place-details',
            'room' => '#room-details',
            'nearby' => '#nearby-occupants',
            'property' => '#property-details',
            'amenities' => '#amenities',
            'rules' => '#rules',
            'calendar' => '#calendar',
            'map' => '#neighborhood-map',
            'host' => '#host-info',
            'reviews' => '#reviews',
            'safety' => '#safety',
            'cancellation' => '#cancellation',
            'similar' => '#similar',
        ])
            ->map(fn (string $href, string $key): array => [
                'key' => $key,
                'label' => __('listing.detail.overview.sections.'.$key),
                'href' => $href,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{url:string,thumb_url:string,alt:string,is_primary:bool}>
     */
    private function gallery(): array
    {
        $place = $this->place;
        $targets = [
            [SleepingPlace::class, $place->id],
            [Room::class, $place->room_id],
            [Property::class, $place->property_id],
        ];

        return MediaItem::query()
            ->select(['id', 'mediable_type', 'mediable_id', 'disk', 'path', 'thumb_path', 'thumbnail_path', 'mobile_path', 'full_path', 'alt_text', 'sort_order', 'is_primary', 'is_cover', 'status'])
            ->with(['translations' => fn ($translation) => $translation
                ->select(['id', 'media_item_id', 'locale', 'caption'])
                ->whereIn('locale', $this->translationLocales())])
            ->active()
            ->where(function (Builder $query) use ($targets): void {
                foreach ($targets as [$type, $id]) {
                    $query->orWhere(function (Builder $target) use ($type, $id): void {
                        $target->where('mediable_type', $type)->where('mediable_id', $id);
                    });
                }
            })
            ->orderByDesc('is_primary')
            ->orderByDesc('is_cover')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(6)
            ->get()
            ->map(fn (MediaItem $media): array => [
                'url' => $media->imageUrl('mobile'),
                'thumb_url' => $media->imageUrl('thumb'),
                'alt' => $media->localizedCaption() ?: __('listing.media.primary_alt', ['title' => $this->title($place)]),
                'is_primary' => (bool) ($media->is_primary || $media->is_cover),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(SleepingPlace $place): array
    {
        $property = $place->property;
        $hostProfile = $property?->host?->hostProfile;
        $rating = $place->published_reviews_rating ?: $hostProfile?->rating_average;
        $reviewsCount = (int) ($place->published_reviews_count ?: $hostProfile?->reviews_count ?: 0);

        return [
            'property_type' => $this->label($property?->type),
            'room_type' => $this->label($place->room?->type),
            'sleeping_place_type' => $this->label($place->type),
            'location' => $this->location($property),
            'rating' => $rating ? number_format((float) $rating, 1) : null,
            'reviews_count' => $reviewsCount,
        ];
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function decisionFlow(SleepingPlace $place, ?array $quote): array
    {
        return [
            $this->row('listing.detail.flow.property', $this->label($place->property?->type)),
            $this->row('listing.detail.flow.room', $this->label($place->room?->type)),
            $this->row('listing.detail.flow.place', $this->label($place->type)),
            $this->row('listing.detail.flow.dates', $this->dateRangeSummary()),
            $this->row('listing.detail.flow.price', $this->priceSummary($place, $quote)),
            $this->row('listing.detail.flow.booking', $this->bookingModeLabel($place)),
        ];
    }

    /**
     * @return array{
     *     has_quote:bool,
     *     summary:string,
     *     date_prices:list<array{label:string,weekday:string,amount:string,source:string}>,
     *     remaining_dates_count:int,
     *     lines:list<array{label:string,amount:string,refundable:bool,type:string}>,
     *     total:?string,
     *     refundable:?string,
     *     non_refundable:?string
     * }
     */
    private function priceBreakdown(SleepingPlace $place, ?array $quote): array
    {
        $currency = strtoupper((string) (($quote['currency'] ?? null) ?: ($place->currency ?: 'EUR')));

        if (! $quote) {
            $lines = [
                [
                    'label' => __('listing.detail.booking.base_nightly_amount'),
                    'amount' => $this->money((float) $place->base_price_per_night, $currency),
                    'refundable' => false,
                    'type' => 'base_nightly_amount',
                ],
            ];

            if ((float) $place->cleaning_fee > 0) {
                $lines[] = [
                    'label' => __('booking.price_lines.cleaning_fee'),
                    'amount' => $this->money((float) $place->cleaning_fee, $currency),
                    'refundable' => false,
                    'type' => 'cleaning_fee',
                ];
            }

            if ((float) $place->deposit_amount > 0) {
                $lines[] = [
                    'label' => __('booking.price_lines.deposit'),
                    'amount' => $this->money((float) $place->deposit_amount, $currency),
                    'refundable' => true,
                    'type' => 'deposit',
                ];
            }

            return [
                'has_quote' => false,
                'summary' => __('listing.detail.booking.price_from', [
                    'amount' => $this->money((float) $place->base_price_per_night, $currency),
                ]),
                'date_prices' => [],
                'remaining_dates_count' => 0,
                'lines' => $lines,
                'total' => null,
                'refundable' => null,
                'non_refundable' => null,
            ];
        }

        $allDatePrices = collect($quote['date_prices'] ?? []);
        $datePrices = $allDatePrices
            ->take(7)
            ->map(function (array $datePrice) use ($currency): array {
                $date = CarbonImmutable::parse((string) $datePrice['date']);
                $source = (string) ($datePrice['source'] ?? 'base');

                return [
                    'label' => $date->translatedFormat('d M'),
                    'weekday' => $date->translatedFormat('D'),
                    'amount' => $this->money((float) $datePrice['price'], $currency),
                    'source' => __('listing.detail.booking.price_sources.'.$source),
                ];
            })
            ->values()
            ->all();

        $hasDailyPrices = $datePrices !== [];
        $lines = collect($quote['line_items'] ?? [])
            ->reject(fn (array $item): bool => ($item['type'] ?? '') === 'total'
                || ($hasDailyPrices && ($item['type'] ?? '') === 'nightly_base'))
            ->map(function (array $item) use ($currency): array {
                $type = (string) ($item['type'] ?? 'line');

                return [
                    'label' => __((string) ($item['label_key'] ?? 'booking.price_lines.'.$type)),
                    'amount' => $this->money((float) ($item['amount'] ?? 0), (string) ($item['currency'] ?? $currency)),
                    'refundable' => (bool) ($item['is_refundable'] ?? false),
                    'type' => $type,
                ];
            })
            ->values()
            ->all();

        return [
            'has_quote' => true,
            'summary' => trans_choice('listing.detail.booking.price_summary', (int) $quote['nights_count'], [
                'count' => (int) $quote['nights_count'],
                'total' => $this->money((float) $quote['total_amount'], $currency),
            ]),
            'date_prices' => $datePrices,
            'remaining_dates_count' => max(0, $allDatePrices->count() - count($datePrices)),
            'lines' => $lines,
            'total' => $this->money((float) $quote['total_amount'], $currency),
            'refundable' => $this->money((float) $quote['refundable_amount'], $currency),
            'non_refundable' => $this->money((float) $quote['non_refundable_amount'], $currency),
        ];
    }

    /**
     * @return list<array{label:string,value:string}>
     */
    private function exactFeatures(SleepingPlace $place): array
    {
        return array_values(array_filter([
            $this->row('listing.detail.exact.bed_type', $this->label($place->type)),
            $this->row('listing.detail.exact.size', __('listing.detail.values.size_cm', [
                'length' => $place->length_cm ?: '—',
                'width' => $place->width_cm ?: '—',
            ])),
            $this->row('listing.detail.exact.bunk_level', $this->valueLabel($place->bunk_level)),
            $this->row('listing.detail.exact.mattress', $this->compoundValue([$place->mattress_type, $place->mattress_firmness])),
            $this->row('listing.detail.exact.bedding_towel', $this->yesNoList([
                'listing.detail.exact.bedding' => $place->has_bedding,
                'listing.detail.exact.towel' => $place->has_towel,
            ])),
            $this->row('listing.detail.exact.power_lamp_locker', $this->yesNoList([
                'listing.detail.exact.socket' => $place->has_power_socket,
                'listing.detail.exact.lamp' => $place->has_lamp,
                'listing.detail.exact.locker' => $place->has_locker,
            ])),
            $this->row('listing.detail.exact.privacy_level', $this->valueLabel($place->privacy_level)),
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    private function roomDetails(SleepingPlace $place, ?int $peopleOnDates = null): array
    {
        $room = $place->room;

        return [
            'people_on_dates' => $peopleOnDates ?? $this->nearbyGuestCount($place),
            'total_places' => (int) ($room?->active_sleeping_places_count ?: $room?->beds_count ?: 0),
            'occupied_places' => (int) ($room?->occupied_places_count ?: 0),
            'gender_policy' => $this->label($room?->gender_policy ?: $room?->gender_type),
            'quiet_rules' => $this->ruleLabelsByCategories($place, ['quiet_hours', 'shared_room_behavior']),
            'amenities' => $this->amenityLabels($room?->amenities ?? collect()),
            'profile' => $room
                ? app(RoomGuestSummaryService::class)->build($room, auth()->user() instanceof User ? auth()->user() : null)
                : ['title' => __('room.public.title'), 'badges' => [], 'occupancy' => ['count' => 0, 'summary' => ''], 'sections' => []],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyDetails(SleepingPlace $place): array
    {
        $property = $place->property;
        $translation = $this->translation($property?->translations ?? collect());
        $addressVisibility = $this->addressVisibility($property);
        $viewer = auth()->user();

        return [
            'description' => $translation?->description ?: $translation?->summary ?: __('listing.detail.property.no_description'),
            'address' => $addressVisibility['address'],
            'address_note' => $addressVisibility['note'],
            'check_in_instructions' => $addressVisibility['instructions'],
            'profile' => app(PropertyGuestSummaryService::class)->build($property, $viewer instanceof User ? $viewer : null),
            'transport' => $property?->nearest_transport ?: $translation?->getting_there ?: __('listing.detail.property.transport_missing'),
            'kitchen_bathroom' => __('listing.detail.property.kitchen_bathroom_summary', [
                'kitchens' => (int) ($property?->kitchens_count ?? 0),
                'bathrooms' => (int) ($property?->bathrooms_count ?? 0),
                'showers' => (int) ($property?->showers_count ?? 0),
            ]),
            'safety' => $this->safetySummary($property, $translation?->safety_notes),
        ];
    }

    /**
     * @return list<array{title:string,items:list<string>}>
     */
    private function amenityGroups(SleepingPlace $place): array
    {
        return [
            [
                'title' => __('listing.detail.amenities.place'),
                'items' => $this->amenityLabels($place->amenities),
            ],
            [
                'title' => __('listing.detail.amenities.room'),
                'items' => $this->amenityLabels($place->room?->amenities ?? collect()),
            ],
            [
                'title' => __('listing.detail.amenities.property'),
                'items' => $this->amenityLabels($place->property?->amenities ?? collect()),
            ],
        ];
    }

    /**
     * @return array{count:int,summary:string,privacy:string,privacy_note:string,badges:list<string>,messages:list<string>,warnings:list<array<string,mixed>>}
     */
    private function nearbySummary(SleepingPlace $place): array
    {
        $fallbackCount = $this->nearbyGuestCount($place);
        $room = $place->room;
        $quiet = $room?->noise_level ? $this->valueLabel($room->noise_level) : __('listing.detail.values.not_set');
        $summary = null;

        if ($room && $this->checkIn !== '' && $this->checkOut !== '') {
            try {
                $summary = app(RoomOccupantSummaryService::class)
                    ->getSummaryForSleepingPlace($place, new OccupantDateRange($this->checkIn, $this->checkOut))
                    ->toArray();
            } catch (\Throwable) {
                $summary = null;
            }
        }

        $count = max($fallbackCount, (int) ($summary['occupants_count'] ?? 0));

        return [
            'count' => $count,
            'summary' => __('listing.detail.nearby.summary', [
                'count' => $count,
                'quiet' => $quiet,
            ]),
            'privacy' => __('listing.detail.nearby.privacy'),
            'privacy_note' => $summary['privacy_note'] ?? __('occupants.privacy_note'),
            'badges' => $summary['badges'] ?? [],
            'messages' => $summary['messages'] ?? [],
            'warnings' => $summary['warnings'] ?? [],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function rulesByGroup(SleepingPlace $place): array
    {
        $rules = collect()
            ->merge($place->property?->rules ?? collect())
            ->merge($place->room?->rules ?? collect())
            ->merge($place->rules ?? collect())
            ->unique('slug')
            ->groupBy(fn ($rule) => $rule->category ?: 'shared_room_behavior');

        $groups = [];

        foreach ($rules as $category => $items) {
            $groups[(string) $category] = $this->ruleLabels($items);
        }

        return $groups;
    }

    /**
     * @return array{days:list<array{label:string,weekday:string,status_label:string,is_selected:bool,is_blocked:bool,price:?string,check_in_allowed:bool,check_out_allowed:bool}>, range_label:string, fallback:string}
     */
    private function calendarPreview(SleepingPlace $place, ?array $quote): array
    {
        $start = $this->calendarStartDate();
        $end = $start->addDays(14);
        $selectedStart = $this->parseDateOrNull($this->checkIn);
        $selectedEnd = $this->parseDateOrNull($this->checkOut);
        $currency = strtoupper((string) (($quote['currency'] ?? null) ?: ($place->currency ?: 'EUR')));

        $availabilityByDate = $place->relationLoaded('availabilityDays')
            ? $place->availabilityDays
                ->filter(function (AvailabilityDay $day) use ($start, $end): bool {
                    $date = CarbonImmutable::parse($day->date)->startOfDay();

                    return $date->greaterThanOrEqualTo($start) && $date->lessThan($end);
                })
                ->keyBy(fn (AvailabilityDay $day): string => $day->date->toDateString())
            : AvailabilityDay::query()
                ->select(['id', 'sleeping_place_id', 'date', 'status', 'price_override', 'check_in_allowed', 'check_out_allowed'])
                ->where('sleeping_place_id', $place->id)
                ->where('date', '>=', $start->toDateString())
                ->where('date', '<', $end->toDateString())
                ->orderBy('date')
                ->get()
                ->keyBy(fn (AvailabilityDay $day): string => $day->date->toDateString());

        $days = [];

        for ($date = $start; $date->lessThan($end); $date = $date->addDay()) {
            $dateKey = $date->toDateString();
            $availability = $availabilityByDate->get($dateKey);
            $statusValue = $availability?->status instanceof AvailabilityStatus
                ? $availability->status->value
                : (string) ($availability?->status ?: AvailabilityStatus::Available->value);

            $days[] = [
                'label' => $date->translatedFormat('d M'),
                'weekday' => $date->translatedFormat('D'),
                'status_label' => $this->availabilityStatusLabel($statusValue),
                'is_selected' => $selectedStart instanceof CarbonImmutable
                    && $selectedEnd instanceof CarbonImmutable
                    && $date->greaterThanOrEqualTo($selectedStart)
                    && $date->lessThan($selectedEnd),
                'is_blocked' => in_array($statusValue, AvailabilityStatus::blocksStayValues(), true),
                'price' => $availability?->price_override === null
                    ? null
                    : $this->money((float) $availability->price_override, $currency),
                'check_in_allowed' => (bool) ($availability?->check_in_allowed ?? true),
                'check_out_allowed' => (bool) ($availability?->check_out_allowed ?? true),
            ];
        }

        return [
            'days' => $days,
            'range_label' => __('listing.detail.calendar.range', [
                'start' => $start->translatedFormat('d M'),
                'end' => $end->subDay()->translatedFormat('d M'),
            ]),
            'fallback' => __('listing.detail.calendar.fallback'),
        ];
    }

    /**
     * @return array{area:string,description:string,transport:string,distance:string,privacy:string}
     */
    private function mapDetails(SleepingPlace $place): array
    {
        $property = $place->property;
        $translation = $this->translation($property?->translations ?? collect());

        return [
            'area' => $this->location($property),
            'description' => $translation?->neighborhood_description ?: __('listing.detail.map.neighborhood_missing'),
            'transport' => $property?->nearest_transport ?: $translation?->getting_there ?: __('listing.detail.property.transport_missing'),
            'distance' => $this->distanceLabel($property?->distance_to_center_meters),
            'privacy' => __('listing.detail.map.address_privacy'),
        ];
    }

    /**
     * @return array{rows:list<array{label:string,value:string}>, callout:string}
     */
    private function safetyDetails(SleepingPlace $place): array
    {
        $property = $place->property;
        $host = $property?->host;
        $hostProfile = $host?->hostProfile;
        $translation = $this->translation($property?->translations ?? collect());
        $addressVisibility = $this->addressVisibility($property);
        $hostVerified = (bool) ($hostProfile?->verified_at || $host?->identity_verified);

        return [
            'rows' => [
                $this->row(
                    'listing.detail.safety.host_verification',
                    $hostVerified ? __('listing.detail.safety.verified') : __('listing.detail.safety.not_verified_yet'),
                ),
                $this->row('listing.detail.safety.address_privacy', $addressVisibility['note']),
                $this->row('listing.detail.safety.property_safety', $this->safetySummary($property, $translation?->safety_notes)),
                $this->row('listing.detail.safety.emergency_help', $this->emergencyHelpLabel($hostProfile)),
            ],
            'callout' => __('listing.detail.safety.complaint_help'),
        ];
    }

    /**
     * @return array{rows:list<array{label:string,value:string}>}
     */
    private function cancellationDetails(SleepingPlace $place, ?array $quote): array
    {
        $hostProfile = $place->property?->host?->hostProfile;
        $policy = (string) ($hostProfile?->default_cancellation_policy ?: 'flexible');
        $deadline = $quote['cancellation_deadline'] ?? null;

        return [
            'rows' => [
                $this->row('listing.detail.cancellation.policy', $this->cancellationPolicyLabel($policy)),
                $this->row(
                    'listing.detail.cancellation.free_until',
                    is_string($deadline) && $deadline !== ''
                        ? __('listing.detail.cancellation.free_until_value', ['date' => $this->localizedDateTime($deadline)])
                        : __('listing.detail.cancellation.choose_dates'),
                ),
                $this->row(
                    'listing.detail.cancellation.extension',
                    $place->extensions_allowed
                        ? __('listing.detail.cancellation.extension_available')
                        : __('listing.detail.cancellation.extension_unavailable'),
                ),
                $this->row('listing.detail.cancellation.payout', __('listing.detail.cancellation.payout_after_checkin')),
            ],
        ];
    }

    /**
     * @return list<array{question:string,answer:string}>
     */
    private function faqItems(SleepingPlace $place): array
    {
        $hostProfile = $place->property?->host?->hostProfile;

        return [
            [
                'question' => __('listing.detail.faq.bedding.question'),
                'answer' => $place->has_bedding ? __('listing.detail.faq.bedding.yes') : __('listing.detail.faq.bedding.no'),
            ],
            [
                'question' => __('listing.detail.faq.towel.question'),
                'answer' => $place->has_towel ? __('listing.detail.faq.towel.yes') : __('listing.detail.faq.towel.no'),
            ],
            [
                'question' => __('listing.detail.faq.late_checkin.question'),
                'answer' => $hostProfile?->can_help_with_check_in ? __('listing.detail.faq.late_checkin.ask_host') : __('listing.detail.faq.late_checkin.check_rules'),
            ],
            [
                'question' => __('listing.detail.faq.deposit.question'),
                'answer' => $place->deposit_amount > 0
                    ? __('listing.detail.faq.deposit.amount', ['amount' => $this->money((float) $place->deposit_amount, $place->currency)])
                    : __('listing.detail.faq.deposit.none'),
            ],
            [
                'question' => __('listing.detail.faq.cancellation.question'),
                'answer' => __('listing.detail.faq.cancellation.answer', [
                    'policy' => $this->valueLabel($hostProfile?->default_cancellation_policy ?: 'flexible'),
                ]),
            ],
            [
                'question' => __('listing.detail.faq.extension.question'),
                'answer' => __('listing.detail.faq.extension.answer'),
            ],
        ];
    }

    private function title(SleepingPlace $place): string
    {
        $translation = $this->translation($place->translations);

        return $translation?->title
            ?: $place->display_name
            ?: __('search.card.untitled', ['number' => $place->place_number ?: $place->id]);
    }

    private function translation(Collection $translations): ?object
    {
        return $this->resolver()->resolve(
            $translations,
            app()->getLocale(),
            'en',
        );
    }

    private function resolver(): LocalizedModelContentResolver
    {
        return app(LocalizedModelContentResolver::class);
    }

    /**
     * @return list<string>
     */
    private function translationLocales(): array
    {
        return app(SupportedContentLocales::class)->preferred();
    }

    private function location(?Property $property): string
    {
        $parts = array_filter([
            $property?->cityModel?->name ?: $property?->city,
            $property?->district,
        ]);

        return $parts === [] ? __('search.card.location_missing') : implode(', ', $parts);
    }

    /**
     * @return array<string, mixed>
     */
    private function addressVisibility(?Property $property): array
    {
        if (! $property) {
            return [
                'address' => __('listing.detail.property.address_missing'),
                'note' => __('listing.detail.property.address_private_note'),
                'instructions' => null,
            ];
        }

        return app(ListingAddressVisibilityService::class)->addressFor($property, auth()->user());
    }

    private function label(mixed $value): string
    {
        if ($value instanceof BackedEnum && method_exists($value, 'label')) {
            return $value->label();
        }

        return $this->valueLabel($value);
    }

    private function valueLabel(mixed $value): string
    {
        if ($value === null || $value === '') {
            return __('listing.detail.values.not_set');
        }

        $key = 'listing.detail.values.'.Str::slug((string) $value, '_');

        return Lang::has($key) ? __($key) : __('listing.detail.values.unknown');
    }

    /**
     * @param  list<mixed>  $values
     */
    private function compoundValue(array $values): string
    {
        $labels = collect($values)
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->map(fn ($value): string => $this->valueLabel($value))
            ->values();

        return $labels->isEmpty() ? __('listing.detail.values.not_set') : $labels->join(', ');
    }

    /**
     * @param  array<string, bool>  $values
     */
    private function yesNoList(array $values): string
    {
        $active = collect($values)
            ->filter()
            ->keys()
            ->map(fn (string $key): string => __($key))
            ->values();

        return $active->isEmpty() ? __('listing.detail.values.not_set') : $active->join(', ');
    }

    /**
     * @return array{label:string,value:string}
     */
    private function row(string $labelKey, string $value): array
    {
        return ['label' => __($labelKey), 'value' => $value];
    }

    private function safetySummary(?Property $property, ?string $safetyNotes): string
    {
        $items = collect([
            $property?->has_security ? __('listing.detail.property.safety.security') : null,
            $property?->has_cctv_common_areas ? __('listing.detail.property.safety.cctv') : null,
            $property?->has_hot_water ? __('listing.detail.property.safety.hot_water') : null,
            $safetyNotes,
        ])->filter();

        return $items->isEmpty()
            ? __('listing.detail.property.safety_missing')
            : $items->join(' · ');
    }

    /**
     * @param  Collection<int, mixed>  $amenities
     * @return list<string>
     */
    private function amenityLabels(Collection $amenities): array
    {
        return $amenities
            ->filter(fn ($amenity): bool => ($amenity->status ?? 'active') === 'active')
            ->map(fn ($amenity): string => $this->translation($amenity->translations)?->name ?: $this->valueLabel($amenity->slug))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, mixed>  $rules
     * @return list<string>
     */
    private function ruleLabels(Collection $rules): array
    {
        return $rules
            ->filter(fn ($rule): bool => ($rule->status ?? 'active') === 'active')
            ->map(fn ($rule): string => $this->translation($rule->translations)?->name ?: $this->valueLabel($rule->slug))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $categories
     * @return list<string>
     */
    private function ruleLabelsByCategories(SleepingPlace $place, array $categories): array
    {
        return $this->ruleLabels(
            collect()
                ->merge($place->property?->rules ?? collect())
                ->merge($place->room?->rules ?? collect())
                ->merge($place->rules ?? collect())
                ->whereIn('category', $categories)
        );
    }

    /**
     * @param  list<?string>  $values
     */
    private function firstText(array $values): ?string
    {
        foreach ($values as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function dateRangeSummary(): string
    {
        $checkIn = $this->parseDateOrNull($this->checkIn);
        $checkOut = $this->parseDateOrNull($this->checkOut);

        if (! $checkIn instanceof CarbonImmutable || ! $checkOut instanceof CarbonImmutable) {
            return __('listing.detail.flow.dates_empty');
        }

        return __('listing.detail.flow.dates_value', [
            'check_in' => $checkIn->translatedFormat('d M'),
            'check_out' => $checkOut->translatedFormat('d M'),
        ]);
    }

    private function staySummary(): string
    {
        $checkIn = $this->parseDateOrNull($this->checkIn);
        $checkOut = $this->parseDateOrNull($this->checkOut);

        if (! $checkIn instanceof CarbonImmutable || ! $checkOut instanceof CarbonImmutable || $checkOut->lessThanOrEqualTo($checkIn)) {
            return __('listing.detail.overview.dates_empty');
        }

        $nights = (int) $checkIn->diffInDays($checkOut);
        $calendarDays = $nights + 1;

        return trans_choice('listing.detail.overview.stay_summary', $nights, [
            'nights' => $nights,
            'days' => $calendarDays,
        ]);
    }

    private function priceSummary(SleepingPlace $place, ?array $quote): string
    {
        $currency = strtoupper((string) (($quote['currency'] ?? null) ?: ($place->currency ?: 'EUR')));

        if ($quote) {
            return $this->money((float) $quote['total_amount'], $currency);
        }

        return __('listing.detail.flow.price_from', [
            'amount' => $this->money((float) $place->base_price_per_night, $currency),
        ]);
    }

    private function bookingModeLabel(SleepingPlace $place): string
    {
        return $place->instant_booking_enabled
            ? __('listing.detail.flow.booking_instant')
            : __('listing.detail.flow.booking_request');
    }

    private function calendarStartDate(): CarbonImmutable
    {
        $checkIn = $this->parseDateOrNull($this->checkIn);

        return $checkIn instanceof CarbonImmutable
            ? $checkIn
            : CarbonImmutable::today();
    }

    private function parseDateOrNull(?string $value): ?CarbonImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function availabilityStatusLabel(string $value): string
    {
        $status = AvailabilityStatus::tryFrom($value);

        return $status instanceof AvailabilityStatus ? $status->label() : $this->valueLabel($value);
    }

    private function distanceLabel(mixed $meters): string
    {
        if ($meters === null || $meters === '') {
            return __('listing.detail.map.distance_missing');
        }

        $meters = (int) $meters;

        if ($meters < 1000) {
            return __('listing.detail.map.distance_meters', ['count' => $meters]);
        }

        return __('listing.detail.map.distance_km', [
            'count' => number_format($meters / 1000, 1),
        ]);
    }

    private function emergencyHelpLabel(?HostProfile $hostProfile): string
    {
        if ($hostProfile?->emergency_contact_available) {
            return __('listing.detail.safety.emergency_available');
        }

        if ($hostProfile?->can_help_with_check_in) {
            return __('listing.detail.safety.host_can_help');
        }

        return __('listing.detail.safety.ask_host');
    }

    private function cancellationPolicyLabel(string $policy): string
    {
        $key = 'listing.cancellation_policy.'.$policy;

        return Lang::has($key) ? __($key) : $this->valueLabel($policy);
    }

    private function localizedDateTime(string $value): string
    {
        return CarbonImmutable::parse($value)->translatedFormat('d M, H:i');
    }

    private function nearbyGuestCount(SleepingPlace $place): int
    {
        if ($this->checkIn === '' || $this->checkOut === '') {
            return (int) ($place->room?->occupied_places_count ?: 0);
        }

        try {
            $checkIn = CarbonImmutable::parse($this->checkIn)->toDateString();
            $checkOut = CarbonImmutable::parse($this->checkOut)->toDateString();
        } catch (\Throwable) {
            return (int) ($place->room?->occupied_places_count ?: 0);
        }

        return Booking::query()
            ->where('room_id', $place->room_id)
            ->where('sleeping_place_id', '!=', $place->id)
            ->whereNotIn('status', $this->nonBlockingBookingStatuses())
            ->whereDate('check_in_date', '<', $checkOut)
            ->whereDate('check_out_date', '>', $checkIn)
            ->distinct()
            ->count('guest_user_id');
    }

    /**
     * @return list<string>
     */
    private function nonBlockingBookingStatuses(): array
    {
        return [
            BookingStatus::Draft->value,
            BookingStatus::CancelledByGuest->value,
            BookingStatus::CancelledByHost->value,
            BookingStatus::CancelledBySystem->value,
            BookingStatus::CancelledByService->value,
            BookingStatus::NoShow->value,
            BookingStatus::HostNoShow->value,
            BookingStatus::CheckedOut->value,
            BookingStatus::Completed->value,
            BookingStatus::AwaitingReview->value,
            BookingStatus::Closed->value,
        ];
    }

    private function money(float|int|string $amount, string $currency): string
    {
        return Number::currency((float) $amount, $currency, app()->getLocale());
    }
}
