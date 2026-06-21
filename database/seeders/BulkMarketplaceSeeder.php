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
use App\Models\BookingCancellation;
use App\Models\BookingCancellationAlternative;
use App\Models\BookingCancellationEvent;
use App\Models\BookingCancellationPolicySnapshot;
use App\Models\BookingCancellationPreview;
use App\Models\BookingCancellationRefundLine;
use App\Models\BookingCancellationStatusLog;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAccessDisclosure;
use App\Models\BookingCheckInAlert;
use App\Models\BookingCheckInChecklistItem;
use App\Models\BookingCheckInInstruction;
use App\Models\BookingCheckInMedia;
use App\Models\BookingCheckInProblem;
use App\Models\BookingCheckInProblemReport;
use App\Models\BookingCheckInStatusLog;
use App\Models\BookingCheckInStep;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutChecklistItem;
use App\Models\BookingCheckOutEvent;
use App\Models\BookingCheckOutInventoryCheck;
use App\Models\BookingCheckOutIssue;
use App\Models\BookingCheckOutIssueReport;
use App\Models\BookingCheckOutMedia;
use App\Models\BookingCheckOutStatusLog;
use App\Models\BookingCheckOutStep;
use App\Models\BookingDepositDecision;
use App\Models\BookingExtension;
use App\Models\BookingExtensionEvent;
use App\Models\BookingExtensionGuestResponse;
use App\Models\BookingExtensionHostResponse;
use App\Models\BookingExtensionLine;
use App\Models\BookingExtensionStatusLog;
use App\Models\BookingExtensionValidationResult;
use App\Models\BookingForgottenItem;
use App\Models\BookingGroupLink;
use App\Models\BookingGuest;
use App\Models\BookingGuestIntake;
use App\Models\BookingHostResponse;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\BookingLifecycleEvent;
use App\Models\BookingListingMismatchCompensationLine;
use App\Models\BookingListingMismatchEvent;
use App\Models\BookingListingMismatchGuestResponse;
use App\Models\BookingListingMismatchHostResponse;
use App\Models\BookingListingMismatchItem;
use App\Models\BookingListingMismatchMedia;
use App\Models\BookingListingMismatchReport;
use App\Models\BookingListingMismatchResolutionOption;
use App\Models\BookingListingMismatchStatusLog;
use App\Models\BookingListingMismatchWarning;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowContactAttempt;
use App\Models\BookingNoShowEvent;
use App\Models\BookingNoShowGuestResponse;
use App\Models\BookingNoShowMedia;
use App\Models\BookingNoShowPolicy;
use App\Models\BookingNoShowPolicySnapshot;
use App\Models\BookingNoShowStatusLog;
use App\Models\BookingPayment;
use App\Models\BookingPaymentAllocation;
use App\Models\BookingPaymentAttempt;
use App\Models\BookingPaymentDeadline;
use App\Models\BookingPaymentStatusLog;
use App\Models\BookingPriceLine;
use App\Models\BookingPriceSnapshot;
use App\Models\BookingQuote;
use App\Models\BookingQuoteLine;
use App\Models\BookingQuoteSuggestion;
use App\Models\BookingQuoteValidationResult;
use App\Models\BookingRefund;
use App\Models\BookingRelocation;
use App\Models\BookingRelocationConsent;
use App\Models\BookingRelocationEvent;
use App\Models\BookingRelocationGuestResponse;
use App\Models\BookingRelocationHostResponse;
use App\Models\BookingRelocationInventoryTransfer;
use App\Models\BookingRelocationOption;
use App\Models\BookingRelocationPriceLine;
use App\Models\BookingRelocationStatusLog;
use App\Models\BookingRelocationValidationResult;
use App\Models\BookingRequest;
use App\Models\BookingRequestCompatibilityResult;
use App\Models\BookingRequestGuestResponse;
use App\Models\BookingRequestHostResponse;
use App\Models\BookingRequestStatusLog;
use App\Models\BookingRequestWarning;
use App\Models\BookingRequirement;
use App\Models\BookingReviewRequest;
use App\Models\BookingStatusHistory;
use App\Models\BookingStatusLog;
use App\Models\BookingStay;
use App\Models\BookingStayEvent;
use App\Models\BookingStayNote;
use App\Models\BookingStayOccupant;
use App\Models\BookingStayStatusLog;
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
use App\Models\HostUnresponsiveContactAttempt;
use App\Models\HostUnresponsiveEvent;
use App\Models\HostUnresponsiveGuestAction;
use App\Models\HostUnresponsiveHostResponse;
use App\Models\HostUnresponsiveMedia;
use App\Models\HostUnresponsivePolicy;
use App\Models\HostUnresponsivePolicySnapshot;
use App\Models\HostUnresponsiveRepresentativeResponse;
use App\Models\HostUnresponsiveStatusLog;
use App\Models\ListingCreationDraft;
use App\Models\ListingHintSnapshot;
use App\Models\ListingPublicationCheck;
use App\Models\ListingReadinessCheck;
use App\Models\MediaItem;
use App\Models\MediaItemTranslation;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentReceipt;
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
use App\Models\PropertyCurrentOccupancySnapshot;
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
use App\Models\RoomCurrentOccupancySnapshot;
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
use App\Models\SleepingPlaceCancellationPolicy;
use App\Models\SleepingPlaceCancellationPolicyRule;
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
use App\Models\StayVisibilityPreference;
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
        $this->seedBookingRequests();
        $this->seedBookings();
        $this->seedBookingChildren();
        $this->seedBookingLifecycleRecords();
        $this->seedBookingStayRecords();
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

    private function seedBookingRequests(): void
    {
        $quoteRows = BookingQuote::query()
            ->select([
                'id',
                'user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'check_in_date',
                'check_in_time',
                'check_out_date',
                'check_out_time',
                'nights_count',
                'chargeable_days_count',
                'calendar_presence_days_count',
                'guests_count',
                'total_payable',
                'deposit_amount',
                'cleaning_fee_amount',
                'service_fee_amount',
                'currency',
            ])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingQuote $quote): array => $quote->toArray())
            ->all();
        $requestStart = BookingRequest::query()->count();

        $this->seedFactoryRows(
            BookingRequest::class,
            function (int $index) use ($quoteRows, $requestStart): array {
                $quote = $this->pick($quoteRows, $index);
                $requestType = match ($index % 6) {
                    1 => BookingRequest::TYPE_STAY_REQUEST,
                    2 => BookingRequest::TYPE_PRELIMINARY_INQUIRY,
                    3 => BookingRequest::TYPE_LONG_TERM_REQUEST,
                    4 => BookingRequest::TYPE_SAME_DAY_URGENT,
                    5 => BookingRequest::TYPE_REQUEST_ONLY,
                    default => BookingRequest::TYPE_HOST_APPROVAL,
                };

                return [
                    'request_number' => sprintf('BR-%s-%06d', now()->format('Y'), $requestStart + $index + 1),
                    'booking_quote_id' => $requestType === BookingRequest::TYPE_PRELIMINARY_INQUIRY ? null : $quote['id'],
                    'guest_user_id' => $quote['user_id'],
                    'host_user_id' => $quote['host_user_id'],
                    'property_id' => $quote['property_id'],
                    'room_id' => $quote['room_id'],
                    'sleeping_place_id' => $quote['sleeping_place_id'],
                    'request_type' => $requestType,
                    'status' => match ($index % 7) {
                        1 => BookingRequest::STATUS_WAITING_GUEST_RESPONSE,
                        2 => BookingRequest::STATUS_APPROVED,
                        3 => BookingRequest::STATUS_REJECTED,
                        4 => BookingRequest::STATUS_EXPIRED,
                        5 => BookingRequest::STATUS_WITHDRAWN_BY_GUEST,
                        6 => BookingRequest::STATUS_CONVERTED_TO_BOOKING,
                        default => BookingRequest::STATUS_WAITING_HOST_RESPONSE,
                    },
                    'hold_dates' => $requestType !== BookingRequest::TYPE_PRELIMINARY_INQUIRY && $index % 3 === 0,
                    'hold_expires_at' => now()->addHours(12 + ($index % 12)),
                    'expires_at' => now()->addHours(24 + ($index % 48)),
                    'check_in_date' => $quote['check_in_date'],
                    'check_in_time' => $quote['check_in_time'] ?: '15:00',
                    'check_out_date' => $quote['check_out_date'],
                    'check_out_time' => $quote['check_out_time'] ?: '11:00',
                    'nights_count' => $quote['nights_count'],
                    'chargeable_days_count' => $quote['chargeable_days_count'],
                    'calendar_presence_days_count' => $quote['calendar_presence_days_count'],
                    'guests_count' => $quote['guests_count'],
                    'trip_purpose' => ['work', 'study', 'travel', 'relocation'][$index % 4],
                    'planned_arrival_time' => $index % 5 === 0 ? '23:30' : '18:00',
                    'planned_departure_time' => $index % 6 === 0 ? '05:30' : '11:00',
                    'guest_message' => 'booking_requests.demo.guest_message',
                    'has_baggage' => $index % 2 === 0,
                    'needs_luggage_storage' => $index % 3 === 0,
                    'needs_early_check_in' => $index % 7 === 0,
                    'needs_late_checkout' => $index % 8 === 0,
                    'needs_residence_registration' => $index % 9 === 0,
                    'needs_reporting_documents' => $index % 10 === 0,
                    'guest_agreed_to_rules' => true,
                    'guest_agreed_to_cancellation_policy' => true,
                    'guest_agreed_to_deposit_policy' => true,
                    'guest_profile_snapshot_json' => [
                        'public_name' => 'Demo guest',
                        'identity_verified' => $index % 2 === 0,
                    ],
                    'guest_rating_snapshot_json' => [
                        'completed_stays_count' => $index % 12,
                        'reviews_count' => $index % 8,
                    ],
                    'compatibility_snapshot_json' => [],
                    'price_snapshot_json' => [
                        'quote_id' => $quote['id'],
                        'total_payable' => $quote['total_payable'],
                    ],
                    'warnings_snapshot_json' => [],
                    'total_amount' => $quote['total_payable'],
                    'deposit_amount' => $quote['deposit_amount'],
                    'cleaning_fee_amount' => $quote['cleaning_fee_amount'],
                    'service_fee_amount' => $quote['service_fee_amount'],
                    'currency' => $quote['currency'] ?: 'EUR',
                ];
            },
        );

        $requestRows = BookingRequest::query()
            ->select(['id', 'guest_user_id', 'host_user_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingRequest $request): array => $request->toArray())
            ->all();

        $this->seedFactoryRows(
            BookingRequestWarning::class,
            fn (int $index): array => [
                'booking_request_id' => $this->pick($requestRows, $index)['id'],
                'warning_key' => ['late_night_arrival', 'no_reviews', 'early_check_in_requested', 'too_many_guests'][$index % 4],
                'severity' => $index % 4 === 3 ? 'blocking' : 'warning',
                'message_key' => 'booking_requests.warnings.'.(['late_night_arrival', 'no_reviews', 'early_check_in_requested', 'too_many_guests'][$index % 4]),
                'message_params_json' => [],
                'blocking' => $index % 4 === 3,
                'visible_to_host' => true,
                'visible_to_guest' => false,
            ],
        );

        $this->seedFactoryRows(
            BookingRequestCompatibilityResult::class,
            fn (int $index): array => [
                'booking_request_id' => $this->pick($requestRows, $index)['id'],
                'compatibility_key' => ['guest_count', 'room_format', 'smoking_policy', 'pet_policy'][$index % 4],
                'status' => $index % 5 === 0 ? 'warning' : 'good',
                'severity' => $index % 5 === 0 ? 'warning' : 'info',
                'message_key' => 'booking_requests.compatibility.'.(['guest_count', 'room_format', 'smoking_policy', 'pet_policy'][$index % 4]),
                'message_params_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingRequestHostResponse::class,
            fn (int $index): array => [
                'booking_request_id' => $this->pick($requestRows, $index)['id'],
                'host_user_id' => $this->pick($requestRows, $index)['host_user_id'],
                'response_type' => ['approve', 'ask_question', 'propose_time_change', 'reject'][$index % 4],
                'message' => 'booking_requests.demo.host_response',
                'rejection_reason' => $index % 4 === 3 ? 'other' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingRequestGuestResponse::class,
            fn (int $index): array => [
                'booking_request_id' => $this->pick($requestRows, $index)['id'],
                'guest_user_id' => $this->pick($requestRows, $index)['guest_user_id'],
                'response_type' => ['answer_question', 'accept_proposal', 'reject_proposal', 'send_message'][$index % 4],
                'message' => 'booking_requests.demo.guest_response',
            ],
        );

        $this->seedFactoryRows(
            BookingRequestStatusLog::class,
            fn (int $index): array => [
                'booking_request_id' => $this->pick($requestRows, $index)['id'],
                'user_id' => $this->pick($requestRows, $index)['guest_user_id'],
                'old_status' => null,
                'new_status' => BookingRequest::STATUS_WAITING_HOST_RESPONSE,
                'reason_key' => 'booking_requests.demo.seeded',
                'context_json' => [],
            ],
        );
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

        $this->seedFactoryRows(
            BookingRequirement::class,
            fn (int $index): array => [
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'requirement_key' => ['rules_acceptance', 'payment', 'host_confirmation', 'phone_verification'][$index % 4],
                'status' => $index % 3 === 0 ? BookingRequirement::STATUS_COMPLETED : BookingRequirement::STATUS_PENDING,
                'required' => true,
                'completed_at' => $index % 3 === 0 ? now()->subDays($index % 30) : null,
                'message_key' => 'bookings.requirements.'.(['rules_acceptance', 'payment', 'host_confirmation', 'phone_verification'][$index % 4]),
            ],
        );

        $this->seedFactoryRows(
            BookingHostResponse::class,
            fn (int $index): array => [
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'host_user_id' => $this->pick($bookingRows, $index)['host_user_id'],
                'response_type' => [BookingHostResponse::TYPE_APPROVED, BookingHostResponse::TYPE_ASK_GUEST_QUESTION, BookingHostResponse::TYPE_PROPOSE_TIME_CHANGE, BookingHostResponse::TYPE_REJECTED][$index % 4],
                'message' => 'bookings.demo.host_response',
                'rejection_reason' => $index % 4 === 3 ? 'other' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingStatusLog::class,
            fn (int $index): array => [
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'user_id' => $this->pick($userIds, $index),
                'old_status' => null,
                'new_status' => ['created', 'waiting_payment', 'confirmed', 'ready_for_check_in'][$index % 4],
                'reason_key' => 'bookings.lifecycle_events.transitioned',
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingGroupLink::class,
            fn (int $index): array => [
                'group_booking_number' => sprintf('BG-%s-%06d', now()->format('Y'), intdiv($index, 3) + 1),
                'main_booking_id' => $this->pick($bookingRows, intdiv($index, 3) * 3)['id'],
                'booking_id' => $this->pick($bookingRows, $index)['id'],
                'guest_user_id' => $this->pick($bookingRows, $index)['guest_user_id'],
                'host_user_id' => $this->pick($bookingRows, $index)['host_user_id'],
                'property_id' => $this->pick($bookingRows, $index)['property_id'],
                'room_id' => $this->pick($bookingRows, $index)['room_id'],
                'status' => $index % 7 === 0 ? 'completed' : 'active',
            ],
        );

        $bookingStayRows = $this->bookingStayRows();
        $staysByBooking = collect($bookingStayRows)->keyBy('booking_id')->all();
        $extensionStart = BookingExtension::query()->count();

        BookingExtension::factory()
            ->count($this->missingFor(BookingExtension::class))
            ->sequence(function (Sequence $sequence) use ($bookingRows, $staysByBooking, $extensionStart): array {
                $booking = $this->pick($bookingRows, $sequence->index);
                $stay = $staysByBooking[$booking['id']] ?? null;
                $current = CarbonImmutable::parse($booking['check_out_date'])->startOfDay();
                $extraNights = 1 + ($sequence->index % 5);
                $new = $current->addDays($extraNights);
                $accommodation = $extraNights * (20 + ($sequence->index % 12));
                $serviceFee = round($accommodation * 0.05, 2);

                return [
                    'extension_number' => sprintf('EXT-%s-%06d', now()->format('Y'), $extensionStart + $sequence->index + 1),
                    'booking_id' => $booking['id'],
                    'booking_stay_id' => $stay['id'] ?? null,
                    'guest_user_id' => $booking['guest_user_id'],
                    'host_user_id' => $booking['host_user_id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'current_checkout_date' => $current->toDateString(),
                    'requested_new_checkout_date' => $new->toDateString(),
                    'current_check_out_date' => $current->toDateString(),
                    'current_check_out_time' => '11:00',
                    'new_check_out_date' => $new->toDateString(),
                    'new_check_out_time' => '11:00',
                    'additional_nights' => $extraNights,
                    'additional_nights_count' => $extraNights,
                    'additional_chargeable_days_count' => $extraNights,
                    'additional_calendar_presence_days_count' => $extraNights + 1,
                    'original_check_out' => $current->toDateString(),
                    'new_check_out' => $new->toDateString(),
                    'extra_nights' => $extraNights,
                    'extra_amount' => $accommodation,
                    'additional_amount' => $accommodation,
                    'accommodation_amount' => $accommodation,
                    'service_fee_amount' => $serviceFee,
                    'total_extra' => $accommodation + $serviceFee,
                    'total_payable' => $accommodation + $serviceFee,
                    'host_payout_amount' => $accommodation,
                    'non_refundable_amount' => $accommodation + $serviceFee,
                    'currency' => 'EUR',
                    'status' => ['waiting_host_confirmation', 'approved_waiting_payment', 'paid', 'applied'][$sequence->index % 4],
                    'payment_status' => $sequence->index % 4 >= 2 ? 'paid' : 'waiting_payment',
                    'requires_host_confirmation' => $sequence->index % 2 === 0,
                    'requires_host_approval' => $sequence->index % 2 === 0,
                    'requires_payment' => true,
                    'payment_required' => true,
                    'hold_dates' => $sequence->index % 4 !== 3,
                    'payment_deadline_at' => now()->addMinutes(30),
                    'hold_expires_at' => now()->addMinutes(30),
                    'expires_at' => now()->addHours(24),
                    'approved_at' => $sequence->index % 4 >= 1 ? now()->subHours(3) : null,
                    'paid_at' => $sequence->index % 4 >= 2 ? now()->subHours(2) : null,
                    'applied_at' => $sequence->index % 4 === 3 ? now()->subHour() : null,
                ];
            })
            ->create();

        $extensionRows = $this->bookingExtensionRows();

        $this->seedFactoryRows(
            BookingExtensionLine::class,
            function (int $index) use ($extensionRows): array {
                $extension = $this->pick($extensionRows, $index);
                $lineType = ['extension_night', 'service_fee', 'extension_discount', 'additional_deposit'][$index % 4];
                $amount = match ($lineType) {
                    'extension_discount' => -5,
                    'service_fee' => 4,
                    'additional_deposit' => 25,
                    default => 20 + ($index % 15),
                };

                return [
                    'booking_extension_id' => $extension['id'],
                    'line_type' => $lineType,
                    'label_key' => 'booking_extensions.lines.'.$lineType,
                    'date' => $lineType === 'extension_night'
                        ? CarbonImmutable::parse($extension['current_check_out_date'])->addDays($index % max(1, $extension['additional_nights_count']))->toDateString()
                        : null,
                    'quantity' => 1,
                    'unit_amount' => $amount,
                    'amount' => $amount,
                    'currency' => $extension['currency'],
                    'is_discount' => $lineType === 'extension_discount',
                    'is_fee' => $lineType === 'service_fee',
                    'is_deposit' => $lineType === 'additional_deposit',
                    'is_refundable' => $lineType === 'additional_deposit',
                    'is_payable_now' => true,
                    'sort_order' => $index % 20,
                ];
            },
        );

        $this->seedFactoryRows(
            BookingExtensionValidationResult::class,
            fn (int $index): array => [
                'booking_extension_id' => $this->pick($extensionRows, $index)['id'],
                'validation_key' => ['host_confirmation_required', 'payment_required', 'same_day_extension_requires_confirmation', 'guest_not_allowed'][$index % 4],
                'severity' => $index % 4 === 3 ? 'warning' : 'info',
                'message_key' => 'booking_extensions.validation.'.(['host_confirmation_required', 'payment_required', 'same_day_extension_requires_confirmation', 'guest_not_allowed'][$index % 4]),
                'message_params_json' => [],
                'blocking' => false,
                'visible_to_guest' => true,
                'visible_to_host' => true,
            ],
        );

        $this->seedFactoryRows(
            BookingExtensionHostResponse::class,
            fn (int $index): array => [
                'booking_extension_id' => $this->pick($extensionRows, $index)['id'],
                'host_user_id' => $this->pick($extensionRows, $index)['host_user_id'],
                'response_type' => ['approve', 'ask_question', 'propose_new_checkout', 'reject'][$index % 4],
                'message' => 'booking_extensions.demo.host_response',
                'proposed_new_check_out_date' => $index % 4 === 2
                    ? CarbonImmutable::parse($this->pick($extensionRows, $index)['new_check_out_date'])->addDay()->toDateString()
                    : null,
                'rejection_reason' => $index % 4 === 3 ? 'other' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingExtensionGuestResponse::class,
            fn (int $index): array => [
                'booking_extension_id' => $this->pick($extensionRows, $index)['id'],
                'guest_user_id' => $this->pick($extensionRows, $index)['guest_user_id'],
                'response_type' => ['accept_host_proposal', 'answer_question', 'send_message', 'cancel_request'][$index % 4],
                'message' => 'booking_extensions.demo.guest_response',
                'accepted_new_check_out_date' => $index % 4 === 0 ? $this->pick($extensionRows, $index)['new_check_out_date'] : null,
            ],
        );

        $this->seedFactoryRows(
            BookingExtensionStatusLog::class,
            fn (int $index): array => [
                'booking_extension_id' => $this->pick($extensionRows, $index)['id'],
                'booking_id' => $this->pick($extensionRows, $index)['booking_id'],
                'user_id' => $index % 2 === 0 ? $this->pick($extensionRows, $index)['guest_user_id'] : $this->pick($extensionRows, $index)['host_user_id'],
                'old_status' => null,
                'new_status' => $this->pick($extensionRows, $index)['status'],
                'reason_key' => 'booking_extensions.events.extension_requested',
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingExtensionEvent::class,
            fn (int $index): array => [
                'booking_extension_id' => $this->pick($extensionRows, $index)['id'],
                'booking_id' => $this->pick($extensionRows, $index)['booking_id'],
                'event_key' => ['extension_requested', 'availability_checked', 'quote_created', 'extension_applied'][$index % 4],
                'event_type' => 'system',
                'user_id' => $this->pick($extensionRows, $index)['guest_user_id'],
                'occurred_at' => now()->subMinutes($index % 1440),
                'context_json' => [],
            ],
        );

        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $relocationStart = BookingRelocation::query()->count();

        BookingRelocation::factory()
            ->count($this->missingFor(BookingRelocation::class))
            ->sequence(function (Sequence $sequence) use ($bookingRows, $staysByBooking, $sleepingPlaceRows, $relocationStart): array {
                $booking = $this->pick($bookingRows, $sequence->index);
                $samePropertyPlaces = array_values(array_filter(
                    $sleepingPlaceRows,
                    fn (array $place): bool => $place['property_id'] === $booking['property_id']
                        && $place['id'] !== $booking['sleeping_place_id'],
                ));
                $newPlace = $samePropertyPlaces === []
                    ? $this->pick($sleepingPlaceRows, $sequence->index + 1)
                    : $this->pick($samePropertyPlaces, $sequence->index);
                $stay = $staysByBooking[$booking['id']] ?? null;
                $checkIn = CarbonImmutable::parse($booking['check_in_date'])->startOfDay();
                $checkOut = CarbonImmutable::parse($booking['check_out_date'])->startOfDay();
                $relocationDate = $checkIn->addDay()->lt($checkOut)
                    ? $checkIn->addDay()
                    : $checkIn;
                $remainingNights = max(1, $relocationDate->diffInDays($checkOut));
                $oldRemainingValue = $remainingNights * (18 + ($sequence->index % 8));
                $newRemainingValue = $oldRemainingValue + match ($sequence->index % 3) {
                    0 => 15,
                    1 => 0,
                    default => -10,
                };
                $priceDifference = $newRemainingValue - $oldRemainingValue;
                $payer = match (true) {
                    $priceDifference < 0 => 'refund_to_guest',
                    $sequence->index % 4 === 1 => 'no_extra_charge',
                    default => 'guest',
                };

                return [
                    'relocation_number' => sprintf('REL-%s-%06d', now()->format('Y'), $relocationStart + $sequence->index + 1),
                    'original_booking_id' => $booking['id'],
                    'new_booking_id' => null,
                    'booking_stay_id' => $stay['id'] ?? null,
                    'guest_user_id' => $booking['guest_user_id'],
                    'host_user_id' => $booking['host_user_id'],
                    'current_property_id' => $booking['property_id'],
                    'current_room_id' => $booking['room_id'],
                    'current_sleeping_place_id' => $booking['sleeping_place_id'],
                    'new_property_id' => $newPlace['property_id'],
                    'new_room_id' => $newPlace['room_id'],
                    'new_sleeping_place_id' => $newPlace['id'],
                    'requested_by_user_id' => $sequence->index % 2 === 0 ? $booking['guest_user_id'] : $booking['host_user_id'],
                    'requested_by_type' => $sequence->index % 2 === 0 ? 'guest' : 'host',
                    'reason' => ['noisy_neighbors', 'uncomfortable_bed', 'breakdown', 'guest_wants_more_comfort', 'complaint_resolution'][$sequence->index % 5],
                    'status' => ['requested', 'waiting_host_consent', 'waiting_guest_consent', 'approved', 'applied'][$sequence->index % 5],
                    'relocation_date' => $relocationDate->toDateString(),
                    'relocation_time' => '14:00',
                    'check_in_date' => $relocationDate->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'original_check_in_date' => $checkIn->toDateString(),
                    'original_check_out_date' => $checkOut->toDateString(),
                    'old_period_check_in_date' => $checkIn->toDateString(),
                    'old_period_check_out_date' => $relocationDate->toDateString(),
                    'new_period_check_in_date' => $relocationDate->toDateString(),
                    'new_period_check_out_date' => $checkOut->toDateString(),
                    'old_remaining_value_amount' => $oldRemainingValue,
                    'new_remaining_value_amount' => $newRemainingValue,
                    'price_difference_amount' => $priceDifference,
                    'additional_payment_amount' => $payer === 'guest' ? max(0, $priceDifference) : 0,
                    'refund_amount' => $payer === 'refund_to_guest' ? abs(min(0, $priceDifference)) : 0,
                    'additional_deposit_amount' => $sequence->index % 6 === 0 ? 20 : 0,
                    'cleaning_fee_difference_amount' => 0,
                    'service_fee_difference_amount' => $payer === 'guest' ? round(max(0, $priceDifference) * 0.05, 2) : 0,
                    'host_payout_difference_amount' => $priceDifference,
                    'currency' => 'EUR',
                    'price_difference_payer' => $payer,
                    'requires_guest_consent' => true,
                    'requires_host_consent' => $sequence->index % 2 === 0,
                    'guest_consented_at' => $sequence->index % 5 >= 3 ? now()->subHours(2) : null,
                    'host_consented_at' => $sequence->index % 5 >= 3 ? now()->subHours(2) : null,
                    'requires_payment' => $payer === 'guest' && $priceDifference > 0,
                    'payment_status' => $payer === 'guest' && $priceDifference > 0 ? 'waiting_payment' : 'not_required',
                    'requires_refund' => $payer === 'refund_to_guest',
                    'refund_status' => $payer === 'refund_to_guest' ? 'pending' : null,
                    'guest_comment' => 'booking_relocations.demo.guest_comment',
                    'host_comment' => 'booking_relocations.demo.host_comment',
                    'support_comment' => 'booking_relocations.demo.future_support_comment',
                    'hold_dates' => $sequence->index % 5 !== 4,
                    'hold_expires_at' => now()->addMinutes(30),
                    'expires_at' => now()->addDay(),
                    'approved_at' => $sequence->index % 5 >= 3 ? now()->subHours(2) : null,
                    'applied_at' => $sequence->index % 5 === 4 ? now()->subHour() : null,
                ];
            })
            ->create();

        $relocationRows = $this->bookingRelocationRows();

        $this->seedFactoryRows(
            BookingRelocationOption::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'sleeping_place_id' => $this->pick($relocationRows, $index)['new_sleeping_place_id'],
                'property_id' => $this->pick($relocationRows, $index)['new_property_id'],
                'room_id' => $this->pick($relocationRows, $index)['new_room_id'],
                'price_difference_amount' => $this->pick($relocationRows, $index)['price_difference_amount'],
                'additional_payment_amount' => $this->pick($relocationRows, $index)['additional_payment_amount'],
                'refund_amount' => $this->pick($relocationRows, $index)['refund_amount'],
                'additional_deposit_amount' => $this->pick($relocationRows, $index)['additional_deposit_amount'],
                'currency' => $this->pick($relocationRows, $index)['currency'],
                'availability_status' => 'available',
                'compatibility_status' => ['good', 'medium', 'warning'][$index % 3],
                'pricing_status' => 'calculated',
                'room_privacy_level' => ['shared', 'quieter', 'more_private'][$index % 3],
                'comfort_score' => 70 + ($index % 20),
                'match_score' => 75 + ($index % 20),
                'host_note' => 'booking_relocations.demo.option_note',
                'guest_selected' => $index % 4 === 0,
                'selected_at' => $index % 4 === 0 ? now()->subHour() : null,
                'expires_at' => now()->addDay(),
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationPriceLine::class,
            function (int $index) use ($relocationRows): array {
                $relocation = $this->pick($relocationRows, $index);
                $lineType = ['old_remaining_value', 'new_remaining_value', 'price_difference', 'additional_payment', 'refund'][$index % 5];
                $amount = match ($lineType) {
                    'old_remaining_value' => $relocation['old_remaining_value_amount'],
                    'new_remaining_value' => $relocation['new_remaining_value_amount'],
                    'additional_payment' => $relocation['additional_payment_amount'],
                    'refund' => -1 * $relocation['refund_amount'],
                    default => $relocation['price_difference_amount'],
                };

                return [
                    'booking_relocation_id' => $relocation['id'],
                    'line_type' => $lineType,
                    'label_key' => 'booking_relocations.lines.'.$lineType,
                    'date' => in_array($lineType, ['old_remaining_value', 'new_remaining_value'], true) ? $relocation['relocation_date'] : null,
                    'quantity' => 1,
                    'unit_amount' => $amount,
                    'amount' => $amount,
                    'currency' => $relocation['currency'],
                    'is_discount' => false,
                    'is_fee' => false,
                    'is_deposit' => false,
                    'is_refundable' => $lineType === 'refund',
                    'is_payable_now' => $lineType === 'additional_payment',
                    'sort_order' => $index % 20,
                ];
            },
        );

        $this->seedFactoryRows(
            BookingRelocationValidationResult::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'validation_key' => ['host_consent_required', 'guest_consent_required', 'payment_required', 'old_place_needs_inspection'][$index % 4],
                'severity' => $index % 3 === 0 ? 'warning' : 'info',
                'message_key' => 'booking_relocations.validation.'.(['host_consent_required', 'guest_consent_required', 'payment_required', 'old_place_needs_inspection'][$index % 4]),
                'message_params_json' => [],
                'blocking' => false,
                'visible_to_guest' => true,
                'visible_to_host' => true,
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationConsent::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'user_id' => $index % 2 === 0 ? $this->pick($relocationRows, $index)['guest_user_id'] : $this->pick($relocationRows, $index)['host_user_id'],
                'consent_type' => ['guest_accepts_new_place', 'guest_accepts_price_difference', 'host_accepts_relocation', 'host_accepts_old_place_block'][$index % 4],
                'status' => $index % 5 === 0 ? 'accepted' : 'pending',
                'message' => 'booking_relocations.demo.consent_message',
                'responded_at' => $index % 5 === 0 ? now()->subHour() : null,
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationHostResponse::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'host_user_id' => $this->pick($relocationRows, $index)['host_user_id'],
                'response_type' => ['approve', 'ask_question', 'offer_alternative', 'propose_no_extra_charge'][$index % 4],
                'message' => 'booking_relocations.demo.host_response',
                'alternative_sleeping_place_id' => $index % 4 === 2 ? $this->pick($relocationRows, $index)['new_sleeping_place_id'] : null,
                'alternative_room_id' => $index % 4 === 2 ? $this->pick($relocationRows, $index)['new_room_id'] : null,
                'proposed_relocation_date' => $index % 4 === 1 ? $this->pick($relocationRows, $index)['relocation_date'] : null,
                'proposed_relocation_time' => $index % 4 === 1 ? '15:00' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationGuestResponse::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'guest_user_id' => $this->pick($relocationRows, $index)['guest_user_id'],
                'response_type' => ['accept', 'select_option', 'accept_price_difference', 'send_message'][$index % 4],
                'message' => 'booking_relocations.demo.guest_response',
                'accepted_sleeping_place_id' => $index % 4 === 0 ? $this->pick($relocationRows, $index)['new_sleeping_place_id'] : null,
                'accepted_relocation_date' => $index % 4 === 0 ? $this->pick($relocationRows, $index)['relocation_date'] : null,
                'accepted_relocation_time' => $index % 4 === 0 ? '14:00' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationInventoryTransfer::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'booking_id' => $this->pick($relocationRows, $index)['original_booking_id'],
                'item_name_snapshot' => ['Old key', 'New key', 'Locker', 'Bedding'][$index % 4],
                'transfer_type' => ['return_old_key', 'issue_new_key', 'assign_new_locker', 'move_bedding'][$index % 4],
                'status' => $index % 4 === 0 ? 'completed' : 'pending',
                'from_sleeping_place_id' => $this->pick($relocationRows, $index)['current_sleeping_place_id'],
                'to_sleeping_place_id' => $this->pick($relocationRows, $index)['new_sleeping_place_id'],
                'from_room_id' => $this->pick($relocationRows, $index)['current_room_id'],
                'to_room_id' => $this->pick($relocationRows, $index)['new_room_id'],
                'note' => 'booking_relocations.demo.inventory_transfer_note',
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationStatusLog::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'original_booking_id' => $this->pick($relocationRows, $index)['original_booking_id'],
                'new_booking_id' => $this->pick($relocationRows, $index)['new_booking_id'],
                'user_id' => $index % 2 === 0 ? $this->pick($relocationRows, $index)['guest_user_id'] : $this->pick($relocationRows, $index)['host_user_id'],
                'old_status' => null,
                'new_status' => $this->pick($relocationRows, $index)['status'],
                'reason_key' => 'booking_relocations.events.relocation_requested',
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingRelocationEvent::class,
            fn (int $index): array => [
                'booking_relocation_id' => $this->pick($relocationRows, $index)['id'],
                'original_booking_id' => $this->pick($relocationRows, $index)['original_booking_id'],
                'new_booking_id' => $this->pick($relocationRows, $index)['new_booking_id'],
                'event_key' => ['relocation_requested', 'options_found', 'price_difference_calculated', 'relocation_applied'][$index % 4],
                'event_type' => 'system',
                'user_id' => $this->pick($relocationRows, $index)['guest_user_id'],
                'occurred_at' => now()->subMinutes($index % 1440),
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            SleepingPlaceCancellationPolicy::class,
            fn (int $index): array => [
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index)['id'],
                'policy_type' => ['flexible', 'moderate', 'strict', 'non_refundable'][$index % 4],
                'title' => 'cancellations.policy_types.'.(['flexible', 'moderate', 'strict', 'non_refundable'][$index % 4]),
                'description' => 'cancellations.demo.policy_description',
                'free_cancellation_until_hours_before_check_in' => [24, 120, 168, 0][$index % 4],
                'penalty_starts_hours_before_check_in' => [24, 120, 168, 0][$index % 4],
                'first_night_non_refundable' => $index % 4 === 2,
                'cleaning_fee_refundable_before_check_in' => true,
                'service_fee_refundable' => $index % 4 === 0,
                'deposit_always_refundable_before_check_in' => true,
                'active' => true,
            ],
        );

        $cancellationPolicyRows = SleepingPlaceCancellationPolicy::query()
            ->select(['id', 'sleeping_place_id', 'policy_type'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (SleepingPlaceCancellationPolicy $policy): array => [
                'id' => $policy->id,
                'sleeping_place_id' => $policy->sleeping_place_id,
                'policy_type' => $policy->policy_type,
            ])
            ->all();

        $this->seedFactoryRows(
            SleepingPlaceCancellationPolicyRule::class,
            fn (int $index): array => [
                'sleeping_place_cancellation_policy_id' => $this->pick($cancellationPolicyRows, $index)['id'],
                'rule_key' => ['free_before_deadline', 'partial_after_deadline', 'deposit_refund', 'cleaning_fee_refund'][$index % 4],
                'applies_when' => ['before_free_cancellation_deadline', 'after_free_cancellation_deadline', 'guest_cancels', 'guest_cancels'][$index % 4],
                'refund_percent' => [100, 50, 100, 100][$index % 4],
                'fixed_penalty_amount' => null,
                'currency' => 'EUR',
                'description' => 'cancellations.demo.policy_rule',
                'sort_order' => $index % 40,
            ],
        );

        $this->seedFactoryRows(
            BookingCancellationPolicySnapshot::class,
            function (int $index) use ($bookingRows): array {
                $booking = $this->pick($bookingRows, $index);
                $policyType = ['flexible', 'moderate', 'strict', 'non_refundable'][$index % 4];
                $checkIn = CarbonImmutable::parse($booking['check_in_date'])->startOfDay();

                return [
                    'booking_id' => $booking['id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'policy_type' => $policyType,
                    'title_snapshot' => 'cancellations.policy_types.'.$policyType,
                    'description_snapshot' => 'cancellations.demo.snapshot_description',
                    'rules_snapshot_json' => ['policy_type' => $policyType, 'source' => 'bulk_demo_seed'],
                    'free_cancellation_until' => $policyType === 'non_refundable' ? null : $checkIn->subHours([24, 120, 168, 0][$index % 4]),
                    'cancellation_penalty_starts_at' => $policyType === 'non_refundable' ? null : $checkIn->subHours([24, 120, 168, 0][$index % 4]),
                    'first_night_non_refundable' => $policyType === 'strict',
                    'cleaning_fee_refundable_before_check_in' => true,
                    'service_fee_refundable' => $policyType === 'flexible',
                    'deposit_always_refundable_before_check_in' => true,
                ];
            },
        );

        $this->seedFactoryRows(
            BookingCancellationPreview::class,
            function (int $index) use ($bookingRows): array {
                $booking = $this->pick($bookingRows, $index);
                $accommodation = 100 + ($index % 30);
                $cleaning = 10;
                $service = 6;
                $deposit = 50;
                $totalRefund = $index % 4 === 3 ? $deposit + $cleaning : $accommodation + $cleaning + $service + $deposit;

                return [
                    'preview_number' => sprintf('CANPRE-%s-%06d', now()->format('Y'), $index + 1),
                    'booking_id' => $booking['id'],
                    'guest_user_id' => $booking['guest_user_id'],
                    'host_user_id' => $booking['host_user_id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'requested_by_user_id' => $index % 3 === 0 ? $booking['host_user_id'] : $booking['guest_user_id'],
                    'requested_by_type' => $index % 3 === 0 ? 'host' : 'guest',
                    'cancellation_type' => ['guest_fault', 'host_fault', 'housing_problem', 'non_refundable'][$index % 4],
                    'reason_key' => ['changed_plans', 'maintenance', 'housing_problem', 'other'][$index % 4],
                    'comment' => 'cancellations.demo.preview_comment',
                    'check_in_date' => $booking['check_in_date'],
                    'check_out_date' => $booking['check_out_date'],
                    'cancelled_at_preview' => now()->subMinutes($index % 120),
                    'hours_before_check_in' => 72,
                    'nights_before_check_in' => 3,
                    'nights_used' => 0,
                    'nights_unused' => max(1, CarbonImmutable::parse($booking['check_in_date'])->diffInDays(CarbonImmutable::parse($booking['check_out_date']))),
                    'accommodation_amount' => $accommodation,
                    'cleaning_fee_amount' => $cleaning,
                    'service_fee_amount' => $service,
                    'deposit_amount' => $deposit,
                    'tax_amount' => 0,
                    'city_fee_amount' => 0,
                    'accommodation_refund_amount' => $index % 4 === 3 ? 0 : $accommodation,
                    'cleaning_fee_refund_amount' => $cleaning,
                    'service_fee_refund_amount' => $index % 4 === 3 ? 0 : $service,
                    'deposit_refund_amount' => $deposit,
                    'tax_refund_amount' => 0,
                    'city_fee_refund_amount' => 0,
                    'penalty_amount' => ($accommodation + $cleaning + $service + $deposit) - $totalRefund,
                    'host_payout_adjustment_amount' => -1 * ($index % 4 === 3 ? 0 : $accommodation),
                    'total_refund_amount' => $totalRefund,
                    'total_non_refundable_amount' => ($accommodation + $cleaning + $service + $deposit) - $totalRefund,
                    'currency' => 'EUR',
                    'policy_snapshot_json' => ['policy_type' => ['flexible', 'moderate', 'strict', 'non_refundable'][$index % 4]],
                    'refund_breakdown_json' => [],
                    'expires_at' => now()->addMinutes(30),
                    'status' => $index % 5 === 0 ? 'converted_to_cancellation' : 'calculated',
                ];
            },
        );

        $cancellationPreviewRows = BookingCancellationPreview::query()
            ->select(['id', 'booking_id', 'guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'requested_by_user_id', 'requested_by_type', 'cancellation_type', 'reason_key', 'check_in_date', 'check_out_date', 'total_refund_amount', 'total_non_refundable_amount', 'currency'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingCancellationPreview $preview): array => [
                'id' => $preview->id,
                'booking_id' => $preview->booking_id,
                'guest_user_id' => $preview->guest_user_id,
                'host_user_id' => $preview->host_user_id,
                'property_id' => $preview->property_id,
                'room_id' => $preview->room_id,
                'sleeping_place_id' => $preview->sleeping_place_id,
                'requested_by_user_id' => $preview->requested_by_user_id,
                'requested_by_type' => $preview->requested_by_type,
                'cancellation_type' => $preview->cancellation_type,
                'reason_key' => $preview->reason_key,
                'check_in_date' => $preview->check_in_date?->toDateString(),
                'check_out_date' => $preview->check_out_date?->toDateString(),
                'total_refund_amount' => (float) $preview->total_refund_amount,
                'total_non_refundable_amount' => (float) $preview->total_non_refundable_amount,
                'currency' => $preview->currency,
            ])
            ->all();

        $this->seedFactoryRows(
            BookingCancellation::class,
            fn (int $index): array => [
                'cancellation_number' => sprintf('CAN-%s-%06d', now()->format('Y'), $index + 1),
                'booking_id' => $this->pick($cancellationPreviewRows, $index)['booking_id'],
                'booking_cancellation_preview_id' => $this->pick($cancellationPreviewRows, $index)['id'],
                'guest_user_id' => $this->pick($cancellationPreviewRows, $index)['guest_user_id'],
                'host_user_id' => $this->pick($cancellationPreviewRows, $index)['host_user_id'],
                'property_id' => $this->pick($cancellationPreviewRows, $index)['property_id'],
                'room_id' => $this->pick($cancellationPreviewRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($cancellationPreviewRows, $index)['sleeping_place_id'],
                'cancelled_by_user_id' => $this->pick($cancellationPreviewRows, $index)['requested_by_user_id'],
                'cancelled_by_type' => $this->pick($cancellationPreviewRows, $index)['requested_by_type'],
                'cancellation_type' => $this->pick($cancellationPreviewRows, $index)['cancellation_type'],
                'reason_key' => $this->pick($cancellationPreviewRows, $index)['reason_key'],
                'comment' => 'cancellations.demo.cancellation_comment',
                'status' => ['booking_cancelled', 'refund_pending', 'calendar_released', 'closed'][$index % 4],
                'check_in_date' => $this->pick($cancellationPreviewRows, $index)['check_in_date'],
                'check_out_date' => $this->pick($cancellationPreviewRows, $index)['check_out_date'],
                'cancelled_at' => now()->subMinutes($index % 1440),
                'hours_before_check_in' => 72,
                'nights_before_check_in' => 3,
                'nights_used' => $index % 6 === 0 ? 1 : 0,
                'nights_unused' => 2,
                'policy_snapshot_id' => null,
                'accommodation_amount' => 100 + ($index % 30),
                'cleaning_fee_amount' => 10,
                'service_fee_amount' => 6,
                'deposit_amount' => 50,
                'tax_amount' => 0,
                'city_fee_amount' => 0,
                'accommodation_refund_amount' => $this->pick($cancellationPreviewRows, $index)['total_refund_amount'] > 60 ? 100 + ($index % 30) : 0,
                'cleaning_fee_refund_amount' => 10,
                'service_fee_refund_amount' => $this->pick($cancellationPreviewRows, $index)['total_refund_amount'] > 60 ? 6 : 0,
                'deposit_refund_amount' => 50,
                'tax_refund_amount' => 0,
                'city_fee_refund_amount' => 0,
                'penalty_amount' => $this->pick($cancellationPreviewRows, $index)['total_non_refundable_amount'],
                'host_payout_adjustment_amount' => -1 * (100 + ($index % 30)),
                'total_refund_amount' => $this->pick($cancellationPreviewRows, $index)['total_refund_amount'],
                'total_non_refundable_amount' => $this->pick($cancellationPreviewRows, $index)['total_non_refundable_amount'],
                'currency' => $this->pick($cancellationPreviewRows, $index)['currency'],
                'refund_status' => $this->pick($cancellationPreviewRows, $index)['total_refund_amount'] > 0 ? 'pending' : 'not_required',
                'calendar_release_status' => $index % 6 === 0 ? 'kept_blocked' : 'released',
                'dates_released_at' => $index % 6 === 0 ? null : now()->subMinutes($index % 120),
                'requires_host_response' => false,
                'requires_dispute' => in_array($this->pick($cancellationPreviewRows, $index)['reason_key'], ['housing_problem', 'host_unresponsive', 'listing_mismatch'], true),
                'completed_at' => $index % 4 === 3 ? now()->subHour() : null,
                'closed_at' => $index % 4 === 3 ? now() : null,
            ],
        );

        $cancellationRows = BookingCancellation::query()
            ->select(['id', 'booking_id', 'guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'reason_key', 'status', 'currency', 'total_refund_amount', 'total_non_refundable_amount'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingCancellation $cancellation): array => [
                'id' => $cancellation->id,
                'booking_id' => $cancellation->booking_id,
                'guest_user_id' => $cancellation->guest_user_id,
                'host_user_id' => $cancellation->host_user_id,
                'property_id' => $cancellation->property_id,
                'room_id' => $cancellation->room_id,
                'sleeping_place_id' => $cancellation->sleeping_place_id,
                'reason_key' => $cancellation->reason_key,
                'status' => $cancellation->status,
                'currency' => $cancellation->currency,
                'total_refund_amount' => (float) $cancellation->total_refund_amount,
                'total_non_refundable_amount' => (float) $cancellation->total_non_refundable_amount,
            ])
            ->all();

        $this->seedFactoryRows(
            BookingCancellationRefundLine::class,
            fn (int $index): array => [
                'booking_cancellation_id' => $this->pick($cancellationRows, $index)['id'],
                'line_type' => ['accommodation', 'cleaning_fee', 'service_fee', 'deposit', 'penalty'][$index % 5],
                'label_key' => 'cancellations.refund_line_types.'.(['accommodation', 'cleaning_fee', 'service_fee', 'deposit', 'penalty'][$index % 5]),
                'amount' => [100, 10, 6, 50, $this->pick($cancellationRows, $index)['total_non_refundable_amount']][$index % 5],
                'currency' => $this->pick($cancellationRows, $index)['currency'],
                'refundable' => $index % 5 !== 4,
                'refund_amount' => $index % 5 === 4 ? 0 : [100, 10, 6, 50, 0][$index % 5],
                'non_refundable_amount' => $index % 5 === 4 ? $this->pick($cancellationRows, $index)['total_non_refundable_amount'] : 0,
                'reason_key' => $this->pick($cancellationRows, $index)['reason_key'],
                'sort_order' => $index % 50,
            ],
        );

        $this->seedFactoryRows(
            BookingCancellationStatusLog::class,
            fn (int $index): array => [
                'booking_cancellation_id' => $this->pick($cancellationRows, $index)['id'],
                'booking_id' => $this->pick($cancellationRows, $index)['booking_id'],
                'user_id' => $index % 2 === 0 ? $this->pick($cancellationRows, $index)['guest_user_id'] : $this->pick($cancellationRows, $index)['host_user_id'],
                'old_status' => null,
                'new_status' => $this->pick($cancellationRows, $index)['status'],
                'reason_key' => $this->pick($cancellationRows, $index)['reason_key'],
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingCancellationEvent::class,
            fn (int $index): array => [
                'booking_cancellation_id' => $this->pick($cancellationRows, $index)['id'],
                'booking_id' => $this->pick($cancellationRows, $index)['booking_id'],
                'event_key' => ['cancellation_preview_created', 'cancellation_confirmed', 'booking_cancelled', 'refund_created', 'calendar_locks_released'][$index % 5],
                'event_type' => 'system',
                'user_id' => $index % 2 === 0 ? $this->pick($cancellationRows, $index)['guest_user_id'] : $this->pick($cancellationRows, $index)['host_user_id'],
                'occurred_at' => now()->subMinutes($index % 1440),
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingCancellationAlternative::class,
            fn (int $index): array => [
                'booking_cancellation_id' => $this->pick($cancellationRows, $index)['id'],
                'sleeping_place_id' => $this->pick($sleepingPlaceRows, $index + 1)['id'],
                'property_id' => $this->pick($sleepingPlaceRows, $index + 1)['property_id'],
                'room_id' => $this->pick($sleepingPlaceRows, $index + 1)['room_id'],
                'suggestion_type' => ['same_host_place', 'same_property_place', 'similar_price_place', 'saved_search'][$index % 4],
                'check_in_date' => CarbonImmutable::now()->addDays(7 + ($index % 30))->toDateString(),
                'check_out_date' => CarbonImmutable::now()->addDays(10 + ($index % 30))->toDateString(),
                'price_preview_amount' => 80 + ($index % 40),
                'currency' => 'EUR',
                'message_key' => 'cancellations.messages.alternatives_available',
                'sort_order' => $index % 30,
            ],
        );

        PaymentRecord::factory()
            ->count($this->missingFor(PaymentRecord::class))
            ->sequence(fn (Sequence $sequence): array => [
                'booking_id' => $this->pick($bookingRows, $sequence->index)['id'],
                'payer_user_id' => $this->pick($userIds, $sequence->index),
            ])
            ->create();

        $this->seedFactoryRows(
            BookingPayment::class,
            fn (int $index): array => $this->bookingState($this->pick($bookingRows, $index), [
                'payment_number' => sprintf('PAY-%s-%06d', now()->format('Y'), $index + 1),
                'booking_quote_id' => null,
                'booking_request_id' => null,
                'booking_extension_id' => null,
                'booking_relocation_id' => null,
                'payment_type' => $index % 5 === 0 ? 'partial_payment' : 'full_payment',
                'payment_purpose' => 'booking_payment',
                'payment_method' => 'internal_test',
                'status' => $index % 5 === 0 ? 'partially_paid' : 'paid',
                'amount' => 126,
                'currency' => 'EUR',
                'required_now_amount' => $index % 5 === 0 ? 90 : 126,
                'remaining_amount' => $index % 5 === 0 ? 36 : 0,
                'remaining_due_at' => $index % 5 === 0 ? now()->addDays(10) : null,
                'payment_deadline_at' => now()->addMinutes(30),
                'paid_at' => $index % 5 === 0 ? null : now()->subDays($index % 14),
            ]),
        );

        $bookingPaymentRows = $this->bookingPaymentRows();

        $this->seedFactoryRows(
            BookingPaymentAttempt::class,
            fn (int $index): array => [
                'booking_payment_id' => $this->pick($bookingPaymentRows, $index)['id'],
                'booking_id' => $this->pick($bookingPaymentRows, $index)['booking_id'],
                'guest_user_id' => $this->pick($bookingPaymentRows, $index)['guest_user_id'],
                'attempt_number' => 1,
                'status' => $index % 6 === 0 ? 'failed' : 'succeeded',
                'payment_method' => 'internal_test',
                'amount' => $this->pick($bookingPaymentRows, $index)['required_now_amount'],
                'currency' => $this->pick($bookingPaymentRows, $index)['currency'],
                'started_at' => now()->subHours($index % 48),
                'succeeded_at' => $index % 6 === 0 ? null : now()->subHours($index % 48),
                'failed_at' => $index % 6 === 0 ? now()->subHours($index % 48) : null,
            ],
        );

        $this->seedFactoryRows(
            BookingPaymentAllocation::class,
            fn (int $index): array => [
                'booking_payment_id' => $this->pick($bookingPaymentRows, $index)['id'],
                'booking_id' => $this->pick($bookingPaymentRows, $index)['booking_id'],
                'allocation_type' => ['accommodation', 'cleaning_fee', 'guest_service_fee', 'deposit'][$index % 4],
                'amount' => [60, 10, 6, 50][$index % 4],
                'currency' => $this->pick($bookingPaymentRows, $index)['currency'],
                'refundable' => $index % 4 === 3,
            ],
        );

        $this->seedFactoryRows(
            BookingPaymentDeadline::class,
            fn (int $index): array => [
                'booking_payment_id' => $this->pick($bookingPaymentRows, $index)['id'],
                'booking_id' => $this->pick($bookingPaymentRows, $index)['booking_id'],
                'deadline_type' => $index % 5 === 0 ? 'remaining_balance' : 'initial_payment',
                'due_at' => now()->addMinutes(30 + ($index % 60)),
                'status' => $index % 5 === 0 ? 'pending' : 'completed',
            ],
        );

        $this->seedFactoryRows(
            BookingPaymentStatusLog::class,
            fn (int $index): array => [
                'booking_payment_id' => $this->pick($bookingPaymentRows, $index)['id'],
                'booking_id' => $this->pick($bookingPaymentRows, $index)['booking_id'],
                'user_id' => $this->pick($bookingPaymentRows, $index)['guest_user_id'],
                'old_status' => 'waiting_payment',
                'new_status' => $this->pick($bookingPaymentRows, $index)['status'],
                'event_key' => 'payment_seeded',
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingRefund::class,
            fn (int $index): array => $this->bookingState($this->pick($bookingRows, $index), [
                'refund_number' => sprintf('REF-%s-%06d', now()->format('Y'), $index + 1),
                'booking_payment_id' => $this->pick($bookingPaymentRows, $index)['id'],
                'refund_type' => ['partial_refund', 'deposit_refund', 'cleaning_fee_refund'][$index % 3],
                'status' => $index % 4 === 0 ? 'completed' : 'pending',
                'amount' => [20, 50, 10][$index % 3],
                'currency' => 'EUR',
                'reason_key' => ['partial_adjustment', 'deposit_return', 'guest_cancelled'][$index % 3],
                'requested_at' => now()->subDays($index % 30),
                'completed_at' => $index % 4 === 0 ? now()->subDays($index % 20) : null,
            ]),
        );

        $this->seedFactoryRows(
            PaymentReceipt::class,
            fn (int $index): array => [
                'booking_id' => $this->pick($bookingPaymentRows, $index)['booking_id'],
                'booking_payment_id' => $this->pick($bookingPaymentRows, $index)['id'],
                'guest_user_id' => $this->pick($bookingPaymentRows, $index)['guest_user_id'],
                'receipt_number' => sprintf('RCT-%s-%06d', now()->format('Y'), $index + 1),
                'status' => $index % 5 === 0 ? 'draft' : 'issued',
                'issued_at' => $index % 5 === 0 ? null : now()->subDays($index % 14),
                'receipt_data_json' => [
                    'payment_number' => $this->pick($bookingPaymentRows, $index)['payment_number'],
                    'amount' => $this->pick($bookingPaymentRows, $index)['amount'],
                    'currency' => $this->pick($bookingPaymentRows, $index)['currency'],
                ],
            ],
        );

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
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $sleepingPlaceIds = $this->ids(SleepingPlace::class);

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

        $this->seedFactoryRows(
            BookingLifecycleEvent::class,
            fn (int $index): array => [
                'booking_id' => $this->pick($bookingIds, $index),
                'event_key' => ['created', 'payment_started', 'confirmed', 'ready_for_check_in', 'guest_checked_in', 'guest_checked_out'][$index % 6],
                'event_type' => ['system', 'payment', 'host_action', 'guest_action'][$index % 4],
                'source_type' => 'bulk_demo_seed',
                'source_id' => $index + 1,
                'user_id' => $this->pick($bookingRows, $index)['guest_user_id'],
                'occurred_at' => now()->subHours($index % 72),
                'context_json' => [],
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
        $bookingsById = collect($bookingRows)->keyBy('id')->all();

        $this->seedMissingOwnedRows(
            BookingNoShowPolicy::class,
            'sleeping_place_id',
            $sleepingPlaceIds,
            fn (int $sleepingPlaceId, int $index): BookingNoShowPolicy => BookingNoShowPolicy::factory()->create([
                'sleeping_place_id' => $sleepingPlaceId,
                'waiting_period_minutes' => [60, 120, 180, 240][$index % 4],
                'same_day_waiting_period_minutes' => 60,
                'night_arrival_waiting_period_minutes' => 240,
                'hold_first_night_on_no_show' => true,
                'release_remaining_nights_after_no_show' => true,
                'refund_deposit_on_no_show' => true,
                'refund_cleaning_fee_on_no_show' => $index % 3 !== 0,
                'refund_service_fee_on_no_show' => $index % 5 === 0,
                'host_payout_rule' => ['policy_based', 'first_night', 'none', 'full_first_day'][$index % 4],
                'guest_penalty_rule' => ['policy_based', 'first_night', 'none', 'custom'][$index % 4],
                'active' => true,
            ]),
        );

        $this->seedMissingOwnedRows(
            BookingNoShowPolicySnapshot::class,
            'booking_id',
            $bookingIds,
            fn (int $bookingId, int $index): BookingNoShowPolicySnapshot => BookingNoShowPolicySnapshot::factory()->create([
                'booking_id' => $bookingId,
                'sleeping_place_id' => $this->pick($bookingRows, $index)['sleeping_place_id'],
                'waiting_period_minutes' => [60, 120, 180, 240][$index % 4],
                'same_day_waiting_period_minutes' => 60,
                'night_arrival_waiting_period_minutes' => 240,
                'hold_first_night_on_no_show' => true,
                'release_remaining_nights_after_no_show' => true,
                'refund_deposit_on_no_show' => true,
                'refund_cleaning_fee_on_no_show' => $index % 3 !== 0,
                'refund_service_fee_on_no_show' => $index % 5 === 0,
                'host_payout_rule' => ['policy_based', 'first_night', 'none', 'full_first_day'][$index % 4],
                'guest_penalty_rule' => ['policy_based', 'first_night', 'none', 'custom'][$index % 4],
                'policy_snapshot_json' => ['source' => 'bulk_demo_seed', 'booking_id' => $bookingId],
            ]),
        );

        $this->seedFactoryRows(
            BookingNoShow::class,
            function (int $index) use ($checkInRows, $bookingsById): array {
                $checkIn = $this->pick($checkInRows, $index);
                $booking = $bookingsById[$checkIn['booking_id']] ?? $this->pick(array_values($bookingsById), $index);
                $waitingMinutes = [60, 120, 180, 240][$index % 4];
                $startedAt = CarbonImmutable::parse($booking['check_in_date'])->setTime(18, 0);
                $waitingUntil = $startedAt->addMinutes($waitingMinutes);
                $refund = $index % 4 === 2 ? 0 : 50 + ($index % 30);
                $penalty = $index % 4 === 2 ? 80 : 20 + ($index % 20);

                return [
                    'no_show_number' => sprintf('NS-%s-%06d', now()->format('Y'), $index + 1),
                    'booking_id' => $checkIn['booking_id'],
                    'booking_check_in_id' => $checkIn['id'],
                    'guest_user_id' => $checkIn['guest_user_id'],
                    'host_user_id' => $checkIn['host_user_id'],
                    'property_id' => $checkIn['property_id'],
                    'room_id' => $checkIn['room_id'],
                    'sleeping_place_id' => $checkIn['sleeping_place_id'],
                    'status' => ['watching', 'host_reported', 'waiting_period_active', 'confirmed_no_show', 'dispute_opened'][$index % 5],
                    'reason_key' => ['guest_did_not_arrive', 'host_reported_guest_absent', 'guest_not_answering', 'guest_no_response_after_waiting_period'][$index % 4],
                    'check_in_date' => $booking['check_in_date'],
                    'planned_check_in_time' => '18:00',
                    'check_in_window' => '18:00-22:00',
                    'no_show_started_at' => $startedAt,
                    'host_reported_at' => $index % 5 === 0 ? null : $startedAt->addMinutes(30),
                    'guest_contacted_at' => $startedAt->addMinutes(35),
                    'guest_last_response_at' => $index % 5 === 2 ? $startedAt->addMinutes(45) : null,
                    'waiting_period_minutes' => $waitingMinutes,
                    'waiting_until' => $waitingUntil,
                    'waiting_expired_at' => $index % 5 === 3 ? $waitingUntil : null,
                    'guest_not_answering' => $index % 5 !== 2,
                    'guest_warned_late_arrival' => $index % 5 === 2,
                    'guest_warned_cancellation' => false,
                    'guest_claimed_arrived' => false,
                    'host_marked_no_show' => $index % 5 !== 0,
                    'guest_response_type' => $index % 5 === 2 ? 'i_am_late' : null,
                    'guest_response_message' => $index % 5 === 2 ? 'no_show.demo.guest_late_message' : null,
                    'host_comment' => 'no_show.demo.host_comment',
                    'guest_comment' => null,
                    'decision_key' => $index % 5 === 3 ? 'confirmed_no_show' : ($index % 5 === 4 ? 'dispute_opened' : null),
                    'decision_at' => $index % 5 >= 3 ? $waitingUntil->addMinutes(10) : null,
                    'decided_by_user_id' => $index % 5 === 3 ? $checkIn['host_user_id'] : null,
                    'refund_or_penalty_status' => $index % 5 === 3 ? 'calculated' : ($index % 5 === 4 ? 'disputed' : 'not_calculated'),
                    'refund_amount' => $index % 5 >= 3 ? $refund : 0,
                    'penalty_amount' => $index % 5 >= 3 ? $penalty : 0,
                    'deposit_refund_amount' => $index % 5 >= 3 ? 50 : 0,
                    'cleaning_fee_refund_amount' => $index % 5 >= 3 ? 10 : 0,
                    'service_fee_refund_amount' => $index % 5 >= 3 && $index % 5 === 0 ? 6 : 0,
                    'host_payout_amount' => $index % 5 === 3 ? $penalty : 0,
                    'currency' => 'EUR',
                    'calendar_release_status' => $index % 5 === 3 ? 'released_remaining_dates' : 'not_released',
                    'dates_released_at' => $index % 5 === 3 ? $waitingUntil->addMinutes(15) : null,
                    'future_support_review_required' => $index % 5 === 4,
                    'future_support_comment' => $index % 5 === 4 ? 'no_show.demo.future_review' : null,
                    'completed_at' => $index % 5 === 3 ? $waitingUntil->addMinutes(20) : null,
                    'closed_at' => null,
                ];
            },
        );

        $noShowRows = BookingNoShow::query()
            ->select(['id', 'booking_id', 'booking_check_in_id', 'guest_user_id', 'host_user_id', 'status', 'reason_key'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingNoShow $noShow): array => [
                'id' => $noShow->id,
                'booking_id' => $noShow->booking_id,
                'booking_check_in_id' => $noShow->booking_check_in_id,
                'guest_user_id' => $noShow->guest_user_id,
                'host_user_id' => $noShow->host_user_id,
                'status' => $noShow->status,
                'reason_key' => $noShow->reason_key,
            ])
            ->all();

        $this->seedFactoryRows(
            BookingNoShowContactAttempt::class,
            fn (int $index): array => [
                'booking_no_show_id' => $this->pick($noShowRows, $index)['id'],
                'booking_id' => $this->pick($noShowRows, $index)['booking_id'],
                'attempted_by_user_id' => $index % 3 === 0 ? $this->pick($noShowRows, $index)['host_user_id'] : null,
                'contact_channel' => ['in_app', 'email', 'message_thread'][$index % 3],
                'attempt_type' => ['automatic_reminder', 'guest_check_request', 'final_warning', 'host_message'][$index % 4],
                'status' => ['sent', 'sent', 'responded', 'expired'][$index % 4],
                'message_key' => 'no_show.messages.guest_response_required',
                'message_text' => null,
                'attempted_at' => now()->subMinutes($index % 180),
                'response_received_at' => $index % 4 === 2 ? now()->subMinutes($index % 120) : null,
                'response_summary' => $index % 4 === 2 ? 'no_show.demo.response_summary' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingNoShowGuestResponse::class,
            fn (int $index): array => [
                'booking_no_show_id' => $this->pick($noShowRows, $index)['id'],
                'booking_id' => $this->pick($noShowRows, $index)['booking_id'],
                'guest_user_id' => $this->pick($noShowRows, $index)['guest_user_id'],
                'response_type' => ['i_am_late', 'i_arrived', 'i_want_to_cancel', 'dispute_no_show'][$index % 4],
                'message' => 'no_show.demo.guest_response',
                'new_arrival_time' => $index % 4 === 0 ? '23:30' : null,
            ],
        );

        $this->seedFactoryRows(
            BookingNoShowMedia::class,
            fn (int $index): array => [
                'booking_no_show_id' => $this->pick($noShowRows, $index)['id'],
                'booking_id' => $this->pick($noShowRows, $index)['booking_id'],
                'uploaded_by_user_id' => $index % 2 === 0 ? $this->pick($noShowRows, $index)['guest_user_id'] : $this->pick($noShowRows, $index)['host_user_id'],
                'media_type' => 'photo',
                'media_role' => ['guest_arrival_evidence', 'host_absence_evidence', 'message_evidence', 'access_problem_evidence'][$index % 4],
                'path' => 'demo/no-show/evidence-'.$index.'.jpg',
                'thumbnail_path' => 'demo/no-show/evidence-'.$index.'-thumb.jpg',
                'caption' => 'no_show.demo.media_caption',
                'visibility' => ['guest_and_host', 'guest_only', 'host_only', 'future_support_only'][$index % 4],
            ],
        );

        $this->seedFactoryRows(
            BookingNoShowStatusLog::class,
            fn (int $index): array => [
                'booking_no_show_id' => $this->pick($noShowRows, $index)['id'],
                'booking_id' => $this->pick($noShowRows, $index)['booking_id'],
                'user_id' => $index % 2 === 0 ? $this->pick($noShowRows, $index)['guest_user_id'] : $this->pick($noShowRows, $index)['host_user_id'],
                'old_status' => null,
                'new_status' => $this->pick($noShowRows, $index)['status'],
                'reason_key' => $this->pick($noShowRows, $index)['reason_key'],
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingNoShowEvent::class,
            fn (int $index): array => [
                'booking_no_show_id' => $this->pick($noShowRows, $index)['id'],
                'booking_id' => $this->pick($noShowRows, $index)['booking_id'],
                'event_key' => ['no_show_watch_started', 'host_reported_no_show', 'guest_contact_attempted', 'no_show_confirmed', 'calendar_released'][$index % 5],
                'event_type' => 'system',
                'source_type' => 'bulk_demo_seed',
                'source_id' => $index + 1,
                'user_id' => $index % 2 === 0 ? $this->pick($noShowRows, $index)['guest_user_id'] : $this->pick($noShowRows, $index)['host_user_id'],
                'occurred_at' => now()->subMinutes($index % 1440),
                'context_json' => [],
            ],
        );

        $this->seedMissingOwnedRows(
            HostUnresponsivePolicy::class,
            'sleeping_place_id',
            $sleepingPlaceIds,
            fn (int $sleepingPlaceId, int $index): HostUnresponsivePolicy => HostUnresponsivePolicy::factory()->create([
                'sleeping_place_id' => $sleepingPlaceId,
                'property_id' => $this->pick($sleepingPlaceRows, $index)['property_id'],
                'pre_check_in_response_minutes' => [120, 180, 240, 360][$index % 4],
                'check_in_response_minutes' => [30, 45, 60, 90][$index % 4],
                'guest_waiting_outside_response_minutes' => [10, 15, 20, 30][$index % 4],
                'night_entry_response_minutes' => [10, 15, 20][$index % 3],
                'urgent_response_minutes' => [5, 10, 15][$index % 3],
                'notify_representative_if_available' => true,
                'auto_show_instructions_if_allowed' => true,
                'auto_block_no_show_while_active' => true,
                'allow_guest_cancellation_after_deadline' => true,
                'allow_guest_relocation_after_deadline' => $index % 5 !== 0,
                'guest_friendly_refund_if_confirmed' => true,
                'active' => true,
            ]),
        );

        $this->seedMissingOwnedRows(
            HostUnresponsivePolicySnapshot::class,
            'booking_id',
            $bookingIds,
            fn (int $bookingId, int $index): HostUnresponsivePolicySnapshot => HostUnresponsivePolicySnapshot::factory()->create([
                'booking_id' => $bookingId,
                'sleeping_place_id' => $this->pick($bookingRows, $index)['sleeping_place_id'],
                'property_id' => $this->pick($bookingRows, $index)['property_id'],
                'pre_check_in_response_minutes' => [120, 180, 240, 360][$index % 4],
                'check_in_response_minutes' => [30, 45, 60, 90][$index % 4],
                'guest_waiting_outside_response_minutes' => [10, 15, 20, 30][$index % 4],
                'night_entry_response_minutes' => [10, 15, 20][$index % 3],
                'urgent_response_minutes' => [5, 10, 15][$index % 3],
                'notify_representative_if_available' => true,
                'auto_show_instructions_if_allowed' => true,
                'auto_block_no_show_while_active' => true,
                'allow_guest_cancellation_after_deadline' => true,
                'allow_guest_relocation_after_deadline' => $index % 5 !== 0,
                'guest_friendly_refund_if_confirmed' => true,
                'policy_snapshot_json' => ['source' => 'bulk_demo_seed', 'booking_id' => $bookingId],
            ]),
        );

        $hostRepresentativeRows = HostRepresentative::query()
            ->select(['id', 'host_user_id', 'representative_user_id', 'name', 'phone', 'email'])
            ->where('active', true)
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (HostRepresentative $representative): array => [
                'id' => $representative->id,
                'host_user_id' => $representative->host_user_id,
                'representative_user_id' => $representative->representative_user_id,
                'name' => $representative->name,
                'contact' => $representative->phone ?: $representative->email,
            ])
            ->all();
        $representativesByHostId = collect($hostRepresentativeRows)->keyBy('host_user_id')->all();
        $representativesById = collect($hostRepresentativeRows)->keyBy('id')->all();

        $this->seedFactoryRows(
            BookingHostUnresponsiveCase::class,
            function (int $index) use ($checkInRows, $bookingsById, $representativesByHostId): array {
                $checkIn = $this->pick($checkInRows, $index);
                $booking = $bookingsById[$checkIn['booking_id']] ?? $this->pick(array_values($bookingsById), $index);
                $representative = $representativesByHostId[$checkIn['host_user_id']] ?? null;
                $deadlineMinutes = [30, 45, 60, 90][$index % 4];
                $startedAt = CarbonImmutable::parse($booking['check_in_date'])->setTime(18, 0)->addMinutes($index % 25);
                $deadlineAt = $startedAt->addMinutes($deadlineMinutes);
                $status = ['reported', 'waiting_host_response', 'guest_waiting', 'host_responded', 'access_resolved', 'unresolved'][$index % 6];
                $decisionKey = match ($status) {
                    'host_responded' => 'host_responded',
                    'access_resolved' => 'access_resolved',
                    'unresolved' => 'confirmed_host_unresponsive',
                    default => null,
                };

                return [
                    'case_number' => sprintf('HU-%s-%06d', now()->format('Y'), $index + 1),
                    'booking_id' => $checkIn['booking_id'],
                    'booking_check_in_id' => $checkIn['id'],
                    'booking_stay_id' => null,
                    'guest_user_id' => $checkIn['guest_user_id'],
                    'host_user_id' => $checkIn['host_user_id'],
                    'host_representative_id' => $representative['id'] ?? null,
                    'property_id' => $checkIn['property_id'],
                    'room_id' => $checkIn['room_id'],
                    'sleeping_place_id' => $checkIn['sleeping_place_id'],
                    'case_type' => ['check_in_no_response', 'access_problem_no_response', 'night_entry_no_response', 'self_check_in_failed'][$index % 4],
                    'reason_key' => ['host_not_answering_messages', 'door_code_not_working', 'guest_waiting_outside', 'instruction_missing'][$index % 4],
                    'status' => $status,
                    'check_in_date' => $booking['check_in_date'],
                    'planned_check_in_time' => '18:00',
                    'check_in_window' => '18:00-22:00',
                    'actual_guest_arrival_at' => $index % 4 === 0 ? $startedAt : null,
                    'guest_marked_arrived' => $index % 4 === 0,
                    'guest_waiting_outside' => $index % 3 === 0,
                    'guest_at_address' => $index % 2 === 0,
                    'guest_feels_unsafe' => $index % 9 === 0,
                    'instruction_was_available' => $index % 5 !== 0,
                    'exact_address_was_shown' => $index % 5 !== 0,
                    'door_code_was_shown' => $index % 4 === 1,
                    'intercom_code_was_shown' => $index % 4 === 2,
                    'key_safe_code_was_shown' => $index % 4 === 3,
                    'host_contact_was_shown' => true,
                    'representative_contact_was_shown' => $representative !== null,
                    'host_contact_attempts_count' => 1 + ($index % 3),
                    'representative_contact_attempts_count' => $representative === null ? 0 : 1,
                    'last_host_contact_attempt_at' => $startedAt,
                    'last_representative_contact_attempt_at' => $representative === null ? null : $startedAt->addMinutes(2),
                    'host_last_response_at' => in_array($status, ['host_responded', 'access_resolved'], true) ? $startedAt->addMinutes(10) : null,
                    'representative_last_response_at' => $status === 'access_resolved' && $representative !== null ? $startedAt->addMinutes(12) : null,
                    'response_deadline_minutes' => $deadlineMinutes,
                    'response_deadline_at' => $deadlineAt,
                    'response_deadline_expired_at' => $status === 'unresolved' ? $deadlineAt : null,
                    'guest_wants_help' => true,
                    'guest_wants_cancellation' => $status === 'unresolved',
                    'guest_wants_refund' => $status === 'unresolved',
                    'guest_wants_relocation' => $index % 6 === 5,
                    'host_response' => $status === 'host_responded' ? 'host_unresponsive.demo.host_response' : null,
                    'representative_response' => $status === 'access_resolved' ? 'host_unresponsive.demo.representative_response' : null,
                    'guest_comment' => 'host_unresponsive.demo.guest_comment',
                    'host_comment' => $status === 'host_responded' ? 'host_unresponsive.demo.host_comment' : null,
                    'decision_key' => $decisionKey,
                    'decision_at' => $decisionKey === null ? null : $deadlineAt,
                    'decided_by_user_id' => $decisionKey === null ? null : $checkIn['host_user_id'],
                    'refund_status' => $status === 'unresolved' ? 'review_started' : null,
                    'refund_amount' => $status === 'unresolved' ? 80 + ($index % 40) : 0,
                    'compensation_amount_future' => 0,
                    'currency' => 'EUR',
                    'future_support_review_required' => $index % 11 === 0,
                    'future_support_comment' => $index % 11 === 0 ? 'host_unresponsive.demo.future_review' : null,
                    'resolved_at' => in_array($status, ['access_resolved', 'resolved'], true) ? $deadlineAt : null,
                    'closed_at' => null,
                ];
            },
        );

        $hostUnresponsiveRows = BookingHostUnresponsiveCase::query()
            ->select(['id', 'booking_id', 'booking_check_in_id', 'guest_user_id', 'host_user_id', 'host_representative_id', 'status', 'reason_key', 'case_type'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(function (BookingHostUnresponsiveCase $case) use ($representativesById): array {
                $representative = $case->host_representative_id ? ($representativesById[$case->host_representative_id] ?? null) : null;

                return [
                    'id' => $case->id,
                    'booking_id' => $case->booking_id,
                    'booking_check_in_id' => $case->booking_check_in_id,
                    'guest_user_id' => $case->guest_user_id,
                    'host_user_id' => $case->host_user_id,
                    'host_representative_id' => $case->host_representative_id,
                    'representative_user_id' => $representative['representative_user_id'] ?? null,
                    'representative_name' => $representative['name'] ?? null,
                    'representative_contact' => $representative['contact'] ?? null,
                    'status' => $case->status,
                    'reason_key' => $case->reason_key,
                    'case_type' => $case->case_type,
                ];
            })
            ->all();

        $this->seedFactoryRows(
            HostUnresponsiveContactAttempt::class,
            function (int $index) use ($hostUnresponsiveRows): array {
                $case = $this->pick($hostUnresponsiveRows, $index);
                $targetsRepresentative = $case['host_representative_id'] !== null && $index % 3 === 1;

                return [
                    'host_unresponsive_case_id' => $case['id'],
                    'booking_id' => $case['booking_id'],
                    'target_user_id' => $targetsRepresentative ? $case['representative_user_id'] : $case['host_user_id'],
                    'target_type' => $targetsRepresentative ? 'host_representative' : 'host',
                    'target_name_snapshot' => $targetsRepresentative ? $case['representative_name'] : null,
                    'target_contact_snapshot' => $targetsRepresentative ? $case['representative_contact'] : null,
                    'contact_channel' => ['in_app', 'message_thread', 'email'][$index % 3],
                    'attempt_type' => ['urgent_check_in_alert', 'access_problem_alert', 'guest_waiting_alert', 'final_warning'][$index % 4],
                    'status' => ['sent', 'sent', 'responded', 'expired'][$index % 4],
                    'message_key' => 'host_unresponsive.messages.urgent_host_alert_sent',
                    'message_text' => null,
                    'attempted_at' => now()->subMinutes($index % 180),
                    'response_received_at' => $index % 4 === 2 ? now()->subMinutes($index % 120) : null,
                    'response_summary' => $index % 4 === 2 ? 'host_unresponsive.demo.response_summary' : null,
                ];
            },
        );

        $this->seedFactoryRows(
            HostUnresponsiveGuestAction::class,
            fn (int $index): array => [
                'host_unresponsive_case_id' => $this->pick($hostUnresponsiveRows, $index)['id'],
                'booking_id' => $this->pick($hostUnresponsiveRows, $index)['booking_id'],
                'guest_user_id' => $this->pick($hostUnresponsiveRows, $index)['guest_user_id'],
                'action_type' => ['reported_host_not_answering', 'marked_at_address', 'marked_waiting_outside', 'requested_cancellation', 'requested_relocation'][$index % 5],
                'message' => 'host_unresponsive.demo.guest_action',
                'guest_location_note' => $index % 3 === 0 ? 'host_unresponsive.demo.location_note' : null,
                'new_waiting_until' => $index % 5 === 4 ? now()->addMinutes(30) : null,
            ],
        );

        $this->seedFactoryRows(
            HostUnresponsiveHostResponse::class,
            fn (int $index): array => [
                'host_unresponsive_case_id' => $this->pick($hostUnresponsiveRows, $index)['id'],
                'booking_id' => $this->pick($hostUnresponsiveRows, $index)['booking_id'],
                'host_user_id' => $this->pick($hostUnresponsiveRows, $index)['host_user_id'],
                'response_type' => ['i_am_available', 'instruction_sent', 'access_details_sent', 'deny_unresponsive', 'send_message'][$index % 5],
                'message' => 'host_unresponsive.demo.host_response',
                'instruction_resent' => $index % 5 === 1,
                'access_details_provided' => $index % 5 === 2,
                'new_arrival_time_proposed' => $index % 5 === 0 ? '19:00' : null,
                'representative_assigned' => $this->pick($hostUnresponsiveRows, $index)['host_representative_id'] !== null,
            ],
        );

        $this->seedFactoryRows(
            HostUnresponsiveRepresentativeResponse::class,
            fn (int $index): array => [
                'host_unresponsive_case_id' => $this->pick($hostUnresponsiveRows, $index)['id'],
                'booking_id' => $this->pick($hostUnresponsiveRows, $index)['booking_id'],
                'host_representative_id' => $this->pick($hostUnresponsiveRows, $index)['host_representative_id'],
                'representative_user_id' => $this->pick($hostUnresponsiveRows, $index)['representative_user_id'],
                'response_type' => ['i_can_help', 'i_am_on_the_way', 'access_helped', 'guest_checked_in', 'cannot_help'][$index % 5],
                'message' => 'host_unresponsive.demo.representative_response',
                'will_meet_guest' => $index % 5 <= 1,
                'estimated_arrival_time' => $index % 5 === 1 ? '18:30' : null,
                'access_help_provided' => $index % 5 === 2,
                'keys_handed_over' => $index % 5 === 3,
                'guest_checked_in' => $index % 5 === 3,
            ],
        );

        $this->seedFactoryRows(
            HostUnresponsiveMedia::class,
            fn (int $index): array => [
                'host_unresponsive_case_id' => $this->pick($hostUnresponsiveRows, $index)['id'],
                'booking_id' => $this->pick($hostUnresponsiveRows, $index)['booking_id'],
                'uploaded_by_user_id' => $index % 2 === 0 ? $this->pick($hostUnresponsiveRows, $index)['guest_user_id'] : $this->pick($hostUnresponsiveRows, $index)['host_user_id'],
                'media_type' => ['photo', 'screenshot'][$index % 2],
                'media_role' => ['guest_waiting_evidence', 'message_screenshot', 'door_code_problem_evidence', 'host_response_evidence'][$index % 4],
                'path' => 'demo/host-unresponsive/evidence-'.$index.'.jpg',
                'thumbnail_path' => 'demo/host-unresponsive/evidence-'.$index.'-thumb.jpg',
                'caption' => 'host_unresponsive.demo.media_caption',
                'visibility' => ['guest_and_host', 'guest_only', 'host_only', 'future_support_only'][$index % 4],
            ],
        );

        $this->seedFactoryRows(
            HostUnresponsiveStatusLog::class,
            fn (int $index): array => [
                'host_unresponsive_case_id' => $this->pick($hostUnresponsiveRows, $index)['id'],
                'booking_id' => $this->pick($hostUnresponsiveRows, $index)['booking_id'],
                'user_id' => $index % 2 === 0 ? $this->pick($hostUnresponsiveRows, $index)['guest_user_id'] : $this->pick($hostUnresponsiveRows, $index)['host_user_id'],
                'old_status' => null,
                'new_status' => $this->pick($hostUnresponsiveRows, $index)['status'],
                'reason_key' => $this->pick($hostUnresponsiveRows, $index)['reason_key'],
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            HostUnresponsiveEvent::class,
            fn (int $index): array => [
                'host_unresponsive_case_id' => $this->pick($hostUnresponsiveRows, $index)['id'],
                'booking_id' => $this->pick($hostUnresponsiveRows, $index)['booking_id'],
                'event_key' => ['host_unresponsive_reported', 'host_contact_attempted', 'representative_contact_attempted', 'access_resolved', 'host_unresponsive_confirmed'][$index % 5],
                'event_type' => 'system',
                'source_type' => 'bulk_demo_seed',
                'source_id' => $index + 1,
                'user_id' => $index % 2 === 0 ? $this->pick($hostUnresponsiveRows, $index)['guest_user_id'] : $this->pick($hostUnresponsiveRows, $index)['host_user_id'],
                'occurred_at' => now()->subMinutes($index % 1440),
                'context_json' => [],
            ],
        );

        $checkInsByBookingId = collect($checkInRows)->keyBy('booking_id')->all();
        $checkOutsByBookingId = collect($checkOutRows)->keyBy('booking_id')->all();

        $this->seedFactoryRows(
            BookingListingMismatchReport::class,
            function (int $index) use ($bookingRows, $checkInsByBookingId, $checkOutsByBookingId): array {
                $booking = $this->pick($bookingRows, $index);
                $checkIn = $checkInsByBookingId[$booking['id']] ?? null;
                $checkOut = $checkOutsByBookingId[$booking['id']] ?? null;
                $type = ['missing_locker', 'missing_wifi', 'dirty_room', 'wrong_bunk_level', 'photos_mismatch', 'mold'][$index % 6];
                $severity = ['low', 'medium', 'high', 'urgent', 'unsafe'][$index % 5];
                $status = ['reported', 'waiting_host_response', 'host_responded', 'confirmed', 'resolution_offered', 'dispute_opened'][$index % 6];
                $reportedAt = CarbonImmutable::parse($booking['check_in_date'])->setTime(19, 0)->addMinutes($index % 90);
                $resolutionType = in_array($status, ['resolution_offered', 'confirmed'], true)
                    ? ['fix_problem', 'cleaning', 'partial_refund', 'relocation'][$index % 4]
                    : null;

                return [
                    'mismatch_number' => sprintf('MM-%s-%06d', now()->format('Y'), $index + 1),
                    'booking_id' => $booking['id'],
                    'booking_stay_id' => null,
                    'booking_check_in_id' => $checkIn['id'] ?? null,
                    'booking_check_out_id' => $index % 7 === 0 ? ($checkOut['id'] ?? null) : null,
                    'guest_user_id' => $booking['guest_user_id'],
                    'host_user_id' => $booking['host_user_id'],
                    'property_id' => $booking['property_id'],
                    'room_id' => $booking['room_id'],
                    'sleeping_place_id' => $booking['sleeping_place_id'],
                    'source_type' => ['guest_report', 'check_in_problem', 'stay_problem', 'cancellation', 'complaint'][$index % 5],
                    'source_id' => $index + 1,
                    'mismatch_type' => $type,
                    'severity' => $severity,
                    'status' => $status,
                    'reported_at' => $reportedAt,
                    'discovered_at' => $reportedAt->subMinutes(20),
                    'guest_description' => 'listing_mismatch.demo.guest_description',
                    'host_response' => in_array($status, ['host_responded', 'resolution_offered'], true) ? 'listing_mismatch.demo.host_response' : null,
                    'what_was_promised' => 'listing_mismatch.demo.promised_'.$type,
                    'what_was_actual' => 'listing_mismatch.demo.actual_'.$type,
                    'guest_wants_to_stay' => $severity !== 'unsafe',
                    'guest_wants_fix' => ! in_array($severity, ['urgent', 'unsafe'], true),
                    'guest_wants_relocation' => in_array($severity, ['high', 'urgent', 'unsafe'], true),
                    'guest_wants_cancellation' => $severity === 'unsafe',
                    'guest_wants_refund' => in_array($severity, ['high', 'urgent', 'unsafe'], true),
                    'guest_wants_compensation' => $severity !== 'low',
                    'host_accepts_problem' => in_array($status, ['confirmed', 'resolution_offered'], true) ? true : null,
                    'host_offered_fix' => $resolutionType === 'fix_problem',
                    'host_offered_relocation' => $resolutionType === 'relocation',
                    'host_offered_refund' => $resolutionType === 'partial_refund',
                    'host_offered_compensation' => $status === 'resolution_offered',
                    'host_denied_problem' => $status === 'dispute_opened',
                    'resolution_type' => $resolutionType,
                    'resolution_status' => $resolutionType === null ? 'not_started' : 'offered',
                    'compensation_amount' => $status === 'resolution_offered' ? 10 + ($index % 40) : 0,
                    'refund_amount' => in_array($severity, ['high', 'urgent', 'unsafe'], true) ? 20 + ($index % 80) : 0,
                    'price_difference_amount' => $resolutionType === 'relocation' ? 15 + ($index % 30) : 0,
                    'currency' => 'EUR',
                    'snapshot_compared' => true,
                    'auto_match_confidence' => in_array($type, ['missing_locker', 'missing_wifi', 'wrong_bunk_level'], true) ? 0.85 : 0.45,
                    'future_review_required' => $status === 'dispute_opened' || $severity === 'unsafe',
                    'future_review_comment' => $status === 'dispute_opened' ? 'listing_mismatch.demo.future_review' : null,
                    'resolved_at' => $status === 'confirmed' ? $reportedAt->addHours(2) : null,
                    'closed_at' => null,
                ];
            },
        );

        $mismatchRows = BookingListingMismatchReport::query()
            ->select(['id', 'booking_id', 'guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'mismatch_type', 'severity', 'status', 'resolution_type'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingListingMismatchReport $report): array => [
                'id' => $report->id,
                'booking_id' => $report->booking_id,
                'guest_user_id' => $report->guest_user_id,
                'host_user_id' => $report->host_user_id,
                'property_id' => $report->property_id,
                'room_id' => $report->room_id,
                'sleeping_place_id' => $report->sleeping_place_id,
                'mismatch_type' => $report->mismatch_type,
                'severity' => $report->severity,
                'status' => $report->status,
                'resolution_type' => $report->resolution_type,
            ])
            ->all();

        $this->seedFactoryRows(
            BookingListingMismatchItem::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'item_key' => ['has_wifi', 'has_locker', 'has_bedding', 'bed_type', 'bunk_level', 'photo_accuracy'][$index % 6],
                'item_type' => ['property_amenity', 'sleeping_place_feature', 'cleanliness', 'photo', 'safety'][$index % 5],
                'promised_value' => 'listed',
                'actual_value' => $index % 4 === 0 ? 'missing' : 'different',
                'snapshot_source_type' => 'booking_listing_snapshot',
                'snapshot_source_id' => $this->pick($mismatchRows, $index)['booking_id'],
                'is_confirmed' => $index % 5 === 0 ? false : ($index % 3 === 0 ? true : null),
                'confidence_score' => $index % 3 === 0 ? 0.85 : 0.45,
                'severity' => $this->pick($mismatchRows, $index)['severity'],
                'guest_note' => 'listing_mismatch.demo.item_guest_note',
                'host_note' => $index % 4 === 0 ? 'listing_mismatch.demo.item_host_note' : null,
            ],
        );

        $mismatchItemIds = $this->ids(BookingListingMismatchItem::class);

        $this->seedFactoryRows(
            BookingListingMismatchMedia::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'booking_id' => $this->pick($mismatchRows, $index)['booking_id'],
                'uploaded_by_user_id' => $index % 2 === 0 ? $this->pick($mismatchRows, $index)['guest_user_id'] : $this->pick($mismatchRows, $index)['host_user_id'],
                'media_type' => ['photo', 'screenshot'][$index % 2],
                'media_role' => ['guest_real_photo', 'missing_amenity_evidence', 'dirty_place_evidence', 'host_fix_evidence'][$index % 4],
                'path' => 'demo/listing-mismatch/evidence-'.$index.'.jpg',
                'thumbnail_path' => 'demo/listing-mismatch/evidence-'.$index.'-thumb.jpg',
                'caption' => 'listing_mismatch.demo.media_caption',
                'visibility' => ['guest_and_host', 'guest_only', 'host_only', 'future_review_only'][$index % 4],
                'related_mismatch_item_id' => $index % 3 === 0 ? $this->pick($mismatchItemIds, $index) : null,
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchHostResponse::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'host_user_id' => $this->pick($mismatchRows, $index)['host_user_id'],
                'response_type' => ['accept', 'deny', 'ask_for_more_evidence', 'offer_fix', 'offer_cleaning', 'offer_relocation', 'offer_refund', 'offer_compensation'][$index % 8],
                'message' => 'listing_mismatch.demo.host_response',
                'accepts_problem' => $index % 8 === 1 ? false : ($index % 3 === 0 ? true : null),
                'proposed_resolution_type' => ['fix_problem', 'cleaning', 'relocation', 'partial_refund', 'compensation'][$index % 5],
                'offered_compensation_amount' => $index % 8 === 7 ? 15 + ($index % 40) : null,
                'offered_refund_amount' => $index % 8 === 6 ? 20 + ($index % 60) : null,
                'currency' => 'EUR',
                'alternative_sleeping_place_id' => $index % 8 === 5 ? $this->pick($mismatchRows, $index)['sleeping_place_id'] : null,
                'maintenance_request_id' => $index % 8 === 4 ? $this->pick($mismatchRows, $index)['id'] : null,
                'cleaning_task_id' => $index % 8 === 4 ? $this->pick($mismatchRows, $index)['id'] : null,
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchGuestResponse::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'guest_user_id' => $this->pick($mismatchRows, $index)['guest_user_id'],
                'response_type' => ['accept_resolution', 'reject_resolution', 'provide_more_evidence', 'request_relocation', 'request_cancellation', 'request_refund', 'request_compensation', 'open_dispute'][$index % 8],
                'message' => 'listing_mismatch.demo.guest_response',
                'accepted_resolution_type' => $index % 8 === 0 ? ['fix_problem', 'cleaning', 'relocation', 'partial_refund'][$index % 4] : null,
                'accepted_compensation_amount' => $index % 8 === 0 ? 10 + ($index % 30) : null,
                'accepted_refund_amount' => $index % 8 === 0 ? 15 + ($index % 50) : null,
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchResolutionOption::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'resolution_type' => ['fix_problem', 'cleaning', 'repair', 'partial_refund', 'relocation', 'cancellation', 'compensation'][$index % 7],
                'status' => ['offered', 'accepted', 'in_progress', 'completed', 'rejected'][$index % 5],
                'description' => 'listing_mismatch.demo.resolution_description',
                'amount' => $index % 7 >= 3 ? 10 + ($index % 90) : null,
                'currency' => 'EUR',
                'sleeping_place_id' => $index % 7 === 4 ? $this->pick($mismatchRows, $index)['sleeping_place_id'] : null,
                'cleaning_task_id' => $index % 7 === 1 ? $this->pick($mismatchRows, $index)['id'] : null,
                'maintenance_request_id' => $index % 7 === 2 ? $this->pick($mismatchRows, $index)['id'] : null,
                'offered_by_user_id' => $this->pick($mismatchRows, $index)['host_user_id'],
                'accepted_by_user_id' => $index % 5 === 1 ? $this->pick($mismatchRows, $index)['guest_user_id'] : null,
                'offered_at' => now()->subHours($index % 72),
                'accepted_at' => $index % 5 === 1 ? now()->subHours($index % 48) : null,
                'rejected_at' => $index % 5 === 4 ? now()->subHours($index % 48) : null,
                'completed_at' => $index % 5 === 3 ? now()->subHours($index % 24) : null,
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchCompensationLine::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'line_type' => ['partial_refund', 'cleaning_fee_refund', 'price_difference_refund', 'inconvenience_compensation'][$index % 4],
                'label_key' => 'listing_mismatch.compensation_lines.'.(['partial_refund', 'cleaning_fee_refund', 'price_difference_refund', 'inconvenience_compensation'][$index % 4]),
                'amount' => 5 + ($index % 75),
                'currency' => 'EUR',
                'calculation_type' => ['fixed', 'percent_of_booking', 'unused_nights'][$index % 3],
                'percent' => $index % 3 === 1 ? 10 + ($index % 25) : null,
                'nights_count' => $index % 3 === 2 ? 1 + ($index % 5) : null,
                'refundable' => true,
                'payable_to_guest' => true,
                'deduct_from_host_payout' => $index % 2 === 0,
                'reason_key' => $this->pick($mismatchRows, $index)['mismatch_type'],
                'sort_order' => ($index % 20) + 1,
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchWarning::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'warning_key' => ['claimed_missing_amenity_was_listed', 'claimed_feature_was_not_listed', 'photo_evidence_missing', 'mismatch_may_require_relocation', 'unsafe_claim_requires_urgent_action'][$index % 5],
                'severity' => $index % 5 === 4 ? 'urgent' : 'warning',
                'message_key' => 'listing_mismatch.warning_keys.'.(['claimed_missing_amenity_was_listed', 'claimed_feature_was_not_listed', 'photo_evidence_missing', 'mismatch_may_require_relocation', 'unsafe_claim_requires_urgent_action'][$index % 5]),
                'message_params_json' => [],
                'visible_to_guest' => true,
                'visible_to_host' => true,
                'blocking' => $index % 5 === 4,
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchStatusLog::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'booking_id' => $this->pick($mismatchRows, $index)['booking_id'],
                'user_id' => $index % 2 === 0 ? $this->pick($mismatchRows, $index)['guest_user_id'] : $this->pick($mismatchRows, $index)['host_user_id'],
                'old_status' => null,
                'new_status' => $this->pick($mismatchRows, $index)['status'],
                'reason_key' => $this->pick($mismatchRows, $index)['mismatch_type'],
                'note' => 'listing_mismatch.demo.status_note',
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingListingMismatchEvent::class,
            fn (int $index): array => [
                'booking_listing_mismatch_report_id' => $this->pick($mismatchRows, $index)['id'],
                'booking_id' => $this->pick($mismatchRows, $index)['booking_id'],
                'event_key' => ['mismatch_reported', 'snapshot_compared', 'host_notified', 'host_responded', 'resolution_offered', 'mismatch_confirmed', 'dispute_opened'][$index % 7],
                'event_type' => 'system',
                'source_type' => 'bulk_demo_seed',
                'source_id' => $index + 1,
                'user_id' => $index % 2 === 0 ? $this->pick($mismatchRows, $index)['guest_user_id'] : $this->pick($mismatchRows, $index)['host_user_id'],
                'occurred_at' => now()->subMinutes($index % 1440),
                'context_json' => [],
            ],
        );

        $this->seedMissingOwnedRows(
            BookingCheckInInstruction::class,
            'booking_check_in_id',
            array_column($checkInRows, 'id'),
            fn (int $checkInId, int $index): BookingCheckInInstruction => BookingCheckInInstruction::factory()->create([
                'booking_check_in_id' => $checkInId,
                'booking_id' => $this->pick($checkInRows, $index)['booking_id'],
                'property_id' => $this->pick($checkInRows, $index)['property_id'],
                'room_id' => $this->pick($checkInRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($checkInRows, $index)['sleeping_place_id'],
                'visible_from' => now()->subDay(),
                'visible_until' => now()->addDays(7),
            ]),
        );

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
            BookingCheckInAccessDisclosure::class,
            fn (int $index): array => [
                'booking_check_in_id' => $this->pick($checkInRows, $index)['id'],
                'booking_id' => $this->pick($checkInRows, $index)['booking_id'],
                'guest_user_id' => $this->pick($checkInRows, $index)['guest_user_id'],
                'disclosure_type' => ['exact_address', 'host_contact', 'door_code'][$index % 3],
                'shown_by_user_id' => $this->pick($checkInRows, $index)['guest_user_id'],
            ],
        );

        $this->seedFactoryRows(
            BookingCheckInStep::class,
            fn (int $index): array => [
                'booking_check_in_id' => $this->pick($checkInRows, $index)['id'],
                'step_key' => ['show_instruction', 'guest_arrived', 'guest_confirmed', 'host_confirmed'][$index % 4],
                'status' => $index % 3 === 0 ? 'completed' : 'pending',
                'completed_by_user_id' => $index % 3 === 0 ? $this->pick($checkInRows, $index)['guest_user_id'] : null,
                'completed_at' => $index % 3 === 0 ? now()->subHours($index % 24) : null,
                'sort_order' => ($index % 14) + 1,
            ],
        );

        $this->seedFactoryRows(
            BookingCheckInMedia::class,
            fn (int $index): array => [
                'booking_check_in_id' => $this->pick($checkInRows, $index)['id'],
                'booking_id' => $this->pick($checkInRows, $index)['booking_id'],
                'uploaded_by_user_id' => $index % 2 === 0 ? $this->pick($checkInRows, $index)['guest_user_id'] : $this->pick($checkInRows, $index)['host_user_id'],
                'media_role' => ['before_check_in_sleeping_place', 'before_check_in_room', 'existing_damage'][$index % 3],
                'path' => sprintf('bulk-demo/check-in-%04d.jpg', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            BookingCheckInProblem::class,
            fn (int $index): array => [
                'booking_check_in_id' => $this->pick($checkInRows, $index)['id'],
                'booking_id' => $this->pick($checkInRows, $index)['booking_id'],
                'guest_user_id' => $this->pick($checkInRows, $index)['guest_user_id'],
                'host_user_id' => $this->pick($checkInRows, $index)['host_user_id'],
                'property_id' => $this->pick($checkInRows, $index)['property_id'],
                'room_id' => $this->pick($checkInRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($checkInRows, $index)['sleeping_place_id'],
                'problem_type' => ['cannot_find_address', 'host_not_answering', 'listing_mismatch', 'other'][$index % 4],
                'severity' => ['low', 'medium', 'high', 'urgent'][$index % 4],
            ],
        );

        $this->seedFactoryRows(
            BookingCheckInStatusLog::class,
            fn (int $index): array => [
                'booking_check_in_id' => $this->pick($checkInRows, $index)['id'],
                'booking_id' => $this->pick($checkInRows, $index)['booking_id'],
                'user_id' => $this->pick($checkInRows, $index)['guest_user_id'],
                'old_status' => $index % 2 === 0 ? 'scheduled' : null,
                'new_status' => ['instructions_available', 'guest_arrived', 'guest_confirmed', 'checked_in'][$index % 4],
            ],
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
            BookingCheckOutStep::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'step_key' => ['guest_confirm_checkout', 'keys_returned', 'inventory_checked', 'review_requested'][$index % 4],
                'status' => $index % 3 === 0 ? 'completed' : 'pending',
                'completed_by_user_id' => $index % 3 === 0 ? $this->pick($checkOutRows, $index)['host_user_id'] : null,
                'completed_at' => $index % 3 === 0 ? now()->subHours($index % 48) : null,
                'required' => $index % 5 !== 0,
                'sort_order' => ($index % 17) + 1,
            ],
        );

        $this->seedFactoryRows(
            BookingCheckOutMedia::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'booking_id' => $this->pick($checkOutRows, $index)['booking_id'],
                'uploaded_by_user_id' => $index % 2 === 0 ? $this->pick($checkOutRows, $index)['guest_user_id'] : $this->pick($checkOutRows, $index)['host_user_id'],
                'media_role' => ['after_checkout_sleeping_place', 'after_checkout_room', 'damage_evidence', 'forgotten_item_photo'][$index % 4],
                'path' => sprintf('bulk-demo/check-out-%04d.jpg', $index + 1),
                'visibility' => ['guest_and_host', 'host_only', 'guest_only', 'internal'][$index % 4],
            ],
        );

        $this->seedFactoryRows(
            BookingCheckOutInventoryCheck::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'booking_id' => $this->pick($checkOutRows, $index)['booking_id'],
                'item_name_snapshot' => ['key', 'access_card', 'locker', 'bedding', 'towel'][$index % 5],
                'returned' => $index % 6 !== 0,
                'lost' => $index % 11 === 0,
                'damaged' => $index % 13 === 0,
                'needs_replacement' => $index % 11 === 0 || $index % 13 === 0,
                'deduction_requested' => $index % 11 === 0,
                'deduction_amount' => $index % 11 === 0 ? 15 : null,
                'currency' => 'EUR',
            ],
        );

        $this->seedFactoryRows(
            BookingCheckOutIssueReport::class,
            fn (int $index): array => $this->bookingCheckOutState($this->pick($checkOutRows, $index)),
        );

        $this->seedFactoryRows(
            BookingCheckOutIssue::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'booking_id' => $this->pick($checkOutRows, $index)['booking_id'],
                'guest_user_id' => $this->pick($checkOutRows, $index)['guest_user_id'],
                'host_user_id' => $this->pick($checkOutRows, $index)['host_user_id'],
                'property_id' => $this->pick($checkOutRows, $index)['property_id'],
                'room_id' => $this->pick($checkOutRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($checkOutRows, $index)['sleeping_place_id'],
                'issue_type' => ['damage', 'extra_dirt', 'lost_key', 'forgotten_items'][$index % 4],
                'severity' => ['low', 'medium', 'high', 'urgent'][$index % 4],
                'status' => ['reported', 'waiting_guest_response', 'resolved', 'deposit_deduction_requested'][$index % 4],
                'amount_requested' => $index % 4 === 3 ? 20 : null,
                'currency' => 'EUR',
            ],
        );

        $this->seedFactoryRows(
            BookingDepositDecision::class,
            fn (int $index): array => $this->bookingCheckOutState($this->pick($checkOutRows, $index)),
        );

        $this->seedFactoryRows(
            BookingForgottenItem::class,
            fn (int $index): array => $this->bookingCheckOutState($this->pick($checkOutRows, $index), [
                'property_id' => $this->pick($checkOutRows, $index)['property_id'],
                'room_id' => $this->pick($checkOutRows, $index)['room_id'],
                'sleeping_place_id' => $this->pick($checkOutRows, $index)['sleeping_place_id'],
                'item_name' => sprintf('Bulk forgotten item %04d', $index + 1),
                'return_method' => ['pickup', 'shipping', 'host_keeps_until_contact'][$index % 3],
            ]),
        );

        $this->seedFactoryRows(
            BookingCheckOutStatusLog::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'booking_id' => $this->pick($checkOutRows, $index)['booking_id'],
                'user_id' => $this->pick($checkOutRows, $index)['host_user_id'],
                'old_status' => $index % 2 === 0 ? 'scheduled' : null,
                'new_status' => ['guest_checked_out', 'waiting_inspection', 'completed', 'closed'][$index % 4],
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingCheckOutEvent::class,
            fn (int $index): array => [
                'booking_check_out_id' => $this->pick($checkOutRows, $index)['id'],
                'booking_id' => $this->pick($checkOutRows, $index)['booking_id'],
                'event_key' => ['guest_confirmed_checkout', 'inspection_started', 'cleaning_created', 'review_requested'][$index % 4],
                'event_type' => ['system', 'guest_action', 'host_action'][$index % 3],
                'user_id' => $this->pick($checkOutRows, $index)['host_user_id'],
                'occurred_at' => now()->subHours($index % 72),
                'context_json' => [],
            ],
        );
    }

    private function seedBookingStayRecords(): void
    {
        $bookingRows = $this->bookingRows();
        $bookingIds = array_column($bookingRows, 'id');
        $stayStart = BookingStay::query()->count();

        $this->seedMissingOwnedRows(
            BookingStay::class,
            'booking_id',
            $bookingIds,
            fn (int $bookingId, int $index): BookingStay => BookingStay::factory()->create($this->bookingState($this->pick($bookingRows, $index), [
                'stay_number' => sprintf('STAY-%s-%06d', now()->format('Y'), $stayStart + $index + 1),
                'booking_id' => $bookingId,
                'status' => ['active', 'checkout_soon', 'extension_requested', 'problem_reported'][$index % 4],
                'check_in_date' => $this->pick($bookingRows, $index)['check_in_date'],
                'check_in_time' => '15:00',
                'actual_check_in_at' => CarbonImmutable::parse($this->pick($bookingRows, $index)['check_in_date'])->setTime(18, 0),
                'planned_check_out_date' => $this->pick($bookingRows, $index)['check_out_date'],
                'planned_check_out_time' => '10:00',
                'nights_count' => max(1, CarbonImmutable::parse($this->pick($bookingRows, $index)['check_in_date'])->diffInDays(CarbonImmutable::parse($this->pick($bookingRows, $index)['check_out_date']))),
                'calendar_presence_days_count' => max(2, CarbonImmutable::parse($this->pick($bookingRows, $index)['check_in_date'])->diffInDays(CarbonImmutable::parse($this->pick($bookingRows, $index)['check_out_date'])) + 1),
                'nights_passed' => min($index % 5, 3),
                'nights_remaining' => max(0, CarbonImmutable::now()->diffInDays(CarbonImmutable::parse($this->pick($bookingRows, $index)['check_out_date']), false)),
                'payment_status' => 'paid',
                'checkout_soon' => $index % 4 === 1,
                'extension_requested' => $index % 4 === 2,
                'has_open_complaint' => $index % 12 === 0,
                'has_open_maintenance' => $index % 15 === 0,
                'has_payment_problem' => false,
                'started_at' => CarbonImmutable::parse($this->pick($bookingRows, $index)['check_in_date'])->setTime(18, 0),
            ])),
        );

        $stayRows = $this->bookingStayRows();

        $this->seedFactoryRows(
            BookingStayOccupant::class,
            fn (int $index): array => [
                'booking_stay_id' => $this->pick($stayRows, $index)['id'],
                'booking_id' => $this->pick($stayRows, $index)['booking_id'],
                'user_id' => $this->pick($stayRows, $index)['guest_user_id'],
                'occupant_name' => sprintf('Demo resident %04d', $index + 1),
                'occupant_type' => 'main_guest',
                'is_main_guest' => true,
                'age_range' => ['18-24', '25-34', '35-44'][$index % 3],
                'public_gender_visible' => false,
                'city_name' => $index % 2 === 0 ? 'Demo city' : null,
                'country_name' => 'Demo country',
                'languages_json' => ['en', 'ru'],
                'stay_purpose' => ['tourist', 'student', 'work', 'long_term_resident'][$index % 4],
                'sleep_schedule' => ['wakes_up_early', 'sleeps_late', 'works_at_night', null][$index % 4],
                'smoking_status' => $index % 5 === 0 ? 'smokes' : 'does_not_smoke',
                'sociability_level' => $index % 3 === 0 ? 'social' : 'prefers_quiet',
                'public_visibility' => $index % 7 === 0 ? 'hidden' : 'roommates_only',
            ],
        );

        $this->seedMissingOwnedRows(
            StayVisibilityPreference::class,
            'booking_stay_id',
            array_column($stayRows, 'id'),
            fn (int $stayId, int $index): StayVisibilityPreference => StayVisibilityPreference::factory()->create([
                'booking_stay_id' => $stayId,
                'user_id' => $this->pick($stayRows, $index)['guest_user_id'],
                'show_public_name' => $index % 7 !== 0,
                'show_sleep_schedule' => $index % 3 === 0,
                'show_smoking_status' => $index % 4 === 0,
                'show_sociability_level' => $index % 2 === 0,
            ]),
        );

        $roomRows = $this->roomRows();
        $propertyRows = $this->propertyRows();
        $propertyById = collect($propertyRows)->keyBy('id')->all();

        $this->seedMissingOwnedRows(
            RoomCurrentOccupancySnapshot::class,
            'room_id',
            array_column($roomRows, 'id'),
            fn (int $roomId, int $index): RoomCurrentOccupancySnapshot => RoomCurrentOccupancySnapshot::factory()->create([
                'room_id' => $roomId,
                'property_id' => $this->pick($roomRows, $index)['property_id'],
                'host_user_id' => $propertyById[$this->pick($roomRows, $index)['property_id']]['host_user_id'] ?: $propertyById[$this->pick($roomRows, $index)['property_id']]['user_id'],
                'current_occupants_count' => ($index % 4) + 1,
                'current_bookings_count' => ($index % 3) + 1,
                'occupied_sleeping_places_count' => ($index % 3) + 1,
                'free_sleeping_places_count' => max(0, 4 - (($index % 3) + 1)),
                'students_count' => $index % 2,
                'workers_count' => ($index + 1) % 2,
                'tourists_count' => $index % 3 === 0 ? 1 : 0,
                'late_sleep_count' => $index % 4 === 0 ? 1 : 0,
                'non_smokers_count' => 1,
                'quiet_preferring_count' => $index % 2,
                'checkout_today_count' => $index % 9 === 0 ? 1 : 0,
                'checkin_today_count' => $index % 10 === 0 ? 1 : 0,
                'has_open_complaints' => $index % 12 === 0,
                'has_open_maintenance' => $index % 15 === 0,
                'last_recalculated_at' => now()->subMinutes($index % 60),
            ]),
        );

        $this->seedMissingOwnedRows(
            PropertyCurrentOccupancySnapshot::class,
            'property_id',
            array_column($propertyRows, 'id'),
            fn (int $propertyId, int $index): PropertyCurrentOccupancySnapshot => PropertyCurrentOccupancySnapshot::factory()->create([
                'property_id' => $propertyId,
                'host_user_id' => $this->pick($propertyRows, $index)['host_user_id'] ?: $this->pick($propertyRows, $index)['user_id'],
                'current_occupants_count' => ($index % 8) + 1,
                'current_bookings_count' => ($index % 6) + 1,
                'occupied_rooms_count' => ($index % 3) + 1,
                'occupied_sleeping_places_count' => ($index % 8) + 1,
                'free_sleeping_places_count' => max(0, 12 - (($index % 8) + 1)),
                'checkout_today_count' => $index % 9 === 0 ? 1 : 0,
                'checkin_today_count' => $index % 10 === 0 ? 1 : 0,
                'has_open_complaints' => $index % 12 === 0,
                'has_open_maintenance' => $index % 15 === 0,
                'last_recalculated_at' => now()->subMinutes($index % 60),
            ]),
        );

        $this->seedFactoryRows(
            BookingStayStatusLog::class,
            fn (int $index): array => [
                'booking_stay_id' => $this->pick($stayRows, $index)['id'],
                'booking_id' => $this->pick($stayRows, $index)['booking_id'],
                'user_id' => $this->pick($stayRows, $index)['host_user_id'],
                'old_status' => $index % 2 === 0 ? 'pending_check_in_confirmation' : null,
                'new_status' => $this->pick($stayRows, $index)['status'],
                'reason_key' => 'stays.events.bulk_seeded',
                'context_json' => [],
            ],
        );

        $this->seedFactoryRows(
            BookingStayNote::class,
            fn (int $index): array => [
                'booking_stay_id' => $this->pick($stayRows, $index)['id'],
                'booking_id' => $this->pick($stayRows, $index)['booking_id'],
                'user_id' => $this->pick($stayRows, $index)['host_user_id'],
                'note_type' => ['host_note', 'guest_note', 'neighbor_note', 'problem_note'][$index % 4],
                'visibility' => ['host_only', 'guest_and_host', 'host_only', 'internal'][$index % 4],
                'note' => sprintf('Demo stay note %04d', $index + 1),
            ],
        );

        $this->seedFactoryRows(
            BookingStayEvent::class,
            fn (int $index): array => [
                'booking_stay_id' => $this->pick($stayRows, $index)['id'],
                'booking_id' => $this->pick($stayRows, $index)['booking_id'],
                'event_key' => ['stay_started', 'guest_present', 'checkout_soon', 'maintenance_reported'][$index % 4],
                'event_type' => ['system', 'guest_action', 'host_action'][$index % 3],
                'source_type' => 'bulk_demo_seed',
                'source_id' => $index + 1,
                'user_id' => $this->pick($stayRows, $index)['guest_user_id'],
                'occurred_at' => now()->subHours($index % 72),
                'context_json' => [],
            ],
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

        for ($attempt = 0; $attempt < 2; $attempt++) {
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
     * @return list<array{id:int,booking_id:int,guest_user_id:int,host_user_id:int,property_id:int,room_id:int,sleeping_place_id:int,payment_number:string,status:string,amount:string,required_now_amount:string,currency:string}>
     */
    private function bookingPaymentRows(): array
    {
        return BookingPayment::query()
            ->select([
                'id',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'payment_number',
                'status',
                'amount',
                'required_now_amount',
                'currency',
            ])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingPayment $payment): array => [
                'id' => $payment->id,
                'booking_id' => $payment->booking_id,
                'guest_user_id' => $payment->guest_user_id,
                'host_user_id' => $payment->host_user_id,
                'property_id' => $payment->property_id,
                'room_id' => $payment->room_id,
                'sleeping_place_id' => $payment->sleeping_place_id,
                'payment_number' => $payment->payment_number,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'required_now_amount' => $payment->required_now_amount,
                'currency' => $payment->currency,
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
     * @return list<array{id:int,booking_id:int,guest_user_id:int,host_user_id:int,property_id:int,room_id:int,sleeping_place_id:int,status:string}>
     */
    private function bookingStayRows(): array
    {
        return BookingStay::query()
            ->select(['id', 'booking_id', 'guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'status'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingStay $stay): array => [
                'id' => $stay->id,
                'booking_id' => $stay->booking_id,
                'guest_user_id' => $stay->guest_user_id,
                'host_user_id' => $stay->host_user_id,
                'property_id' => $stay->property_id,
                'room_id' => $stay->room_id,
                'sleeping_place_id' => $stay->sleeping_place_id,
                'status' => $stay->status,
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,booking_id:int,guest_user_id:int,host_user_id:int,property_id:int,room_id:int,sleeping_place_id:int,status:string,current_check_out_date:string,new_check_out_date:string,additional_nights_count:int,currency:string}>
     */
    private function bookingExtensionRows(): array
    {
        return BookingExtension::query()
            ->select([
                'id',
                'booking_id',
                'guest_user_id',
                'host_user_id',
                'property_id',
                'room_id',
                'sleeping_place_id',
                'status',
                'current_check_out_date',
                'new_check_out_date',
                'additional_nights_count',
                'currency',
            ])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingExtension $extension): array => [
                'id' => $extension->id,
                'booking_id' => $extension->booking_id,
                'guest_user_id' => $extension->guest_user_id,
                'host_user_id' => $extension->host_user_id,
                'property_id' => $extension->property_id,
                'room_id' => $extension->room_id,
                'sleeping_place_id' => $extension->sleeping_place_id,
                'status' => $extension->status instanceof \BackedEnum ? $extension->status->value : (string) $extension->status,
                'current_check_out_date' => $extension->current_check_out_date?->toDateString() ?: now()->toDateString(),
                'new_check_out_date' => $extension->new_check_out_date?->toDateString() ?: now()->addDay()->toDateString(),
                'additional_nights_count' => max(1, (int) $extension->additional_nights_count),
                'currency' => $extension->currency ?: 'EUR',
            ])
            ->all();
    }

    /**
     * @return list<array{id:int,original_booking_id:int,new_booking_id:int|null,guest_user_id:int,host_user_id:int,current_property_id:int,current_room_id:int,current_sleeping_place_id:int,new_property_id:int,new_room_id:int,new_sleeping_place_id:int,relocation_date:string,status:string,old_remaining_value_amount:string,new_remaining_value_amount:string,price_difference_amount:string,additional_payment_amount:string,refund_amount:string,additional_deposit_amount:string,currency:string}>
     */
    private function bookingRelocationRows(): array
    {
        return BookingRelocation::query()
            ->select([
                'id',
                'original_booking_id',
                'new_booking_id',
                'guest_user_id',
                'host_user_id',
                'current_property_id',
                'current_room_id',
                'current_sleeping_place_id',
                'new_property_id',
                'new_room_id',
                'new_sleeping_place_id',
                'relocation_date',
                'status',
                'old_remaining_value_amount',
                'new_remaining_value_amount',
                'price_difference_amount',
                'additional_payment_amount',
                'refund_amount',
                'additional_deposit_amount',
                'currency',
            ])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (BookingRelocation $relocation): array => [
                'id' => $relocation->id,
                'original_booking_id' => $relocation->original_booking_id,
                'new_booking_id' => $relocation->new_booking_id,
                'guest_user_id' => $relocation->guest_user_id,
                'host_user_id' => $relocation->host_user_id,
                'current_property_id' => $relocation->current_property_id,
                'current_room_id' => $relocation->current_room_id,
                'current_sleeping_place_id' => $relocation->current_sleeping_place_id,
                'new_property_id' => $relocation->new_property_id ?: $relocation->current_property_id,
                'new_room_id' => $relocation->new_room_id ?: $relocation->current_room_id,
                'new_sleeping_place_id' => $relocation->new_sleeping_place_id ?: $relocation->current_sleeping_place_id,
                'relocation_date' => $relocation->relocation_date?->toDateString() ?: now()->toDateString(),
                'status' => (string) $relocation->status,
                'old_remaining_value_amount' => (string) $relocation->old_remaining_value_amount,
                'new_remaining_value_amount' => (string) $relocation->new_remaining_value_amount,
                'price_difference_amount' => (string) $relocation->price_difference_amount,
                'additional_payment_amount' => (string) $relocation->additional_payment_amount,
                'refund_amount' => (string) $relocation->refund_amount,
                'additional_deposit_amount' => (string) $relocation->additional_deposit_amount,
                'currency' => $relocation->currency ?: 'EUR',
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
