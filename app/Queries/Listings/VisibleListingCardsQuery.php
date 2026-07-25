<?php

namespace App\Queries\Listings;

use App\Data\Listings\ListingCardContext;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\SleepingPlace;
use App\Services\Listings\ListingCardAmenityRuleService;
use App\Services\Localization\SupportedContentLocales;
use Illuminate\Database\Eloquent\Builder;

final readonly class VisibleListingCardsQuery
{
    /**
     * Build the guest-facing listing card query with selected columns, aggregates, and card relations.
     *
     * @return Builder<SleepingPlace>
     */
    public function handle(ListingCardContext $context): Builder
    {
        return SleepingPlace::query()
            ->select($this->sleepingPlaceColumns())
            ->join('rooms as search_rooms', 'search_rooms.id', '=', 'sleeping_places.room_id')
            ->join('properties as search_properties', 'search_properties.id', '=', 'sleeping_places.property_id')
            ->leftJoin('property_location_details as search_property_location_details', 'search_property_location_details.property_id', '=', 'search_properties.id')
            ->leftJoin('property_access_details as search_property_access_details', 'search_property_access_details.property_id', '=', 'search_properties.id')
            ->leftJoin('property_condition_details as search_property_condition_details', 'search_property_condition_details.property_id', '=', 'search_properties.id')
            ->leftJoin('host_profiles as search_host_profiles', 'search_host_profiles.user_id', '=', 'search_properties.host_user_id')
            ->where('sleeping_places.status', SleepingPlaceStatus::Active->value)
            ->where('search_rooms.status', RoomStatus::Active->value)
            ->where('search_properties.status', PropertyStatus::Active->value)
            ->withCount([
                'reviews as published_reviews_count' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ])
            ->withAvg([
                'reviews as published_reviews_rating' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ], 'overall_rating')
            ->withAvg([
                'reviews as published_cleanliness_rating' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ], 'cleanliness_rating')
            ->withAvg([
                'reviews as published_safety_rating' => fn (Builder $query) => $query->visible()->guestToPlace(),
            ], 'safety_rating')
            ->with($this->cardEagerLoads($context));
    }

    /**
     * @return array<string, mixed>
     */
    private function cardEagerLoads(ListingCardContext $context): array
    {
        $locales = $this->translationLocales($context);
        $mediaSelect = [
            'id',
            'mediable_type',
            'mediable_id',
            'disk',
            'path',
            'thumb_path',
            'thumbnail_path',
            'mobile_path',
            'full_path',
            'alt_text',
            'sort_order',
            'is_primary',
            'is_cover',
            'status',
        ];
        $mediaTranslations = fn ($query) => $query
            ->select(['id', 'media_item_id', 'locale', 'caption'])
            ->whereIn('locale', $locales);
        $amenitySelect = ['amenities.id', 'amenities.slug', 'amenities.category', 'amenities.status'];
        $ruleSelect = ['rules.id', 'rules.slug', 'rules.category', 'rules.status'];
        $roomCompatibilitySelect = [
            'id',
            'room_id',
            'gender_policy',
            'is_private',
            'is_shared',
            'max_people_in_room',
            'current_people_count',
            'typical_people_count',
            'noise_level',
            'light_level',
            'quiet_hours_enabled',
            'quiet_hours_start',
            'quiet_hours_end',
            'can_turn_light_at_night',
            'can_work_at_night',
            'can_eat',
            'can_store_food',
            'has_workspace',
            'has_desk',
            'has_chair',
            'has_personal_lockers',
            'has_lock',
            'has_window',
            'has_air_conditioning',
            'has_heating',
            'can_open_window',
            'smoking_allowed',
            'pets_present',
            'pets_allowed',
            'kitchen_night_use_allowed',
            'washing_machine_available',
            'long_stay_allowed',
            'short_stay_allowed',
            'late_entry_allowed',
        ];
        $sleepingPlaceCompatibilitySelect = [
            'id',
            'sleeping_place_id',
            'sleeping_place_type',
            'is_top_bunk',
            'is_bottom_bunk',
            'is_sofa',
            'is_floor_mattress',
            'is_for_one_person',
            'is_for_couple',
            'has_curtain',
            'has_locker',
            'locker_has_lock',
            'has_power_socket',
            'has_usb_charger',
            'has_personal_lamp',
            'has_shelf',
            'has_luggage_space',
            'has_bedding',
            'has_towel',
            'privacy_level',
            'noise_level_near_place',
            'light_level_near_place',
            'suitable_for_tall_person',
            'suitable_for_heavy_person',
            'suitable_for_limited_mobility',
            'min_nights',
            'max_nights',
            'can_extend',
            'instant_booking_enabled',
        ];
        $amenities = fn ($query) => $query
            ->select($amenitySelect)
            ->whereIn('amenities.slug', ListingCardAmenityRuleService::KEY_AMENITY_SLUGS)
            ->where('amenities.status', 'active');
        $rules = fn ($query) => $query
            ->select($ruleSelect)
            ->whereIn('rules.slug', ListingCardAmenityRuleService::KEY_RULE_SLUGS)
            ->where('rules.status', 'active');
        $amenityTranslations = fn ($query) => $query
            ->select(['id', 'amenity_id', 'locale', 'name'])
            ->whereIn('locale', $locales);
        $ruleTranslations = fn ($query) => $query
            ->select(['id', 'rule_id', 'locale', 'name'])
            ->whereIn('locale', $locales);

        $with = [
            'translations' => fn ($query) => $query
                ->select(['id', 'sleeping_place_id', 'locale', 'title', 'summary'])
                ->whereIn('locale', $locales),
            'cardMedia' => fn ($query) => $query->select($mediaSelect)->with(['translations' => $mediaTranslations]),
            'amenities' => $amenities,
            'amenities.translations' => $amenityTranslations,
            'rules' => $rules,
            'rules.translations' => $ruleTranslations,
            'compatibilityProfile' => fn ($query) => $query->select($sleepingPlaceCompatibilitySelect),
            'listingHintSnapshots' => fn ($query) => $query
                ->select([
                    'id',
                    'sleeping_place_id',
                    'hint_key',
                    'category',
                    'type',
                    'importance',
                    'priority',
                    'message_key',
                    'message_params_json',
                    'source',
                    'show_on_card',
                    'show_on_detail',
                    'show_before_booking',
                    'show_in_favorites',
                    'show_in_saved_search',
                    'calculated_at',
                    'expires_at',
                    'valid_from',
                    'valid_until',
                ])
                ->fresh()
                ->where('show_on_card', true)
                ->orderByDesc('priority'),
            'room' => fn ($query) => $query
                ->select([
                    'id',
                    'property_id',
                    'type',
                    'status',
                    'title',
                    'gender_policy',
                    'gender_type',
                    'capacity',
                    'beds_count',
                    'max_guests',
                    'occupied_places_count',
                    'available_places_count',
                    'has_desk',
                    'has_chair',
                    'noise_level',
                ])
                ->with([
                    'translations' => fn ($translation) => $translation
                        ->select(['id', 'room_id', 'locale', 'title', 'summary'])
                        ->whereIn('locale', $locales),
                    'cardMedia' => fn ($media) => $media->select($mediaSelect)->with(['translations' => $mediaTranslations]),
                    'amenities' => $amenities,
                    'amenities.translations' => $amenityTranslations,
                    'rules' => $rules,
                    'rules.translations' => $ruleTranslations,
                    'compatibilityProfile' => fn ($profile) => $profile->select($roomCompatibilitySelect),
                ]),
            'property' => fn ($query) => $query
                ->select([
                    'id',
                    'host_user_id',
                    'city_id',
                    'type',
                    'property_type',
                    'status',
                    'city',
                    'district',
                    'distance_to_center_meters',
                    'kitchens_count',
                    'has_elevator',
                    'has_parking',
                    'title',
                    'current_guests_count',
                    'current_residents_count',
                ])
                ->with([
                    'translations' => fn ($translation) => $translation
                        ->select(['id', 'property_id', 'locale', 'title', 'summary'])
                        ->whereIn('locale', $locales),
                    'cityModel:id,name',
                    'cardMedia' => fn ($media) => $media->select($mediaSelect)->with(['translations' => $mediaTranslations]),
                    'amenities' => $amenities,
                    'amenities.translations' => $amenityTranslations,
                    'rules' => $rules,
                    'rules.translations' => $ruleTranslations,
                    'host:id,name,rating_as_host,identity_verified,identity_verified_at',
                    'host.hostProfile:id,user_id,rating_average,reviews_count,response_time_minutes,verified_at,default_cancellation_policy',
                ]),
        ];

        if ($context->hasDates()) {
            $with['availabilityDays'] = fn ($query) => $query
                ->select(['id', 'sleeping_place_id', 'date', 'price_override'])
                ->whereDate('date', '>=', $context->checkInDate)
                ->whereDate('date', '<', $context->checkOutDate)
                ->whereNotNull('price_override');
        }

        return $with;
    }

    /**
     * @return list<string>
     */
    private function sleepingPlaceColumns(): array
    {
        return [
            'sleeping_places.id',
            'sleeping_places.room_id',
            'sleeping_places.property_id',
            'sleeping_places.type',
            'sleeping_places.status',
            'sleeping_places.place_number',
            'sleeping_places.display_name',
            'sleeping_places.bunk_level',
            'sleeping_places.has_bedding',
            'sleeping_places.has_towel',
            'sleeping_places.has_locker',
            'sleeping_places.has_luggage_space',
            'sleeping_places.is_accessible',
            'sleeping_places.max_guests',
            'sleeping_places.base_price_per_night',
            'sleeping_places.weekly_price',
            'sleeping_places.monthly_price',
            'sleeping_places.weekend_price',
            'sleeping_places.cleaning_fee',
            'sleeping_places.deposit_amount',
            'sleeping_places.currency',
            'sleeping_places.min_nights',
            'sleeping_places.max_nights',
            'sleeping_places.instant_booking_enabled',
            'sleeping_places.requires_host_approval',
            'sleeping_places.extensions_allowed',
            'sleeping_places.created_at',
        ];
    }

    /**
     * @return list<string>
     */
    private function translationLocales(ListingCardContext $context): array
    {
        return array_values(array_unique(array_filter([
            ...app(SupportedContentLocales::class)->preferred($context->locale),
        ])));
    }
}
