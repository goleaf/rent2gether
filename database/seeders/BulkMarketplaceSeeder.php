<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Enums\BedStatus;
use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Enums\PayoutStatus;
use App\Enums\ReviewStatus;
use App\Enums\ReviewType;
use App\Enums\UserStatus;
use App\Models\Amenity;
use App\Models\AmenityTranslation;
use App\Models\AvailabilityDay;
use App\Models\Bed;
use App\Models\BedAvailability;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAlert;
use App\Models\BookingCheckInChecklistItem;
use App\Models\BookingCheckInProblemReport;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutChecklistItem;
use App\Models\BookingCheckOutIssueReport;
use App\Models\BookingDepositDecision;
use App\Models\BookingExtension;
use App\Models\BookingForgottenItem;
use App\Models\BookingGuest;
use App\Models\BookingGuestIntake;
use App\Models\BookingPriceLine;
use App\Models\BookingPriceSnapshot;
use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use App\Models\BookingQuoteSuggestion;
use App\Models\BookingQuoteValidationResult;
use App\Models\BookingReviewRequest;
use App\Models\BookingStatusHistory;
use App\Models\BookingTimelineDate;
use App\Models\CheckinRecord;
use App\Models\CheckoutRecord;
use App\Models\City;
use App\Models\CityTranslation;
use App\Models\CoLivingProfile;
use App\Models\CoLivingVisibilitySetting;
use App\Models\CompatibilityResult;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\DepositRecord;
use App\Models\DiscountRule;
use App\Models\Favorite;
use App\Models\FavoriteCollection;
use App\Models\GuestCompatibilityProfile;
use App\Models\GuestCompatibilityVisibilitySetting;
use App\Models\GuestHintDismissal;
use App\Models\GuestHintImpression;
use App\Models\GuestPreference;
use App\Models\GuestProfile;
use App\Models\HostBulkActionBatch;
use App\Models\HostBulkActionItem;
use App\Models\HostBulkActionLog;
use App\Models\HostCalendarEvent;
use App\Models\HostCalendarNote;
use App\Models\HostCalendarViewSetting;
use App\Models\HostCleaningFinding;
use App\Models\HostCleaningTask;
use App\Models\HostCleaningTaskItem;
use App\Models\HostCleaningTaskPhoto;
use App\Models\HostCleaningTemplate;
use App\Models\HostCurrentStaySnapshot;
use App\Models\HostGuestStayFlag;
use App\Models\HostGuestStayNote;
use App\Models\HostHintAction;
use App\Models\HostHintDismissal;
use App\Models\HostHintSnapshot;
use App\Models\HostInspectionTask;
use App\Models\HostListingSuggestion;
use App\Models\HostListingWizardSession;
use App\Models\HostProfile;
use App\Models\HostRepresentative;
use App\Models\ListingCreationDraft;
use App\Models\ListingHintSnapshot;
use App\Models\ListingPublicationCheck;
use App\Models\ListingReadinessCheck;
use App\Models\MediaItem;
use App\Models\MediaItemTranslation;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentRecord;
use App\Models\Payout;
use App\Models\PriceRule;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\PropertyAddress;
use App\Models\PropertyAmenity;
use App\Models\PropertyConditionDetail;
use App\Models\PropertyLocationDetail;
use App\Models\PropertyPhoto;
use App\Models\PropertyRule;
use App\Models\PropertyTranslation;
use App\Models\RefundRequest;
use App\Models\Region;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomAccessDetail;
use App\Models\RoomComfortDetail;
use App\Models\RoomCompatibilityProfile;
use App\Models\RoomConditionDetail;
use App\Models\RoomLayoutDetail;
use App\Models\RoomOccupantSnapshot;
use App\Models\RoomPhoto;
use App\Models\RoomTemplate;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\RuleTranslation;
use App\Models\SavedSearch;
use App\Models\SavedSearchResult;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceAvailabilityStatusLog;
use App\Models\SleepingPlaceBookingDateLock;
use App\Models\SleepingPlaceCalendarBlock;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\SleepingPlaceCalendarRule;
use App\Models\SleepingPlaceCalendarSetting;
use App\Models\SleepingPlaceComfortDetail;
use App\Models\SleepingPlaceCompatibilityProfile;
use App\Models\SleepingPlaceConditionDetail;
use App\Models\SleepingPlaceCreationBatch;
use App\Models\SleepingPlaceDatePrice;
use App\Models\SleepingPlaceDiscountRule;
use App\Models\SleepingPlacePhoto;
use App\Models\SleepingPlacePhysicalDetail;
use App\Models\SleepingPlacePositionDetail;
use App\Models\SleepingPlacePricingSetting;
use App\Models\SleepingPlaceStorageDetail;
use App\Models\SleepingPlaceTemplate;
use App\Models\SleepingPlaceTranslation;
use App\Models\SleepingPlaceTurnoverRule;
use App\Models\User;
use App\Models\UserActivitySummary;
use App\Models\UserDocument;
use App\Models\UserLanguage;
use App\Models\UserNotificationPreference;
use App\Models\UserPrivacySetting;
use App\Models\UserProfile;
use App\Models\UserSavedPreference;
use App\Models\UserSetting;
use App\Models\UserVerification;
use App\Models\WaitlistEntry;
use App\Models\WaitlistItem;
use App\Models\WaitlistOffer;
use App\Services\Localization\SupportedContentLocales;
use App\Services\Media\DemoMediaFileService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BulkMarketplaceSeeder extends Seeder
{
    private const TARGET_COUNT = 1000;

    public function run(): void
    {
        $this->seedGeo();
        $this->seedCatalog();
        $this->seedUsers();
        $this->seedUserProfilesAndSettings();
        $this->seedUserIdentityPrivacyAndPreferences();
        $this->seedListingHierarchy();
        $this->seedListingDetails();
        $this->seedListingTranslations();
        $this->seedCompatibilityRecords();
        $this->seedAvailabilityAndPricing();
        $this->seedBookingQuotes();
        $this->seedBookings();
        $this->seedBookingChildren();
        $this->seedBookingLifecycleRecords();
        $this->seedLegacyAvailabilityAndWaitlists();
        $this->seedMessaging();
        $this->seedSocialRecords();
        $this->seedDecisionAndQueueRecords();
        $this->seedHostOperationalRecords();
        $this->seedListingWorkflowRecords();
        $this->seedMediaAndNotifications();
    }

    private function seedGeo(): void
    {
        $missingCountries = $this->missingFor(Country::class);
        $existingCountryCodes = Country::query()->pluck('code')->all();
        $createdCountries = 0;

        for ($seedIndex = 1; $createdCountries < $missingCountries; $seedIndex++) {
            $code = sprintf('ZZ%04d', $seedIndex);

            if (in_array($code, $existingCountryCodes, true)) {
                continue;
            }

            Country::query()->create([
                'iso2' => $code,
                'code' => $code,
                'iso3' => sprintf('ZZZ%04d', $seedIndex),
                'name' => sprintf('Bulk demo country %04d', $seedIndex),
                'name_en' => sprintf('Bulk demo country %04d', $seedIndex),
                'name_ru' => sprintf('Bulk demo country RU %04d', $seedIndex),
                'name_native' => sprintf('Bulk demo country %04d', $seedIndex),
                'currency_code' => 'EUR',
                'phone_code' => '+000',
                'timezone_default' => 'Europe/Vilnius',
                'status' => Country::STATUS_ACTIVE,
                'source' => 'bulk_demo_seed',
                'is_active' => true,
            ]);

            $existingCountryCodes[] = $code;
            $createdCountries++;
        }

        $countryIds = $this->ids(Country::class);
        $missingRegions = $this->missingFor(Region::class);
        $createdRegions = 0;

        for ($seedIndex = 1; $createdRegions < $missingRegions; $seedIndex++) {
            $countryId = $this->pick($countryIds, $seedIndex);
            $code = sprintf('BR%04d', $seedIndex);

            $region = Region::query()->firstOrCreate(
                ['country_id' => $countryId, 'code' => $code],
                [
                    'name' => sprintf('Bulk demo region %04d', $seedIndex),
                    'source' => 'bulk_demo_seed',
                    'source_id' => sprintf('bulk-region-%04d', $seedIndex),
                ],
            );

            if ($region->wasRecentlyCreated) {
                $createdRegions++;
            }
        }

        $regionRows = $this->regionRows();
        $missingCities = $this->missingFor(City::class);
        $createdCities = 0;

        for ($seedIndex = 1; $createdCities < $missingCities; $seedIndex++) {
            $region = $this->pick($regionRows, $seedIndex);
            $geonameId = 9_000_000 + $seedIndex;

            $city = City::query()->firstOrCreate(
                ['geoname_id' => $geonameId],
                [
                    'country_id' => $region['country_id'],
                    'region_id' => $region['id'],
                    'name' => sprintf('Bulk demo city %04d', $seedIndex),
                    'ascii_name' => sprintf('Bulk demo city %04d', $seedIndex),
                    'alternate_names' => null,
                    'latitude' => 54.6000000 + (($seedIndex % 100) / 1000),
                    'longitude' => 25.2000000 + (($seedIndex % 100) / 1000),
                    'population' => 10_000 + $seedIndex,
                    'timezone' => 'Europe/Vilnius',
                    'feature_class' => 'P',
                    'feature_code' => 'PPL',
                    'status' => City::STATUS_ACTIVE,
                    'source' => 'bulk_demo_seed',
                    'source_id' => (string) $geonameId,
                    'is_active' => true,
                ],
            );

            if ($city->wasRecentlyCreated) {
                $createdCities++;
            }
        }
    }

    private function seedCatalog(): void
    {
        $missingAmenities = $this->missingFor(Amenity::class);
        $existingAmenitySlugs = Amenity::query()->pluck('slug')->all();
        $createdAmenities = 0;

        for ($seedIndex = 1; $createdAmenities < $missingAmenities; $seedIndex++) {
            $slug = sprintf('bulk-amenity-%04d', $seedIndex);

            if (in_array($slug, $existingAmenitySlugs, true)) {
                continue;
            }

            Amenity::query()->create([
                'slug' => $slug,
                'name_normalized' => Str::of($slug)->replace('-', ' ')->toString(),
                'category' => 'bulk_demo',
                'icon' => null,
                'status' => 'active',
            ]);

            $existingAmenitySlugs[] = $slug;
            $createdAmenities++;
        }

        $missingRules = $this->missingFor(Rule::class);
        $existingRuleSlugs = Rule::query()->pluck('slug')->all();
        $createdRules = 0;

        for ($seedIndex = 1; $createdRules < $missingRules; $seedIndex++) {
            $slug = sprintf('bulk-rule-%04d', $seedIndex);

            if (in_array($slug, $existingRuleSlugs, true)) {
                continue;
            }

            Rule::query()->create([
                'slug' => $slug,
                'name_normalized' => Str::of($slug)->replace('-', ' ')->toString(),
                'category' => 'bulk_demo',
                'requires_confirmation' => $seedIndex % 3 === 0,
                'status' => 'active',
            ]);

            $existingRuleSlugs[] = $slug;
            $createdRules++;
        }

        $this->seedAmenityTranslations();
        $this->seedRuleTranslations();
    }

    private function seedUsers(): void
    {
        $missing = $this->missingFor(User::class);
        $start = User::query()->count();

        User::factory()
            ->count($missing)
            ->sequence(fn (Sequence $sequence): array => [
                'name' => sprintf('Bulk Demo User %04d', $start + $sequence->index + 1),
                'email' => sprintf('bulk.user.%04d@rent2gether.test', $start + $sequence->index + 1),
                'phone' => sprintf('+370600%05d', $start + $sequence->index + 1),
                'phone_verified' => true,
                'country' => 'Lithuania',
                'city' => 'Vilnius',
                'languages' => ['en', 'ru'],
                'bio' => 'Bulk demo account for marketplace testing.',
                'occupation' => 'Marketplace tester',
                'travel_purpose' => 'temporary_stay',
                'prefers_quiet' => true,
                'willing_to_share_room' => true,
                'identity_verified' => true,
                'identity_verified_at' => now(),
                'is_host' => true,
                'host_description' => 'Bulk demo host profile.',
                'host_experience_years' => 1,
                'host_lives_on_site' => false,
                'rating_as_guest' => 4.70,
                'rating_as_host' => 4.75,
                'completed_stays_count' => 1,
                'hosted_stays_count' => 1,
                'status' => UserStatus::Active->value,
                'last_active_at' => now(),
            ])
            ->create();
    }

    private function seedUserProfilesAndSettings(): void
    {
        $userIds = $this->ids(User::class);
        $cityRows = $this->cityRows();

        $this->seedMissingUserOwnedRows(
            UserProfile::class,
            $userIds,
            fn (int $userId, int $index): UserProfile => UserProfile::factory()->create([
                'user_id' => $userId,
                'display_name' => sprintf('Bulk profile %04d', $index + 1),
                'country_id' => $this->pick($cityRows, $index)['country_id'],
                'city_id' => $this->pick($cityRows, $index)['id'],
                'status' => UserStatus::Active->value,
            ]),
        );

        $this->seedMissingUserOwnedRows(
            GuestPreference::class,
            $userIds,
            fn (int $userId, int $index): GuestPreference => GuestPreference::factory()->create([
                'user_id' => $userId,
                'preferred_city_id' => $this->pick($cityRows, $index)['id'],
            ]),
        );

        $this->seedMissingUserOwnedRows(
            HostProfile::class,
            $userIds,
            fn (int $userId, int $index): HostProfile => HostProfile::factory()->create([
                'user_id' => $userId,
                'display_name' => sprintf('Bulk host %04d', $index + 1),
                'status' => UserStatus::Active->value,
            ]),
        );

        $this->seedMissingUserOwnedRows(
            UserSetting::class,
            $userIds,
            fn (int $userId, int $index): UserSetting => UserSetting::factory()->create([
                'user_id' => $userId,
                'locale' => $index % 2 === 0 ? 'en' : 'ru',
                'currency' => 'EUR',
                'active_mode' => UserSetting::MODE_GUEST,
                'account_role' => UserSetting::ROLE_BOTH,
            ]),
        );
    }

    private function seedUserIdentityPrivacyAndPreferences(): void
    {
        $userIds = $this->ids(User::class);
        $cityRows = $this->cityRows();

        $this->seedMissingOwnedRows(
            GuestProfile::class,
            'user_id',
            $userIds,
            fn (int $userId): GuestProfile => GuestProfile::factory()->create(['user_id' => $userId]),
        );

        $this->seedMissingOwnedRows(
            CoLivingProfile::class,
            'user_id',
            $userIds,
            fn (int $userId, int $index): CoLivingProfile => CoLivingProfile::factory()->create([
                'user_id' => $userId,
                'country_id' => $this->pick($cityRows, $index)['country_id'],
                'city_id' => $this->pick($cityRows, $index)['id'],
            ]),
        );

        $this->seedMissingOwnedRows(
            CoLivingVisibilitySetting::class,
            'user_id',
            $userIds,
            fn (int $userId): CoLivingVisibilitySetting => CoLivingVisibilitySetting::factory()->create(['user_id' => $userId]),
        );

        $this->seedMissingOwnedRows(
            GuestCompatibilityProfile::class,
            'user_id',
            $userIds,
            fn (int $userId): GuestCompatibilityProfile => GuestCompatibilityProfile::factory()->create(['user_id' => $userId]),
        );

        $this->seedMissingOwnedRows(
            GuestCompatibilityVisibilitySetting::class,
            'user_id',
            $userIds,
            fn (int $userId): GuestCompatibilityVisibilitySetting => GuestCompatibilityVisibilitySetting::factory()->create(['user_id' => $userId]),
        );

        $this->seedMissingOwnedRows(
            UserPrivacySetting::class,
            'user_id',
            $userIds,
            fn (int $userId): UserPrivacySetting => UserPrivacySetting::factory()->create(['user_id' => $userId]),
        );

        $this->seedMissingOwnedRows(
            UserSavedPreference::class,
            'user_id',
            $userIds,
            fn (int $userId, int $index): UserSavedPreference => UserSavedPreference::factory()->create([
                'user_id' => $userId,
                'preferred_locale' => $index % 2 === 0 ? 'en' : 'ru',
            ]),
        );

        $this->seedMissingOwnedRows(
            UserActivitySummary::class,
            'user_id',
            $userIds,
            fn (int $userId): UserActivitySummary => UserActivitySummary::factory()->create(['user_id' => $userId]),
        );

        $this->seedFactoryRows(
            HostRepresentative::class,
            fn (int $index): array => [
                'host_user_id' => $this->pick($userIds, $index),
                'representative_user_id' => $this->pick($userIds, $index + 1),
                'name' => sprintf('Bulk representative %04d', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            UserVerification::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'verification_type' => ['phone', 'email', 'identity'][$index % 3],
                'status' => $index % 4 === 0 ? 'pending' : 'verified',
                'verified_at' => $index % 4 === 0 ? null : now(),
            ],
        );

        $this->seedFactoryRows(
            UserDocument::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'document_type' => ['identity_document', 'residence_permit', 'student_card'][$index % 3],
                'file_path' => sprintf('private/demo-documents/user-%04d.jpg', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            UserLanguage::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'language_code' => $index % 2 === 0 ? 'en' : 'ru',
                'is_primary' => true,
            ],
        );

        $this->seedFactoryRows(
            UserNotificationPreference::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'category' => ['bookings', 'messages', 'calendar', 'reviews'][$index % 4],
                'channel' => ['in_app', 'email'][$index % 2],
            ],
        );
    }

    private function seedListingHierarchy(): void
    {
        $userIds = $this->ids(User::class);
        $cityRows = $this->cityRows();
        $propertyStart = Property::query()->count();

        Property::factory()
            ->count($this->missingFor(Property::class))
            ->sequence(function (Sequence $sequence) use ($userIds, $cityRows, $propertyStart): array {
                $city = $this->pick($cityRows, $sequence->index);
                $number = $propertyStart + $sequence->index + 1;

                return [
                    'user_id' => $this->pick($userIds, $sequence->index),
                    'host_user_id' => $this->pick($userIds, $sequence->index),
                    'country_id' => $city['country_id'],
                    'region_id' => $city['region_id'],
                    'city_id' => $city['id'],
                    'country' => $city['country_name'],
                    'city' => $city['name'],
                    'region_name' => $city['region_name'],
                    'title' => sprintf('Bulk demo property %04d', $number),
                    'description' => 'Bulk demo property for marketplace testing.',
                    'district' => sprintf('Bulk district %02d', $number % 50),
                    'status' => 'active',
                    'latitude' => $city['latitude'],
                    'longitude' => $city['longitude'],
                    'approximate_latitude' => $city['latitude'],
                    'approximate_longitude' => $city['longitude'],
                ];
            })
            ->create();

        $propertyIds = $this->ids(Property::class);
        $roomStart = Room::query()->count();

        Room::factory()
            ->count($this->missingFor(Room::class))
            ->sequence(fn (Sequence $sequence): array => [
                'property_id' => $this->pick($propertyIds, $sequence->index),
                'title' => sprintf('Bulk demo room %04d', $roomStart + $sequence->index + 1),
                'description' => 'Bulk demo shared room.',
                'status' => 'active',
            ])
            ->create();

        $roomRows = $this->roomRows();
        $sleepingPlaceStart = SleepingPlace::query()->count();

        SleepingPlace::factory()
            ->count($this->missingFor(SleepingPlace::class))
            ->sequence(function (Sequence $sequence) use ($roomRows, $sleepingPlaceStart): array {
                $room = $this->pick($roomRows, $sequence->index);
                $number = $sleepingPlaceStart + $sequence->index + 1;

                return [
                    'room_id' => $room['id'],
                    'property_id' => $room['property_id'],
                    'place_number' => (string) $number,
                    'display_name' => sprintf('Bulk sleeping place %04d', $number),
                    'status' => 'active',
                ];
            })
            ->create();

        $bedStart = Bed::query()->count();

        Bed::factory()
            ->count($this->missingFor(Bed::class))
            ->sequence(fn (Sequence $sequence): array => [
                'room_id' => $this->pick($roomRows, $sequence->index)['id'],
                'title' => sprintf('Bulk demo bed %04d', $bedStart + $sequence->index + 1),
                'status' => BedStatus::Active->value,
            ])
            ->create();
    }

    private function seedListingDetails(): void
    {
        $userIds = $this->ids(User::class);
        $propertyRows = $this->propertyRows();
        $propertyIds = array_column($propertyRows, 'id');
        $roomRows = $this->roomRows();
        $roomIds = array_column($roomRows, 'id');
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $sleepingPlaceIds = array_column($sleepingPlaceRows, 'id');

        $this->seedMissingOwnedRows(
            PropertyAddress::class,
            'property_id',
            $propertyIds,
            fn (int $propertyId, int $index): PropertyAddress => PropertyAddress::factory()->create([
                'property_id' => $propertyId,
                'country_id' => $this->pick($propertyRows, $index)['country_id'],
                'city_id' => $this->pick($propertyRows, $index)['city_id'],
            ]),
        );

        foreach ([PropertyLocationDetail::class, PropertyConditionDetail::class, PropertyAccessDetail::class] as $modelClass) {
            $this->seedMissingOwnedRows(
                $modelClass,
                'property_id',
                $propertyIds,
                fn (int $propertyId): Model => $modelClass::factory()->create(['property_id' => $propertyId]),
            );
        }

        $this->seedFactoryRows(
            PropertyAmenity::class,
            fn (int $index): array => [
                'property_id' => $this->pick($propertyIds, $index),
                'amenity_key' => sprintf('bulk_amenity_%03d', $index % 100),
            ],
        );

        $this->seedFactoryRows(
            PropertyRule::class,
            fn (int $index): array => [
                'property_id' => $this->pick($propertyIds, $index),
                'rule_key' => sprintf('bulk_rule_%03d', $index % 100),
            ],
        );

        $this->seedFactoryRows(
            PropertyPhoto::class,
            fn (int $index): array => [
                'property_id' => $this->pick($propertyIds, $index),
                'uploaded_by_user_id' => $this->pick($userIds, $index),
                'path' => sprintf('bulk-demo/property-photos/%04d.jpg', $index + 1),
                'thumbnail_path' => sprintf('bulk-demo/property-photos/%04d-thumb.jpg', $index + 1),
                'caption' => sprintf('Bulk property photo %04d', $index + 1),
                'sort_order' => $index,
                'is_primary' => $index % 5 === 0,
                'is_main' => $index % 5 === 0,
            ],
        );

        foreach ([RoomLayoutDetail::class, RoomComfortDetail::class, RoomAccessDetail::class, RoomConditionDetail::class, RoomCompatibilityProfile::class] as $modelClass) {
            $this->seedMissingOwnedRows(
                $modelClass,
                'room_id',
                $roomIds,
                fn (int $roomId): Model => $modelClass::factory()->create(['room_id' => $roomId]),
            );
        }

        $this->seedFactoryRows(
            RoomPhoto::class,
            fn (int $index): array => [
                'room_id' => $this->pick($roomIds, $index),
                'uploaded_by_user_id' => $this->pick($userIds, $index),
                'path' => sprintf('bulk-demo/room-photos/%04d.jpg', $index + 1),
                'thumbnail_path' => sprintf('bulk-demo/room-photos/%04d-thumb.jpg', $index + 1),
                'caption' => sprintf('Bulk room photo %04d', $index + 1),
                'sort_order' => $index,
                'is_primary' => $index % 5 === 0,
                'is_main' => $index % 5 === 0,
            ],
        );

        $this->seedFactoryRows(
            RoomTemplate::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'name' => sprintf('Bulk room template %04d', $index + 1),
                'is_default' => $index % 20 === 0,
            ],
        );

        foreach ([SleepingPlacePhysicalDetail::class, SleepingPlaceComfortDetail::class, SleepingPlaceStorageDetail::class, SleepingPlacePositionDetail::class, SleepingPlaceConditionDetail::class, SleepingPlaceCompatibilityProfile::class, SleepingPlaceCalendarSetting::class, SleepingPlaceTurnoverRule::class] as $modelClass) {
            $this->seedMissingOwnedRows(
                $modelClass,
                'sleeping_place_id',
                $sleepingPlaceIds,
                fn (int $sleepingPlaceId): Model => $modelClass::factory()->create(['sleeping_place_id' => $sleepingPlaceId]),
            );
        }

        $this->seedSleepingPlaceCalendarDays($sleepingPlaceIds);

        $this->seedFactoryRows(
            SleepingPlaceCalendarBlock::class,
            function (int $index) use ($sleepingPlaceRows, $userIds): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);
                $startsAt = CarbonImmutable::now()->addDays(300 + $index)->startOfDay();

                return [
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'property_id' => $sleepingPlace['property_id'],
                    'source_type' => 'bulk_demo_seed',
                    'source_id' => $index + 1,
                    'block_type' => match ($index % 3) {
                        0 => 'closed_by_host',
                        1 => 'cleaning',
                        default => 'repair',
                    },
                    'status' => 'released',
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->addDay(),
                    'check_in_date' => $startsAt->toDateString(),
                    'check_out_date' => $startsAt->addDay()->toDateString(),
                    'reason_key' => 'bulk_demo_seed',
                    'visible_to_guest' => false,
                    'created_by_user_id' => $this->pick($userIds, $index),
                    'released_at' => now(),
                ];
            },
        );

        $this->seedFactoryRows(
            SleepingPlaceBookingDateLock::class,
            fn (int $index): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index)['id'],
                'date' => CarbonImmutable::now()->addDays(360 + $index)->toDateString(),
                'lock_type' => 'booked',
                'status' => 'released',
                'released_at' => now(),
            ],
        );

        $this->seedFactoryRows(
            SleepingPlaceAvailabilityStatusLog::class,
            fn (int $index): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index)['id'],
                'date' => CarbonImmutable::now()->addDays(420 + $index)->toDateString(),
                'old_status' => 'available',
                'new_status' => 'closed_by_host',
                'source_type' => 'bulk_demo_seed',
                'source_id' => $index + 1,
                'user_id' => $this->pick($userIds, $index),
                'note' => 'Bulk demo availability status log.',
            ],
        );

        $this->seedFactoryRows(
            SleepingPlaceCalendarRule::class,
            fn (int $index): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceIds, $index),
                'priority' => $index % 10,
            ],
        );

        $this->seedFactoryRows(
            SleepingPlacePhoto::class,
            fn (int $index): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceIds, $index),
                'uploaded_by_user_id' => $this->pick($userIds, $index),
                'path' => sprintf('bulk-demo/sleeping-place-photos/%04d.jpg', $index + 1),
                'thumbnail_path' => sprintf('bulk-demo/sleeping-place-photos/%04d-thumb.jpg', $index + 1),
                'caption' => sprintf('Bulk sleeping place photo %04d', $index + 1),
                'sort_order' => $index,
                'is_primary' => $index % 5 === 0,
                'is_main' => $index % 5 === 0,
            ],
        );

        $this->seedFactoryRows(
            SleepingPlaceCreationBatch::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'property_id' => $this->pick($roomRows, $index)['property_id'],
                'room_id' => $this->pick($roomRows, $index)['id'],
                'batch_name' => sprintf('Bulk sleeping-place batch %04d', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            SleepingPlaceTemplate::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'name' => sprintf('Bulk sleeping place template %04d', $index + 1),
                'is_default' => $index % 20 === 0,
            ],
        );
    }

    private function seedListingTranslations(): void
    {
        $this->seedTranslationsFor(
            CountryTranslation::class,
            'country_id',
            $this->ids(Country::class),
            fn (int $countryId, string $locale, int $index): array => [
                'country_id' => $countryId,
                'locale' => $locale,
                'name' => $this->localizedSeedText('Bulk country', $locale, $index),
                'name_normalized' => Str::lower($this->localizedSeedText('Bulk country', $this->fallbackLocale(), $index)),
                'source' => 'bulk_demo_seed',
                'source_id' => (string) $countryId,
                'is_preferred' => true,
            ],
        );

        $this->seedTranslationsFor(
            CityTranslation::class,
            'city_id',
            $this->ids(City::class),
            fn (int $cityId, string $locale, int $index): array => [
                'city_id' => $cityId,
                'locale' => $locale,
                'name' => $this->localizedSeedText('Bulk city', $locale, $index),
                'name_normalized' => Str::lower($this->localizedSeedText('Bulk city', $this->fallbackLocale(), $index)),
                'source' => 'bulk_demo_seed',
                'source_id' => (string) $cityId,
                'is_preferred' => true,
            ],
        );

        $this->seedTranslationsFor(
            PropertyTranslation::class,
            'property_id',
            $this->ids(Property::class),
            fn (int $propertyId, string $locale, int $index): array => [
                'property_id' => $propertyId,
                'locale' => $locale,
                'title' => $this->localizedSeedText('Bulk property', $locale, $index),
                'summary' => $this->localizedSeedText('Short property summary', $locale, $index),
                'description' => $this->localizedSeedText('Property description', $locale, $index),
                'getting_there' => $this->localizedSeedText('Transport note', $locale, $index),
                'what_to_know' => $this->localizedSeedText('Guest note', $locale, $index),
                'suitable_for' => $this->localizedSeedText('Suitable guest note', $locale, $index),
                'not_suitable_for' => $this->localizedSeedText('Unsuitable guest note', $locale, $index),
                'check_in_instructions' => $this->localizedSeedText('Check in instructions', $locale, $index),
                'check_out_instructions' => $this->localizedSeedText('Check out instructions', $locale, $index),
                'house_rules_text' => $this->localizedSeedText('House rules', $locale, $index),
                'safety_notes' => $this->localizedSeedText('Safety notes', $locale, $index),
            ],
        );

        $this->seedTranslationsFor(
            RoomTranslation::class,
            'room_id',
            $this->ids(Room::class),
            fn (int $roomId, string $locale, int $index): array => [
                'room_id' => $roomId,
                'locale' => $locale,
                'title' => $this->localizedSeedText('Bulk room', $locale, $index),
                'summary' => $this->localizedSeedText('Room summary', $locale, $index),
                'description' => $this->localizedSeedText('Room description', $locale, $index),
                'sleeping_arrangement' => $this->localizedSeedText('Sleeping arrangement', $locale, $index),
                'privacy_notes' => $this->localizedSeedText('Privacy notes', $locale, $index),
                'notes' => $this->localizedSeedText('Room notes', $locale, $index),
            ],
        );

        $this->seedTranslationsFor(
            SleepingPlaceTranslation::class,
            'sleeping_place_id',
            $this->ids(SleepingPlace::class),
            fn (int $sleepingPlaceId, string $locale, int $index): array => [
                'sleeping_place_id' => $sleepingPlaceId,
                'locale' => $locale,
                'title' => $this->localizedSeedText('Bulk sleeping place', $locale, $index),
                'summary' => $this->localizedSeedText('Sleeping place summary', $locale, $index),
                'description' => $this->localizedSeedText('Sleeping place description', $locale, $index),
                'special_conditions' => $this->localizedSeedText('Special conditions', $locale, $index),
                'privacy_notes' => $this->localizedSeedText('Privacy notes', $locale, $index),
                'accessibility_notes' => $this->localizedSeedText('Accessibility notes', $locale, $index),
            ],
        );
    }

    private function seedCompatibilityRecords(): void
    {
        $userIds = $this->ids(User::class);
        $sleepingPlaceRows = $this->sleepingPlaceRows();

        $this->seedFactoryRows(
            CompatibilityResult::class,
            function (int $index) use ($userIds, $sleepingPlaceRows): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);

                return [
                    'user_id' => $this->pick($userIds, $index),
                    'property_id' => $sleepingPlace['property_id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'check_in_date' => CarbonImmutable::now()->addDays(30 + ($index % 60))->toDateString(),
                    'check_out_date' => CarbonImmutable::now()->addDays(33 + ($index % 60))->toDateString(),
                ];
            },
        );
    }

    private function seedAvailabilityAndPricing(): void
    {
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $availabilityStart = CarbonImmutable::now()->addDays(180);

        AvailabilityDay::factory()
            ->count($this->missingFor(AvailabilityDay::class))
            ->sequence(function (Sequence $sequence) use ($sleepingPlaceRows, $availabilityStart): array {
                $cycle = intdiv($sequence->index, count($sleepingPlaceRows));

                return [
                    'sleeping_place_id' => $this->pick($sleepingPlaceRows, $sequence->index)['id'],
                    'date' => $availabilityStart->addDays($cycle)->toDateString(),
                    'status' => AvailabilityStatus::Available->value,
                ];
            })
            ->create();

        PriceRule::factory()
            ->count($this->missingFor(PriceRule::class))
            ->sequence(fn (Sequence $sequence): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $sequence->index)['id'],
            ])
            ->create();

        DiscountRule::factory()
            ->count($this->missingFor(DiscountRule::class))
            ->sequence(fn (Sequence $sequence): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $sequence->index)['id'],
            ])
            ->create();

        $sleepingPlaceIds = array_map(fn (array $row): int => (int) $row['id'], $sleepingPlaceRows);

        $this->seedMissingOwnedRows(
            SleepingPlacePricingSetting::class,
            'sleeping_place_id',
            $sleepingPlaceIds,
            fn (int $sleepingPlaceId, int $index): SleepingPlacePricingSetting => SleepingPlacePricingSetting::factory()->create([
                'sleeping_place_id' => $sleepingPlaceId,
                'base_nightly_price' => 20 + ($index % 30),
                'weekend_price' => 25 + ($index % 30),
                'cleaning_fee' => 10,
                'deposit_required' => true,
                'deposit_amount' => 50,
                'guest_service_fee_type' => SleepingPlacePricingSetting::FEE_PERCENT,
                'guest_service_fee_value' => 5,
            ]),
        );

        $this->seedFactoryRows(
            SleepingPlaceDatePrice::class,
            function (int $index) use ($sleepingPlaceRows): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);

                return [
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'date' => CarbonImmutable::now()->addDays(270 + $index)->toDateString(),
                    'price' => 28 + ($index % 20),
                    'currency' => 'EUR',
                    'price_type' => $index % 2 === 0 ? SleepingPlaceDatePrice::TYPE_MANUAL_OVERRIDE : SleepingPlaceDatePrice::TYPE_SEASONAL,
                ];
            },
        );

        $this->seedFactoryRows(
            SleepingPlaceDiscountRule::class,
            fn (int $index): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index)['id'],
                'discount_type' => $index % 3 === 0 ? SleepingPlaceDiscountRule::TYPE_MONTHLY : SleepingPlaceDiscountRule::TYPE_WEEKLY,
                'name' => sprintf('Bulk pricing discount %04d', $index + 1),
                'value_type' => SleepingPlaceDiscountRule::VALUE_PERCENT,
                'value' => $index % 3 === 0 ? 25 : 10,
                'min_nights' => $index % 3 === 0 ? 30 : 7,
                'priority' => $index % 3 === 0 ? 20 : 10,
            ],
        );

        $this->seedFactoryRows(
            PromoCode::class,
            function (int $index) use ($sleepingPlaceRows): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);

                return [
                    'code' => sprintf('BULK%06d', $index + 1),
                    'name' => sprintf('Bulk promo %04d', $index + 1),
                    'value_type' => PromoCode::VALUE_PERCENT,
                    'value' => 10,
                    'currency' => 'EUR',
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'property_id' => $sleepingPlace['property_id'],
                    'active' => true,
                ];
            },
        );
    }

    private function seedBookingQuotes(): void
    {
        $userIds = $this->ids(User::class);
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $quoteStart = BookingQuote::query()->count();

        $this->seedFactoryRows(
            BookingQuote::class,
            function (int $index) use ($userIds, $sleepingPlaceRows, $quoteStart): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);
                $checkIn = CarbonImmutable::now()->addDays(180 + ($index % 90))->startOfDay();
                $nights = 1 + ($index % 7);
                $checkOut = $checkIn->addDays($nights);
                $accommodation = 18 + ($nights * 12);
                $cleaningFee = 5;
                $deposit = 30;
                $serviceFee = round($accommodation * 0.05, 2);

                return [
                    'quote_number' => sprintf('QT-%s-%06d', $checkIn->format('Y'), $quoteStart + $index + 1),
                    'user_id' => $this->pick($userIds, $index),
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'property_id' => $sleepingPlace['property_id'],
                    'host_user_id' => $this->pick($userIds, $index + 1),
                    'check_in_date' => $checkIn->toDateString(),
                    'check_in_time' => '15:00',
                    'check_out_date' => $checkOut->toDateString(),
                    'check_out_time' => '11:00',
                    'nights_count' => $nights,
                    'chargeable_days_count' => $nights,
                    'calendar_presence_days_count' => $nights + 1,
                    'guests_count' => 1,
                    'included_guests_count' => 1,
                    'extra_guests_count' => 0,
                    'availability_status' => 'available',
                    'validation_status' => 'valid',
                    'pricing_status' => 'calculated',
                    'accommodation_amount' => $accommodation,
                    'cleaning_fee_amount' => $cleaningFee,
                    'service_fee_amount' => $serviceFee,
                    'deposit_amount' => $deposit,
                    'total_without_deposit' => $accommodation + $cleaningFee + $serviceFee,
                    'total_payable' => $accommodation + $cleaningFee + $serviceFee + $deposit,
                    'host_payout_preview_amount' => $accommodation + $cleaningFee,
                    'refundable_amount' => $deposit,
                    'non_refundable_amount' => $accommodation + $cleaningFee + $serviceFee,
                    'currency' => 'EUR',
                    'payment_deadline_at' => now()->addMinutes(20),
                    'expires_at' => now()->addMinutes(20),
                    'status' => BookingQuote::STATUS_VALID,
                ];
            },
        );

        $quoteIds = $this->ids(BookingQuote::class);

        $this->seedFactoryRows(
            BookingQuoteLine::class,
            fn (int $index): array => [
                'booking_quote_id' => $this->pick($quoteIds, $index),
                'line_type' => $index % 5 === 0 ? 'deposit' : 'night',
                'label_key' => $index % 5 === 0 ? 'booking_quotes.lines.deposit' : 'booking_quotes.lines.night',
                'date' => CarbonImmutable::now()->addDays(180 + ($index % 90))->toDateString(),
                'quantity' => 1,
                'unit_amount' => $index % 5 === 0 ? 30 : 20,
                'amount' => $index % 5 === 0 ? 30 : 20,
                'currency' => 'EUR',
                'is_deposit' => $index % 5 === 0,
                'is_refundable' => $index % 5 === 0,
                'sort_order' => $index % 10,
            ],
        );

        $this->seedFactoryRows(
            BookingQuoteValidationResult::class,
            fn (int $index): array => [
                'booking_quote_id' => $this->pick($quoteIds, $index),
                'validation_key' => 'host_confirmation_required',
                'severity' => 'info',
                'message_key' => 'booking_dates.validation.host_confirmation_required',
                'message_params_json' => [],
                'blocking' => false,
                'visible_to_guest' => true,
                'visible_to_host' => false,
            ],
        );

        $this->seedFactoryRows(
            BookingTimelineDate::class,
            fn (int $index): array => [
                'booking_quote_id' => $this->pick($quoteIds, $index),
                'event_key' => 'payment_deadline',
                'scheduled_at' => now()->addMinutes(20 + ($index % 60)),
                'status' => 'pending',
            ],
        );

        $this->seedFactoryRows(
            BookingQuoteSuggestion::class,
            function (int $index) use ($quoteIds, $sleepingPlaceRows): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);
                $checkIn = CarbonImmutable::now()->addDays(210 + ($index % 90));

                return [
                    'booking_quote_id' => $this->pick($quoteIds, $index),
                    'suggestion_type' => 'nearest_dates',
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'property_id' => $sleepingPlace['property_id'],
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkIn->addDays(3)->toDateString(),
                    'nights_count' => 3,
                    'price_preview_amount' => 68,
                    'currency' => 'EUR',
                    'message_key' => 'booking_quotes.suggestions.nearest_dates',
                    'sort_order' => $index % 10,
                ];
            },
        );
    }

    private function seedBookings(): void
    {
        $userIds = $this->ids(User::class);
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $bedIds = $this->ids(Bed::class);
        $bookingStart = Booking::query()->count();

        Booking::factory()
            ->count($this->missingFor(Booking::class))
            ->sequence(function (Sequence $sequence) use ($userIds, $sleepingPlaceRows, $bedIds, $bookingStart): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $sequence->index);
                $guestId = $this->pick($userIds, $sequence->index);
                $hostId = $this->pick($userIds, $sequence->index + 1);
                $number = $bookingStart + $sequence->index + 1;

                return [
                    'reference' => sprintf('RTG-BULK-%06d', $number),
                    'bed_id' => $this->pick($bedIds, $sequence->index),
                    'guest_id' => $guestId,
                    'guest_user_id' => $guestId,
                    'host_id' => $hostId,
                    'host_user_id' => $hostId,
                    'property_id' => $sleepingPlace['property_id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'sleeping_place_id' => $sleepingPlace['id'],
                ];
            })
            ->create();
    }

    private function seedBookingChildren(): void
    {
        $bookingRows = $this->bookingRows();
        $userIds = $this->ids(User::class);

        BookingGuest::factory()
            ->count($this->missingFor(BookingGuest::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
                'user_id' => $this->pick($userIds, $sequence->index),
            ])
            ->create();

        BookingPriceLine::factory()
            ->count($this->missingFor(BookingPriceLine::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
            ])
            ->create();

        $bookingIds = array_map(fn (array $row): int => (int) $row['id'], $bookingRows);
        $quoteIds = $this->ids(BookingQuote::class);

        $this->seedMissingOwnedRows(
            BookingPriceSnapshot::class,
            'booking_id',
            $bookingIds,
            fn (int $bookingId, int $index): BookingPriceSnapshot => BookingPriceSnapshot::factory()->create([
                'booking_id' => $bookingId,
                'booking_quote_id' => $this->pick($quoteIds, $index),
            ]),
        );

        $promoCodeIds = $this->ids(PromoCode::class);

        $this->seedFactoryRows(
            PromoCodeRedemption::class,
            fn (int $index): array => [
                'promo_code_id' => $this->pick($promoCodeIds, $index),
                'user_id' => $this->pick($userIds, $index),
                'booking_quote_id' => $this->pick($quoteIds, $index),
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'discount_amount' => 5,
                'currency' => 'EUR',
                'redeemed_at' => now()->subDays($index % 30),
            ],
        );

        BookingStatusHistory::factory()
            ->count($this->missingFor(BookingStatusHistory::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
                'changed_by_user_id' => $this->pick($userIds, $sequence->index),
            ])
            ->create();

        BookingExtension::factory()
            ->count($this->missingFor(BookingExtension::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
            ])
            ->create();

        PaymentRecord::factory()
            ->count($this->missingFor(PaymentRecord::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
                'payer_user_id' => $this->pick($userIds, $sequence->index),
            ])
            ->create();

        DepositRecord::factory()
            ->count($this->missingFor(DepositRecord::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
            ])
            ->create();

        RefundRequest::factory()
            ->count($this->missingFor(RefundRequest::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
                'requested_by_user_id' => $this->pick($userIds, $sequence->index),
            ])
            ->create();

        Payout::factory()
            ->count($this->missingFor(Payout::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
                'host_id' => $this->pick($userIds, $sequence->index + 1),
                'status' => PayoutStatus::Pending->value,
            ])
            ->create();

        $this->seedCheckinRecords($bookingRows);
        $this->seedCheckoutRecords($bookingRows);
    }

    private function seedBookingLifecycleRecords(): void
    {
        $bookingRows = $this->bookingRows();
        $bookingIds = array_column($bookingRows, 'id');

        $this->seedFactoryRows(
            BookingGuestIntake::class,
            function (int $index) use ($bookingRows): array {
                $booking = $this->pick($bookingRows, $index);

                return [
                    'booking_id' => $booking['id'],
                    'user_id' => $booking['guest_user_id'],
                    'guest_user_id' => $booking['guest_user_id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'status' => $index % 3 === 0 ? 'draft' : 'completed',
                ];
            },
        );

        $this->seedFactoryRows(
            BookingReviewRequest::class,
            fn (int $index): array => [
                'booking_id' => $this->pick($bookingIds, $index),
                'reviewer_user_id' => $this->pick($bookingRows, $index)['guest_user_id'],
                'reviewee_user_id' => $this->pick($bookingRows, $index)['host_user_id'],
                'reviewer_role' => $index % 2 === 0 ? 'guest' : 'host',
            ],
        );

        $this->seedMissingOwnedRows(
            BookingCheckIn::class,
            'booking_id',
            $bookingIds,
            fn (int $bookingId, int $index): BookingCheckIn => BookingCheckIn::factory()->create($this->bookingState($this->pick($bookingRows, $index), [
                'booking_id' => $bookingId,
                'check_in_date' => $this->pick($bookingRows, $index)['check_in_date'],
            ])),
        );

        $this->seedMissingOwnedRows(
            BookingCheckOut::class,
            'booking_id',
            $bookingIds,
            fn (int $bookingId, int $index): BookingCheckOut => BookingCheckOut::factory()->create($this->bookingState($this->pick($bookingRows, $index), [
                'booking_id' => $bookingId,
                'check_out_date' => $this->pick($bookingRows, $index)['check_out_date'],
            ])),
        );

        $checkInRows = $this->bookingCheckInRows();
        $checkOutRows = $this->bookingCheckOutRows();

        $this->seedFactoryRows(
            BookingCheckInChecklistItem::class,
            fn (int $index): array => [
                'booking_check_in_id' => $this->pick($checkInRows, $index)['id'],
                'item_key' => ['keys_handed_over', 'room_shown', 'rules_explained'][$index % 3],
                'label_key' => 'check_in.checklist.'.(['keys_handed_over', 'room_shown', 'rules_explained'][$index % 3]),
            ],
        );

        $this->seedFactoryRows(
            BookingCheckInAlert::class,
            fn (int $index): array => $this->bookingCheckInState($this->pick($checkInRows, $index)),
        );

        $this->seedFactoryRows(
            BookingCheckInProblemReport::class,
            fn (int $index): array => $this->bookingCheckInState($this->pick($checkInRows, $index)),
        );

        $this->seedFactoryRows(
            BookingCheckOutChecklistItem::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'item_key' => ['keys_returned', 'locker_emptied', 'room_checked'][$index % 3],
                'label_key' => 'check_out.checklist.'.(['keys_returned', 'locker_emptied', 'room_checked'][$index % 3]),
            ],
        );

        $this->seedFactoryRows(
            BookingCheckOutIssueReport::class,
            fn (int $index): array => $this->bookingCheckOutState($this->pick($checkOutRows, $index)),
        );

        $this->seedFactoryRows(
            BookingDepositDecision::class,
            fn (int $index): array => $this->bookingCheckOutState($this->pick($checkOutRows, $index)),
        );

        $this->seedFactoryRows(
            BookingForgottenItem::class,
            fn (int $index): array => $this->bookingCheckOutState($this->pick($checkOutRows, $index), [
                'item_name' => sprintf('Bulk forgotten item %04d', $index + 1),
            ]),
        );
    }

    private function seedLegacyAvailabilityAndWaitlists(): void
    {
        $bedIds = $this->ids(Bed::class);
        $userIds = $this->ids(User::class);
        $sleepingPlaceIds = $this->ids(SleepingPlace::class);
        $date = CarbonImmutable::now()->addDays(180)->toDateString();

        $missingBedAvailability = $this->missingFor(BedAvailability::class);

        for ($index = 0; $index < $missingBedAvailability; $index++) {
            BedAvailability::query()->create([
                'bed_id' => $this->pick($bedIds, $index),
                'date' => $date,
                'status' => 'available',
                'price_override' => null,
                'note' => null,
            ]);
        }

        $missingWaitlistEntries = $this->missingFor(WaitlistEntry::class);

        for ($index = 0; $index < $missingWaitlistEntries; $index++) {
            WaitlistEntry::query()->create([
                'user_id' => $this->pick($userIds, $index),
                'bed_id' => $this->pick($bedIds, $index),
                'desired_check_in' => CarbonImmutable::now()->addWeeks(3)->toDateString(),
                'desired_check_out' => CarbonImmutable::now()->addWeeks(3)->addDays(5)->toDateString(),
                'max_price' => 45,
                'ready_to_book' => true,
                'auto_request' => false,
                'notified' => false,
                'notified_at' => null,
                'status' => 'waiting',
            ]);
        }

        $missingWaitlistItems = $this->missingFor(WaitlistItem::class);
        $existingPairs = WaitlistItem::query()
            ->select(['user_id', 'sleeping_place_id'])
            ->get()
            ->mapWithKeys(fn (WaitlistItem $item): array => [$item->user_id.':'.$item->sleeping_place_id => true])
            ->all();
        $createdItems = 0;

        for ($index = 0; $createdItems < $missingWaitlistItems; $index++) {
            $userId = $this->pick($userIds, $index);
            $sleepingPlaceId = $this->pick($sleepingPlaceIds, $index);
            $key = $userId.':'.$sleepingPlaceId;

            if (isset($existingPairs[$key])) {
                continue;
            }

            WaitlistItem::factory()->create([
                'user_id' => $userId,
                'sleeping_place_id' => $sleepingPlaceId,
            ]);

            $existingPairs[$key] = true;
            $createdItems++;
        }
    }

    private function seedMessaging(): void
    {
        $userIds = $this->ids(User::class);
        $bookingRows = $this->bookingRows();
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $bedIds = $this->ids(Bed::class);

        $this->seedConversations($bookingRows, $userIds, $bedIds);

        $this->seedMessageThreads($bookingRows, $sleepingPlaceRows, $userIds);

        $conversationIds = $this->ids(Conversation::class);
        $threadIds = $this->ids(MessageThread::class);

        Message::factory()
            ->count($this->missingFor(Message::class))
            ->sequence(function (Sequence $sequence) use ($conversationIds, $threadIds, $userIds, $bookingRows, $sleepingPlaceRows): array {
                $senderId = $this->pick($userIds, $sequence->index);
                $recipientId = $this->pick($userIds, $sequence->index + 1);
                $booking = $this->pick($bookingRows, $sequence->index);
                $sleepingPlace = $this->pick($sleepingPlaceRows, $sequence->index);

                return [
                    'conversation_id' => $this->pick($conversationIds, $sequence->index),
                    'thread_id' => $this->pick($threadIds, $sequence->index),
                    'sender_id' => $senderId,
                    'sender_user_id' => $senderId,
                    'recipient_user_id' => $recipientId,
                    'booking_id' => $booking['id'],
                    'property_id' => $sleepingPlace['property_id'],
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'body' => sprintf('Bulk demo message %04d', $sequence->index + 1),
                ];
            })
            ->create();
    }

    /**
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $bookingRows
     * @param  list<int>  $userIds
     * @param  list<int>  $bedIds
     */
    private function seedConversations(array $bookingRows, array $userIds, array $bedIds): void
    {
        $missing = $this->missingFor(Conversation::class);
        $existingKeys = Conversation::query()
            ->select(['participant_one_id', 'participant_two_id', 'booking_id'])
            ->get()
            ->mapWithKeys(fn (Conversation $conversation): array => [
                $conversation->participant_one_id.':'.$conversation->participant_two_id.':'.$conversation->booking_id => true,
            ])
            ->all();
        $created = 0;

        foreach ($bookingRows as $index => $booking) {
            if ($created >= $missing) {
                return;
            }

            $participantOneId = $this->pick($userIds, $index);
            $participantTwoId = $this->pick($userIds, $index + 1);
            $key = $participantOneId.':'.$participantTwoId.':'.$booking['id'];

            if (isset($existingKeys[$key])) {
                continue;
            }

            Conversation::factory()->create([
                'participant_one_id' => $participantOneId,
                'participant_two_id' => $participantTwoId,
                'booking_id' => $booking['id'],
                'bed_id' => $this->pick($bedIds, $index),
                'last_message_at' => now(),
            ]);

            $existingKeys[$key] = true;
            $created++;
        }
    }

    private function seedSocialRecords(): void
    {
        $userIds = $this->ids(User::class);
        $bedIds = $this->ids(Bed::class);
        $cityIds = $this->ids(City::class);
        $bookingRows = $this->bookingRows();
        $sleepingPlaceRows = $this->sleepingPlaceRows();

        Favorite::factory()
            ->count($this->missingFor(Favorite::class))
            ->sequence(fn (Sequence $sequence): array => [
                'user_id' => $this->pick($userIds, $sequence->index),
                'bed_id' => $this->pick($bedIds, $sequence->index),
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $sequence->index)['id'],
            ])
            ->create();

        SavedSearch::factory()
            ->count($this->missingFor(SavedSearch::class))
            ->sequence(fn (Sequence $sequence): array => [
                'user_id' => $this->pick($userIds, $sequence->index),
                'city_id' => $this->pick($cityIds, $sequence->index),
                'name' => sprintf('Bulk search %04d', $sequence->index + 1),
                'locale' => $sequence->index % 2 === 0 ? 'en' : 'ru',
            ])
            ->create();

        $this->seedReviews($bookingRows, $sleepingPlaceRows, $userIds, $bedIds);

        Complaint::factory()
            ->count($this->missingFor(Complaint::class))
            ->sequence(function (Sequence $sequence) use ($userIds, $bedIds, $bookingRows, $sleepingPlaceRows): array {
                $booking = $this->pick($bookingRows, $sequence->index);
                $sleepingPlace = $this->pick($sleepingPlaceRows, $sequence->index);

                return [
                    'booking_id' => $booking['id'],
                    'reporter_id' => $this->pick($userIds, $sequence->index),
                    'reporter_user_id' => $this->pick($userIds, $sequence->index),
                    'reported_user_id' => $this->pick($userIds, $sequence->index + 1),
                    'property_id' => $sleepingPlace['property_id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'bed_id' => $this->pick($bedIds, $sequence->index),
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'type' => ComplaintType::Other->value,
                    'status' => ComplaintStatus::Created->value,
                ];
            })
            ->create();

        $complaintIds = $this->ids(Complaint::class);

        ComplaintStatusHistory::factory()
            ->count($this->missingFor(ComplaintStatusHistory::class))
            ->sequence(fn (Sequence $sequence): array => [
                'complaint_id' => $this->pick($complaintIds, $sequence->index),
                'actor_user_id' => $this->pick($userIds, $sequence->index),
                'status' => ComplaintStatus::Created->value,
            ])
            ->create();
    }

    private function seedDecisionAndQueueRecords(): void
    {
        $userIds = $this->ids(User::class);
        $cityIds = $this->ids(City::class);
        $savedSearchIds = $this->ids(SavedSearch::class);
        $waitlistItemRows = $this->waitlistItemRows();
        $sleepingPlaceRows = $this->sleepingPlaceRows();

        $this->seedFactoryRows(
            FavoriteCollection::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'title' => sprintf('Bulk favorites %04d', $index + 1),
                'slug' => sprintf('bulk-favorites-%04d', $index + 1),
                'city_id' => $this->pick($cityIds, $index),
            ],
        );

        $this->seedSavedSearchResults($savedSearchIds, $sleepingPlaceRows);

        $this->seedFactoryRows(
            WaitlistOffer::class,
            function (int $index) use ($waitlistItemRows): array {
                $item = $this->pick($waitlistItemRows, $index);

                return [
                    'waitlist_item_id' => $item['id'],
                    'user_id' => $item['user_id'],
                    'property_id' => $item['property_id'],
                    'room_id' => $item['room_id'],
                    'sleeping_place_id' => $item['sleeping_place_id'],
                ];
            },
        );

        $this->seedFactoryRows(
            GuestHintImpression::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index)['id'],
                'hint_key' => sprintf('bulk_guest_hint_%03d', $index % 50),
            ],
        );

        $this->seedFactoryRows(
            GuestHintDismissal::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index)['id'],
                'hint_key' => sprintf('bulk_guest_hint_%03d', $index % 50),
            ],
        );

        $this->seedFactoryRows(
            ListingHintSnapshot::class,
            function (int $index) use ($sleepingPlaceRows): array {
                $place = $this->pick($sleepingPlaceRows, $index);

                return [
                    'sleeping_place_id' => $place['id'],
                    'property_id' => $place['property_id'],
                    'room_id' => $place['room_id'],
                    'hint_key' => sprintf('bulk_listing_hint_%03d', $index % 50),
                ];
            },
        );
    }

    private function seedHostOperationalRecords(): void
    {
        $userIds = $this->ids(User::class);
        $bookingRows = $this->bookingRows();
        $sleepingPlaceRows = $this->sleepingPlaceRows();

        $this->seedFactoryRows(
            HostBulkActionBatch::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'selected_count' => 1,
                'affected_count' => $index % 3 === 0 ? 0 : 1,
            ],
        );

        $batchIds = $this->ids(HostBulkActionBatch::class);

        $this->seedFactoryRows(
            HostBulkActionItem::class,
            fn (int $index): array => [
                'batch_id' => $this->pick($batchIds, $index),
                'target_id' => $this->pick($sleepingPlaceRows, $index)['id'],
            ],
        );

        $this->seedFactoryRows(
            HostBulkActionLog::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'batch_id' => $this->pick($batchIds, $index),
                'target_id' => $this->pick($sleepingPlaceRows, $index)['id'],
            ],
        );

        $this->seedFactoryRows(
            HostCalendarEvent::class,
            function (int $index) use ($userIds, $bookingRows, $sleepingPlaceRows): array {
                $booking = $this->pick($bookingRows, $index);
                $place = $this->pick($sleepingPlaceRows, $index);

                return [
                    'user_id' => $this->pick($userIds, $index),
                    'property_id' => $place['property_id'],
                    'room_id' => $place['room_id'],
                    'sleeping_place_id' => $place['id'],
                    'booking_id' => $booking['id'],
                    'event_type' => $index % 2 === 0 ? 'booking' : 'note',
                    'guest_user_id' => $booking['guest_user_id'],
                ];
            },
        );

        $this->seedFactoryRows(
            HostCalendarNote::class,
            function (int $index) use ($userIds, $bookingRows, $sleepingPlaceRows): array {
                $booking = $this->pick($bookingRows, $index);
                $place = $this->pick($sleepingPlaceRows, $index);

                return [
                    'user_id' => $this->pick($userIds, $index),
                    'property_id' => $place['property_id'],
                    'room_id' => $place['room_id'],
                    'sleeping_place_id' => $place['id'],
                    'booking_id' => $booking['id'],
                    'note' => sprintf('Bulk host calendar note %04d', $index + 1),
                ];
            },
        );

        $this->seedMissingOwnedRows(
            HostCalendarViewSetting::class,
            'user_id',
            $userIds,
            fn (int $userId, int $index): HostCalendarViewSetting => HostCalendarViewSetting::factory()->create([
                'user_id' => $userId,
                'default_property_id' => $this->pick($sleepingPlaceRows, $index)['property_id'],
                'default_room_id' => $this->pick($sleepingPlaceRows, $index)['room_id'],
            ]),
        );

        $this->seedFactoryRows(
            HostCurrentStaySnapshot::class,
            function (int $index) use ($bookingRows): array {
                $booking = $this->pick($bookingRows, $index);

                return [
                    'user_id' => $booking['host_user_id'],
                    'guest_user_id' => $booking['guest_user_id'],
                    'booking_id' => $booking['id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'booking_total_amount' => 140 + ($index % 30),
                ];
            },
        );

        $this->seedFactoryRows(
            RoomOccupantSnapshot::class,
            fn (int $index): array => [
                'room_id' => $this->pick($bookingRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($bookingRows, $index)['sleeping_place_id'],
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'user_id' => $this->pick($bookingRows, $index)['guest_user_id'],
                'public_alias_snapshot' => sprintf('Bulk guest %04d', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            HostGuestStayFlag::class,
            fn (int $index): array => [
                'user_id' => $this->pick($bookingRows, $index)['host_user_id'],
                'guest_user_id' => $this->pick($bookingRows, $index)['guest_user_id'],
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'flag_key' => sprintf('bulk_flag_%03d', $index % 50),
                'message_key' => sprintf('current_occupants.flags.bulk_flag_%03d', $index % 50),
            ],
        );

        $this->seedFactoryRows(
            HostGuestStayNote::class,
            function (int $index) use ($bookingRows): array {
                $booking = $this->pick($bookingRows, $index);

                return [
                    'user_id' => $booking['host_user_id'],
                    'guest_user_id' => $booking['guest_user_id'],
                    'booking_id' => $booking['id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'note' => sprintf('Bulk stay note %04d', $index + 1),
                ];
            },
        );

        $this->seedFactoryRows(
            HostCleaningTemplate::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'name' => sprintf('Bulk cleaning template %04d', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            HostCleaningTask::class,
            function (int $index) use ($bookingRows): array {
                $booking = $this->pick($bookingRows, $index);

                return [
                    'user_id' => $booking['host_user_id'],
                    'booking_id' => $booking['id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                ];
            },
        );

        $cleaningTaskIds = $this->ids(HostCleaningTask::class);
        $checkOutRows = $this->bookingCheckOutRows();

        $this->seedFactoryRows(
            HostCleaningTaskItem::class,
            fn (int $index): array => [
                'host_cleaning_task_id' => $this->pick($cleaningTaskIds, $index),
                'item_key' => sprintf('bulk_cleaning_item_%03d', $index % 50),
                'label_key' => sprintf('cleaning.checklist.bulk_cleaning_item_%03d', $index % 50),
            ],
        );

        $this->seedFactoryRows(
            HostCleaningTaskPhoto::class,
            fn (int $index): array => [
                'host_cleaning_task_id' => $this->pick($cleaningTaskIds, $index),
                'uploaded_by_user_id' => $this->pick($userIds, $index),
                'path' => sprintf('bulk-demo/cleaning/%04d-after.jpg', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            HostCleaningFinding::class,
            fn (int $index): array => [
                'host_cleaning_task_id' => $this->pick($cleaningTaskIds, $index),
                'booking_id' => $this->pick($bookingRows, $index)['id'],
            ],
        );

        $this->seedFactoryRows(
            HostInspectionTask::class,
            function (int $index) use ($bookingRows, $checkOutRows): array {
                $booking = $this->pick($bookingRows, $index);

                return [
                    'user_id' => $booking['host_user_id'],
                    'booking_id' => $booking['id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                ];
            },
        );

        $this->seedFactoryRows(
            HostHintSnapshot::class,
            function (int $index) use ($userIds, $sleepingPlaceRows): array {
                $place = $this->pick($sleepingPlaceRows, $index);

                return [
                    'user_id' => $this->pick($userIds, $index),
                    'property_id' => $place['property_id'],
                    'room_id' => $place['room_id'],
                    'sleeping_place_id' => $place['id'],
                    'hint_key' => sprintf('bulk_host_hint_%03d', $index % 50),
                    'message_key' => sprintf('host_hints.messages.bulk_host_hint_%03d', $index % 50),
                ];
            },
        );

        $hintRows = $this->hostHintSnapshotRows();

        $this->seedFactoryRows(
            HostHintDismissal::class,
            fn (int $index): array => [
                'user_id' => $this->pick($hintRows, $index)['user_id'],
                'property_id' => $this->pick($hintRows, $index)['property_id'],
                'room_id' => $this->pick($hintRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($hintRows, $index)['sleeping_place_id'],
                'hint_key' => $this->pick($hintRows, $index)['hint_key'],
            ],
        );

        $this->seedFactoryRows(
            HostHintAction::class,
            fn (int $index): array => [
                'user_id' => $this->pick($hintRows, $index)['user_id'],
                'host_hint_snapshot_id' => $this->pick($hintRows, $index)['id'],
            ],
        );
    }

    private function seedListingWorkflowRecords(): void
    {
        $userIds = $this->ids(User::class);
        $propertyRows = $this->propertyRows();
        $sleepingPlaceRows = $this->sleepingPlaceRows();

        $this->seedFactoryRows(
            HostListingWizardSession::class,
            fn (int $index): array => [
                'user_id' => $this->pick($userIds, $index),
                'property_id' => $this->pick($propertyRows, $index)['id'],
                'current_step' => ['property', 'rooms', 'sleeping_places', 'photos'][$index % 4],
            ],
        );

        $this->seedFactoryRows(
            ListingPublicationCheck::class,
            fn (int $index): array => $this->listingWorkflowState($this->pick($sleepingPlaceRows, $index), $this->pick($userIds, $index), [
                'check_key' => sprintf('bulk_publication_check_%03d', $index % 50),
                'message_key' => sprintf('listing_wizard.checks.bulk_publication_check_%03d', $index % 50),
            ]),
        );

        $this->seedFactoryRows(
            ListingCreationDraft::class,
            fn (int $index): array => $this->listingWorkflowState($this->pick($sleepingPlaceRows, $index), $this->pick($userIds, $index), [
                'draft_type' => ['property', 'room', 'sleeping_place', 'full_listing_wizard'][$index % 4],
                'current_step' => ['property', 'room', 'sleeping_place', 'photos'][$index % 4],
            ]),
        );

        $this->seedFactoryRows(
            ListingReadinessCheck::class,
            fn (int $index): array => $this->listingWorkflowState($this->pick($sleepingPlaceRows, $index), $this->pick($userIds, $index), [
                'check_key' => sprintf('bulk_readiness_check_%03d', $index % 50),
                'message_key' => sprintf('listing_readiness.messages.bulk_readiness_check_%03d', $index % 50),
            ]),
        );

        $this->seedFactoryRows(
            HostListingSuggestion::class,
            fn (int $index): array => $this->listingWorkflowState($this->pick($sleepingPlaceRows, $index), $this->pick($userIds, $index), [
                'suggestion_key' => sprintf('bulk_listing_suggestion_%03d', $index % 50),
                'message_key' => sprintf('listing_readiness.suggestions.bulk_listing_suggestion_%03d', $index % 50),
            ]),
        );
    }

    /**
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $bookingRows
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $sleepingPlaceRows
     * @param  list<int>  $userIds
     * @param  list<int>  $bedIds
     */
    private function seedReviews(array $bookingRows, array $sleepingPlaceRows, array $userIds, array $bedIds): void
    {
        $missing = $this->missingFor(Review::class);
        $existingKeys = Review::query()
            ->select(['booking_id', 'type'])
            ->get()
            ->mapWithKeys(fn (Review $review): array => [$review->booking_id.':'.$review->type->value => true])
            ->all();
        $reviewTypes = [ReviewType::GuestToPlace, ReviewType::HostToGuest];
        $created = 0;

        foreach ($bookingRows as $index => $booking) {
            foreach ($reviewTypes as $reviewType) {
                if ($created >= $missing) {
                    return;
                }

                $key = $booking['id'].':'.$reviewType->value;

                if (isset($existingKeys[$key])) {
                    continue;
                }

                $sleepingPlace = $this->pick($sleepingPlaceRows, $index);
                $guestId = $this->pick($userIds, $index);
                $hostId = $this->pick($userIds, $index + 1);

                Review::factory()->create([
                    'booking_id' => $booking['id'],
                    'reviewer_id' => $reviewType === ReviewType::GuestToPlace ? $guestId : $hostId,
                    'reviewee_id' => $reviewType === ReviewType::GuestToPlace ? $hostId : $guestId,
                    'guest_user_id' => $guestId,
                    'host_user_id' => $hostId,
                    'bed_id' => $this->pick($bedIds, $index),
                    'sleeping_place_id' => $sleepingPlace['id'],
                    'room_id' => $sleepingPlace['room_id'],
                    'property_id' => $sleepingPlace['property_id'],
                    'type' => $reviewType->value,
                    'status' => ReviewStatus::Published->value,
                ]);

                $existingKeys[$key] = true;
                $created++;
            }
        }
    }

    private function seedMediaAndNotifications(): void
    {
        $userIds = $this->ids(User::class);
        $propertyIds = $this->ids(Property::class);

        $mediaItems = MediaItem::factory()
            ->count($this->missingFor(MediaItem::class))
            ->sequence(function (Sequence $sequence) use ($userIds, $propertyIds): array {
                $propertyId = $this->pick($propertyIds, $sequence->index);

                return [
                    'owner_type' => Property::class,
                    'owner_id' => $propertyId,
                    'mediable_type' => Property::class,
                    'mediable_id' => $propertyId,
                    'owner_user_id' => $this->pick($userIds, $sequence->index),
                    'path' => sprintf('bulk-demo/properties/%04d.jpg', $sequence->index + 1),
                    'thumbnail_path' => sprintf('bulk-demo/properties/%04d-thumb.jpg', $sequence->index + 1),
                    'thumb_path' => sprintf('bulk-demo/properties/%04d-thumb.jpg', $sequence->index + 1),
                    'mobile_path' => sprintf('bulk-demo/properties/%04d-mobile.jpg', $sequence->index + 1),
                    'full_path' => sprintf('bulk-demo/properties/%04d-full.jpg', $sequence->index + 1),
                    'is_primary' => $sequence->index % 5 === 0,
                    'is_cover' => $sequence->index % 5 === 0,
                ];
            })
            ->create();

        $this->seedMediaTranslations($mediaItems);
        $this->seedMediaItemTranslations();
        $this->ensureBulkMediaFiles();

        Notification::factory()
            ->count($this->missingFor(Notification::class))
            ->sequence(function (Sequence $sequence) use ($userIds): array {
                $userId = $this->pick($userIds, $sequence->index);

                return [
                    'user_id' => $userId,
                    'notifiable_id' => $userId,
                    'notifiable_type' => User::class,
                    'data' => ['params' => ['reference' => sprintf('RTG-BULK-%04d', $sequence->index + 1)]],
                    'status' => $sequence->index % 4 === 0 ? 'read' : 'unread',
                    'read_at' => $sequence->index % 4 === 0 ? now() : null,
                ];
            })
            ->create();
    }

    private function ensureBulkMediaFiles(): void
    {
        $files = app(DemoMediaFileService::class);

        MediaItem::query()
            ->select([
                'id',
                'disk',
                'path',
                'thumbnail_path',
                'thumb_path',
                'mobile_path',
                'full_path',
                'width',
                'height',
                'alt_text',
            ])
            ->where('path', 'like', 'bulk-demo/%')
            ->chunkById(200, function ($mediaItems) use ($files): void {
                $files->ensureForMediaItems($mediaItems);
            });
    }

    private function seedMediaTranslations(iterable $mediaItems): void
    {
        foreach ($mediaItems as $index => $mediaItem) {
            if (! $mediaItem instanceof MediaItem) {
                continue;
            }

            foreach ($this->contentLocales() as $locale) {
                $mediaItem->translations()->firstOrCreate(
                    ['locale' => $locale],
                    ['caption' => $this->localizedSeedText('Bulk demo property photo', $locale, $index)],
                );
            }
        }
    }

    private function seedMediaItemTranslations(): void
    {
        $this->seedTranslationsFor(
            MediaItemTranslation::class,
            'media_item_id',
            $this->ids(MediaItem::class),
            fn (int $mediaItemId, string $locale, int $index): array => [
                'media_item_id' => $mediaItemId,
                'locale' => $locale,
                'caption' => $this->localizedSeedText('Bulk media caption', $locale, $index),
            ],
        );
    }

    private function seedAmenityTranslations(): void
    {
        $this->seedTranslationsFor(
            AmenityTranslation::class,
            'amenity_id',
            $this->ids(Amenity::class),
            fn (int $amenityId, string $locale, int $index): array => [
                'amenity_id' => $amenityId,
                'locale' => $locale,
                'name' => $this->localizedSeedText('Bulk amenity', $locale, $index),
                'name_normalized' => Str::lower($this->localizedSeedText('Bulk amenity', $this->fallbackLocale(), $index)),
                'description' => $this->localizedSeedText('Amenity description', $locale, $index),
            ],
        );
    }

    private function seedRuleTranslations(): void
    {
        $this->seedTranslationsFor(
            RuleTranslation::class,
            'rule_id',
            $this->ids(Rule::class),
            fn (int $ruleId, string $locale, int $index): array => [
                'rule_id' => $ruleId,
                'locale' => $locale,
                'name' => $this->localizedSeedText('Bulk rule', $locale, $index),
                'name_normalized' => Str::lower($this->localizedSeedText('Bulk rule', $this->fallbackLocale(), $index)),
                'description' => $this->localizedSeedText('Rule description', $locale, $index),
            ],
        );
    }

    /**
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $bookingRows
     */
    private function seedCheckinRecords(array $bookingRows): void
    {
        $missing = $this->missingFor(CheckinRecord::class);

        for ($index = 0; $index < $missing; $index++) {
            CheckinRecord::query()->create([
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'planned_time' => '15:00',
                'actual_arrival_at' => now(),
                'met_by' => 'host',
                'property_found' => true,
                'keys_handed' => true,
                'room_shown' => true,
                'rules_explained' => true,
                'linen_provided' => true,
                'towel_provided' => true,
                'keys_received' => true,
                'code_received' => false,
                'sleeping_place_shown' => true,
                'everything_ok' => true,
                'locker_assigned' => true,
                'photos_before' => [],
                'guest_confirmed' => true,
                'host_confirmed' => true,
                'guest_confirmed_at' => now(),
                'host_confirmed_at' => now(),
                'has_issue' => false,
                'status' => 'confirmed',
                'problem_reported' => false,
                'problem_media' => [],
            ]);
        }
    }

    /**
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $bookingRows
     */
    private function seedCheckoutRecords(array $bookingRows): void
    {
        $missing = $this->missingFor(CheckoutRecord::class);

        for ($index = 0; $index < $missing; $index++) {
            CheckoutRecord::query()->create([
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'planned_time' => '11:00',
                'actual_departure_at' => now(),
                'planned_checkout_time' => '11:00',
                'actual_checkout_at' => now(),
                'keys_returned' => true,
                'locker_emptied' => true,
                'belongings_collected' => true,
                'belongings_removed' => true,
                'linen_returned' => true,
                'place_clean' => true,
                'has_damage' => false,
                'no_damage' => true,
                'damage_found' => false,
                'has_extra_dirt' => false,
                'has_forgotten_items' => false,
                'deposit_withheld' => false,
                'deposit_action' => 'release',
                'withhold_amount' => 0,
                'photos_after' => [],
                'damage_media' => [],
                'guest_confirmed' => true,
                'host_confirmed' => true,
                'guest_confirmed_checkout_at' => now(),
                'host_confirmed_checkout_at' => now(),
                'status' => 'confirmed',
            ]);
        }
    }

    /**
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $bookingRows
     * @param  array<int, array{id:int, property_id:int, room_id:int}>  $sleepingPlaceRows
     * @param  list<int>  $userIds
     */
    private function seedMessageThreads(array $bookingRows, array $sleepingPlaceRows, array $userIds): void
    {
        $missing = $this->missingFor(MessageThread::class);
        $existingBookingIds = MessageThread::query()
            ->whereNotNull('booking_id')
            ->pluck('booking_id')
            ->all();
        $created = 0;

        foreach ($bookingRows as $index => $booking) {
            if ($created >= $missing) {
                return;
            }

            if (in_array($booking['id'], $existingBookingIds, true)) {
                continue;
            }

            $sleepingPlace = $this->pick($sleepingPlaceRows, $index);
            $guestId = $this->pick($userIds, $index);
            $hostId = $this->pick($userIds, $index + 1);

            MessageThread::factory()->create([
                'guest_user_id' => $guestId,
                'host_user_id' => $hostId,
                'booking_id' => $booking['id'],
                'property_id' => $sleepingPlace['property_id'],
                'sleeping_place_id' => $sleepingPlace['id'],
                'last_message_at' => now(),
                'status' => 'open',
            ]);

            $created++;
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  callable(int): array<string, mixed>  $state
     */
    private function seedFactoryRows(string $modelClass, callable $state): void
    {
        $missing = $this->missingFor($modelClass);

        if ($missing === 0) {
            return;
        }

        $start = $modelClass::query()->count();

        $modelClass::factory()
            ->count($missing)
            ->sequence(fn (Sequence $sequence): array => $state($start + $sequence->index))
            ->create();
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  list<int>  $ownerIds
     * @param  callable(int, int): TModel  $create
     */
    private function seedMissingOwnedRows(string $modelClass, string $ownerColumn, array $ownerIds, callable $create): void
    {
        $missing = $this->missingFor($modelClass);
        $existingOwnerIds = $modelClass::query()
            ->whereNotNull($ownerColumn)
            ->pluck($ownerColumn)
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
        $created = 0;

        foreach ($ownerIds as $index => $ownerId) {
            if ($created >= $missing) {
                return;
            }

            if (in_array($ownerId, $existingOwnerIds, true)) {
                continue;
            }

            $create($ownerId, $index);
            $existingOwnerIds[] = $ownerId;
            $created++;
        }
    }

    /**
     * @param  list<int>  $sleepingPlaceIds
     */
    private function seedSleepingPlaceCalendarDays(array $sleepingPlaceIds): void
    {
        $missing = $this->missingFor(SleepingPlaceCalendarDay::class);
        $existing = SleepingPlaceCalendarDay::query()
            ->select(['sleeping_place_id', 'date'])
            ->get()
            ->mapWithKeys(fn (SleepingPlaceCalendarDay $day): array => [
                $day->sleeping_place_id.':'.$day->date->toDateString() => true,
            ])
            ->all();
        $start = CarbonImmutable::now()->addDays(240);
        $created = 0;

        for ($index = 0; $created < $missing; $index++) {
            $sleepingPlaceId = $this->pick($sleepingPlaceIds, $index);
            $date = $start->addDays(intdiv($index, count($sleepingPlaceIds)))->toDateString();
            $key = $sleepingPlaceId.':'.$date;

            if (isset($existing[$key])) {
                continue;
            }

            SleepingPlaceCalendarDay::factory()->create([
                'sleeping_place_id' => $sleepingPlaceId,
                'date' => $date,
                'source' => 'bulk_demo_seed',
            ]);

            $existing[$key] = true;
            $created++;
        }
    }

    /**
     * @param  list<int>  $savedSearchIds
     * @param  list<array{id:int,property_id:int,room_id:int}>  $sleepingPlaceRows
     */
    private function seedSavedSearchResults(array $savedSearchIds, array $sleepingPlaceRows): void
    {
        $missing = $this->missingFor(SavedSearchResult::class);
        $existing = SavedSearchResult::query()
            ->select(['saved_search_id', 'sleeping_place_id'])
            ->get()
            ->mapWithKeys(fn (SavedSearchResult $result): array => [
                $result->saved_search_id.':'.$result->sleeping_place_id => true,
            ])
            ->all();
        $created = 0;

        for ($index = 0; $created < $missing; $index++) {
            $savedSearchId = $this->pick($savedSearchIds, $index);
            $sleepingPlace = $this->pick($sleepingPlaceRows, $index);
            $key = $savedSearchId.':'.$sleepingPlace['id'];

            if (isset($existing[$key])) {
                continue;
            }

            SavedSearchResult::factory()->create([
                'saved_search_id' => $savedSearchId,
                'sleeping_place_id' => $sleepingPlace['id'],
                'property_id' => $sleepingPlace['property_id'],
                'room_id' => $sleepingPlace['room_id'],
            ]);

            $existing[$key] = true;
            $created++;
        }
    }

    /**
     * @param  array{id:int,property_id:int,room_id:int,sleeping_place_id:int,guest_user_id:int,host_user_id:int,check_in_date:string,check_out_date:string}  $booking
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function bookingState(array $booking, array $overrides = []): array
    {
        return [
            'booking_id' => $booking['id'],
            'guest_user_id' => $booking['guest_user_id'],
            'host_user_id' => $booking['host_user_id'],
            'property_id' => $booking['property_id'],
            'room_id' => $booking['room_id'],
            'sleeping_place_id' => $booking['sleeping_place_id'],
            ...$overrides,
        ];
    }

    /**
     * @param  array{id:int,booking_id:int,property_id:int,room_id:int,sleeping_place_id:int,guest_user_id:int,host_user_id:int}  $checkIn
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function bookingCheckInState(array $checkIn, array $overrides = []): array
    {
        return [
            'booking_check_in_id' => $checkIn['id'],
            'booking_id' => $checkIn['booking_id'],
            'guest_user_id' => $checkIn['guest_user_id'],
            'host_user_id' => $checkIn['host_user_id'],
            ...$overrides,
        ];
    }

    /**
     * @param  array{id:int,booking_id:int,property_id:int,room_id:int,sleeping_place_id:int,guest_user_id:int,host_user_id:int}  $checkOut
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function bookingCheckOutState(array $checkOut, array $overrides = []): array
    {
        return [
            'booking_check_out_id' => $checkOut['id'],
            'booking_id' => $checkOut['booking_id'],
            'guest_user_id' => $checkOut['guest_user_id'],
            'host_user_id' => $checkOut['host_user_id'],
            ...$overrides,
        ];
    }

    /**
     * @param  array{id:int,property_id:int,room_id:int}  $sleepingPlace
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function listingWorkflowState(array $sleepingPlace, int $userId, array $overrides = []): array
    {
        return [
            'user_id' => $userId,
            'property_id' => $sleepingPlace['property_id'],
            'room_id' => $sleepingPlace['room_id'],
            'sleeping_place_id' => $sleepingPlace['id'],
            ...$overrides,
        ];
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  list<int>  $userIds
     * @param  callable(int, int): TModel  $create
     */
    private function seedMissingUserOwnedRows(string $modelClass, array $userIds, callable $create): void
    {
        $missing = $this->missingFor($modelClass);
        $existingUserIds = $modelClass::query()->pluck('user_id')->all();
        $created = 0;

        foreach ($userIds as $index => $userId) {
            if ($created >= $missing) {
                return;
            }

            if (in_array($userId, $existingUserIds, true)) {
                continue;
            }

            $create($userId, $index);
            $created++;
        }
    }

    /**
     * @template TModel of Model
     *
     * @param  class-string<TModel>  $modelClass
     * @param  list<int>  $ownerIds
     * @param  callable(int, string, int): array<string, mixed>  $payload
     */
    private function seedTranslationsFor(string $modelClass, string $ownerColumn, array $ownerIds, callable $payload): void
    {
        $existing = $modelClass::query()
            ->select([$ownerColumn, 'locale'])
            ->whereIn($ownerColumn, $ownerIds)
            ->get()
            ->mapWithKeys(fn (Model $translation): array => [
                $translation->getAttribute($ownerColumn).':'.$translation->getAttribute('locale') => true,
            ])
            ->all();

        foreach ($ownerIds as $index => $ownerId) {
            foreach ($this->contentLocales() as $locale) {
                $key = $ownerId.':'.$locale;

                if (isset($existing[$key])) {
                    continue;
                }

                $modelClass::query()->create($payload($ownerId, $locale, $index));
                $existing[$key] = true;
            }
        }
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    private function missingFor(string $modelClass): int
    {
        return max(0, self::TARGET_COUNT - $modelClass::query()->count());
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return list<int>
     */
    private function ids(string $modelClass): array
    {
        return $modelClass::query()
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @template TValue
     *
     * @param  list<TValue>  $items
     * @return TValue
     */
    private function pick(array $items, int $index): mixed
    {
        if ($items === []) {
            throw new \RuntimeException('Bulk marketplace seeder prerequisite data is missing.');
        }

        return $items[$index % count($items)];
    }

    /**
     * @return list<array{id:int, country_id:int, name:string}>
     */
    private function regionRows(): array
    {
        return Region::query()
            ->select(['id', 'country_id', 'name'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (Region $region): array => [
                'id' => $region->id,
                'country_id' => $region->country_id,
                'name' => $region->name,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,country_id:int,region_id:int|null,name:string,country_name:string,region_name:string|null,latitude:string|null,longitude:string|null}>
     */
    private function cityRows(): array
    {
        return City::query()
            ->select(['id', 'country_id', 'region_id', 'name', 'latitude', 'longitude'])
            ->with([
                'country:id,name_en,name',
                'region:id,name',
            ])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (City $city): array => [
                'id' => $city->id,
                'country_id' => $city->country_id,
                'region_id' => $city->region_id,
                'name' => $city->name,
                'country_name' => $city->country?->name_en ?: $city->country?->name ?: 'Bulk demo country',
                'region_name' => $city->region?->name,
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,user_id:int|null,host_user_id:int|null,country_id:int|null,city_id:int|null}>
     */
    private function propertyRows(): array
    {
        return Property::query()
            ->select(['id', 'user_id', 'host_user_id', 'country_id', 'city_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (Property $property): array => [
                'id' => $property->id,
                'user_id' => $property->user_id,
                'host_user_id' => $property->host_user_id,
                'country_id' => $property->country_id,
                'city_id' => $property->city_id,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,property_id:int}>
     */
    private function roomRows(): array
    {
        return Room::query()
            ->select(['id', 'property_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (Room $room): array => [
                'id' => $room->id,
                'property_id' => $room->property_id,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,property_id:int,room_id:int}>
     */
    private function sleepingPlaceRows(): array
    {
        return SleepingPlace::query()
            ->select(['id', 'property_id', 'room_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (SleepingPlace $sleepingPlace): array => [
                'id' => $sleepingPlace->id,
                'property_id' => $sleepingPlace->property_id,
                'room_id' => $sleepingPlace->room_id,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,property_id:int,room_id:int,sleeping_place_id:int,guest_user_id:int,host_user_id:int,check_in_date:string,check_out_date:string}>
     */
    private function bookingRows(): array
    {
        return Booking::query()
            ->select([
                'id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'guest_user_id',
                'guest_id',
                'host_user_id',
                'host_id',
                'check_in_date',
                'check_in',
                'check_out_date',
                'check_out',
            ])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
                'sleeping_place_id' => $booking->sleeping_place_id,
                'guest_user_id' => $booking->guest_user_id ?: $booking->guest_id,
                'host_user_id' => $booking->host_user_id ?: $booking->host_id,
                'check_in_date' => $booking->check_in_date?->toDateString() ?: $booking->check_in?->toDateString() ?: CarbonImmutable::now()->addWeek()->toDateString(),
                'check_out_date' => $booking->check_out_date?->toDateString() ?: $booking->check_out?->toDateString() ?: CarbonImmutable::now()->addWeek()->addDays(3)->toDateString(),
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,booking_id:int,property_id:int,room_id:int,sleeping_place_id:int,guest_user_id:int,host_user_id:int}>
     */
    private function bookingCheckInRows(): array
    {
        return BookingCheckIn::query()
            ->select(['id', 'booking_id', 'property_id', 'room_id', 'sleeping_place_id', 'guest_user_id', 'host_user_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingCheckIn $checkIn): array => [
                'id' => $checkIn->id,
                'booking_id' => $checkIn->booking_id,
                'property_id' => $checkIn->property_id,
                'room_id' => $checkIn->room_id,
                'sleeping_place_id' => $checkIn->sleeping_place_id,
                'guest_user_id' => $checkIn->guest_user_id,
                'host_user_id' => $checkIn->host_user_id,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,booking_id:int,property_id:int,room_id:int,sleeping_place_id:int,guest_user_id:int,host_user_id:int}>
     */
    private function bookingCheckOutRows(): array
    {
        return BookingCheckOut::query()
            ->select(['id', 'booking_id', 'property_id', 'room_id', 'sleeping_place_id', 'guest_user_id', 'host_user_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingCheckOut $checkOut): array => [
                'id' => $checkOut->id,
                'booking_id' => $checkOut->booking_id,
                'property_id' => $checkOut->property_id,
                'room_id' => $checkOut->room_id,
                'sleeping_place_id' => $checkOut->sleeping_place_id,
                'guest_user_id' => $checkOut->guest_user_id,
                'host_user_id' => $checkOut->host_user_id,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,user_id:int,property_id:int,room_id:int,sleeping_place_id:int}>
     */
    private function waitlistItemRows(): array
    {
        return WaitlistItem::query()
            ->select(['id', 'user_id', 'property_id', 'room_id', 'sleeping_place_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (WaitlistItem $item): array => [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'property_id' => $item->property_id,
                'room_id' => $item->room_id,
                'sleeping_place_id' => $item->sleeping_place_id,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,user_id:int,property_id:int|null,room_id:int|null,sleeping_place_id:int|null,hint_key:string}>
     */
    private function hostHintSnapshotRows(): array
    {
        return HostHintSnapshot::query()
            ->select(['id', 'user_id', 'property_id', 'room_id', 'sleeping_place_id', 'hint_key'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (HostHintSnapshot $hint): array => [
                'id' => $hint->id,
                'user_id' => $hint->user_id,
                'property_id' => $hint->property_id,
                'room_id' => $hint->room_id,
                'sleeping_place_id' => $hint->sleeping_place_id,
                'hint_key' => $hint->hint_key,
            ])
            ->all();
    }

    private function localizedSeedText(string $base, string $locale, int $index): string
    {
        $prefix = $locale === $this->fallbackLocale() ? '' : strtoupper($locale).' ';

        return sprintf('%s%s %04d', $prefix, $base, $index + 1);
    }

    /**
     * @return list<string>
     */
    private function contentLocales(): array
    {
        return app(SupportedContentLocales::class)->locales();
    }

    private function fallbackLocale(): string
    {
        return (string) config('localization.fallback_locale', config('app.fallback_locale', 'en'));
    }
}
