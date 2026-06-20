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
use App\Models\BookingExtension;
use App\Models\BookingGuest;
use App\Models\BookingPriceLine;
use App\Models\BookingStatusHistory;
use App\Models\CheckinRecord;
use App\Models\CheckoutRecord;
use App\Models\City;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\DepositRecord;
use App\Models\DiscountRule;
use App\Models\Favorite;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentRecord;
use App\Models\Payout;
use App\Models\PriceRule;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\RefundRequest;
use App\Models\Region;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\RuleTranslation;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Models\WaitlistEntry;
use App\Models\WaitlistItem;
use App\Services\Media\DemoMediaFileService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BulkMarketplaceSeeder extends Seeder
{
    private const TARGET_COUNT = 1000;

    private const LOCALES = ['en', 'ru'];

    public function run(): void
    {
        $this->seedGeo();
        $this->seedCatalog();
        $this->seedUsers();
        $this->seedUserProfilesAndSettings();
        $this->seedListingHierarchy();
        $this->seedListingTranslations();
        $this->seedAvailabilityAndPricing();
        $this->seedBookings();
        $this->seedBookingChildren();
        $this->seedLegacyAvailabilityAndWaitlists();
        $this->seedMessaging();
        $this->seedSocialRecords();
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

    private function seedListingTranslations(): void
    {
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
    }

    private function seedBookings(): void
    {
        $userIds = $this->ids(User::class);
        $sleepingPlaceRows = $this->sleepingPlaceRows();
        $bedIds = $this->ids(Bed::class);

        Booking::factory()
            ->count($this->missingFor(Booking::class))
            ->sequence(function (Sequence $sequence) use ($userIds, $sleepingPlaceRows, $bedIds): array {
                $sleepingPlace = $this->pick($sleepingPlaceRows, $sequence->index);
                $guestId = $this->pick($userIds, $sequence->index);
                $hostId = $this->pick($userIds, $sequence->index + 1);

                return [
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

        MediaItem::factory()
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
                    'caption_en' => sprintf('Bulk demo property photo %04d', $sequence->index + 1),
                    'caption_ru' => sprintf('Bulk demo property photo RU %04d', $sequence->index + 1),
                    'is_primary' => $sequence->index % 5 === 0,
                    'is_cover' => $sequence->index % 5 === 0,
                ];
            })
            ->create();

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
                'caption_en',
                'caption_ru',
            ])
            ->where('path', 'like', 'bulk-demo/%')
            ->chunkById(200, function ($mediaItems) use ($files): void {
                $files->ensureForMediaItems($mediaItems);
            });
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
                'name_normalized' => Str::lower($this->localizedSeedText('Bulk amenity', 'en', $index)),
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
                'name_normalized' => Str::lower($this->localizedSeedText('Bulk rule', 'en', $index)),
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
            ->get()
            ->mapWithKeys(fn (Model $translation): array => [
                $translation->getAttribute($ownerColumn).':'.$translation->getAttribute('locale') => true,
            ])
            ->all();

        foreach ($ownerIds as $index => $ownerId) {
            foreach (self::LOCALES as $locale) {
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
     * @return list<array{id:int,property_id:int,room_id:int}>
     */
    private function bookingRows(): array
    {
        return Booking::query()
            ->select(['id', 'property_id', 'room_id'])
            ->orderBy('id')
            ->limit(self::TARGET_COUNT)
            ->get()
            ->map(fn (Booking $booking): array => [
                'id' => $booking->id,
                'property_id' => $booking->property_id,
                'room_id' => $booking->room_id,
            ])
            ->all();
    }

    private function localizedSeedText(string $base, string $locale, int $index): string
    {
        $prefix = $locale === 'ru' ? 'RU ' : '';

        return sprintf('%s%s %04d', $prefix, $base, $index + 1);
    }
}
