<?php

namespace Tests\Unit;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingPriceLine;
use App\Models\BookingStatusHistory;
use App\Models\City;
use App\Models\Complaint;
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
use App\Models\PriceRule;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\RefundRequest;
use App\Models\Region;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Models\WaitlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreMarketplaceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_marketplace_schema_contains_required_tables_columns_and_indexes(): void
    {
        $requiredTables = [
            'users',
            'user_profiles',
            'guest_preferences',
            'host_profiles',
            'user_settings',
            'countries',
            'regions',
            'cities',
            'properties',
            'property_translations',
            'rooms',
            'room_translations',
            'sleeping_places',
            'sleeping_place_translations',
            'amenities',
            'amenity_translations',
            'rules',
            'rule_translations',
            'property_amenity',
            'room_amenity',
            'sleeping_place_amenity',
            'property_rule',
            'room_rule',
            'sleeping_place_rule',
            'media_items',
            'availability_days',
            'price_rules',
            'discount_rules',
            'bookings',
            'booking_guests',
            'booking_price_lines',
            'booking_status_histories',
            'payment_records',
            'deposit_records',
            'refund_requests',
            'favorites',
            'saved_searches',
            'waitlist_items',
            'message_threads',
            'messages',
            'reviews',
            'complaints',
            'notifications',
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table [{$table}].");
        }

        $requiredColumns = [
            'countries' => ['iso2', 'iso3', 'name_en', 'name_ru', 'name_native', 'phone_code', 'currency_code', 'timezone_default', 'status', 'name_normalized'],
            'cities' => ['geoname_id', 'country_id', 'region_id', 'name', 'ascii_name', 'alternate_names', 'latitude', 'longitude', 'population', 'timezone', 'feature_class', 'feature_code', 'name_normalized', 'status'],
            'user_profiles' => ['user_id', 'display_name', 'avatar_path', 'date_of_birth', 'gender', 'country_id', 'city_id', 'phone', 'phone_verified_at', 'email_verified_at', 'about', 'languages_json', 'occupation', 'travel_purpose', 'smokes', 'has_pets', 'allergies', 'prefers_quiet', 'sleep_schedule', 'social_level', 'identity_verified_at', 'rating_average', 'reviews_count', 'complaints_count', 'status'],
            'guest_preferences' => ['preferred_budget_min', 'preferred_budget_max', 'preferred_currency', 'preferred_city_id', 'preferred_room_type', 'preferred_sleeping_place_type', 'wants_wifi', 'wants_kitchen', 'wants_washing_machine', 'wants_locker', 'wants_lower_bunk', 'avoids_mixed_room', 'avoids_smoking', 'avoids_pets', 'needs_late_check_in', 'needs_early_check_out', 'needs_workspace', 'needs_quiet_hours', 'needs_accessibility', 'max_people_in_room', 'max_walking_distance_to_transport_meters', 'sleep_schedule', 'social_level', 'allergies', 'baggage_size', 'accessibility_needs_json'],
            'host_profiles' => ['user_id', 'display_name', 'avatar_path', 'about', 'languages_json', 'response_time_minutes', 'response_rate', 'response_style', 'lives_in_property', 'lives_nearby', 'can_help_with_check_in', 'emergency_contact_available', 'hosting_experience', 'default_check_in_time', 'default_check_out_time', 'default_cancellation_policy', 'default_deposit_setting', 'default_house_rules', 'rating_average', 'reviews_count', 'cancellations_count', 'verified_at', 'status'],
            'properties' => ['host_user_id', 'rental_unit_type', 'country_id', 'region_id', 'region_name', 'city_id', 'type', 'status', 'address_line_1', 'address_line_2', 'house_number', 'apartment_number', 'floor', 'total_floors', 'has_elevator', 'latitude', 'longitude', 'approximate_latitude', 'approximate_longitude', 'show_exact_address_before_booking', 'show_exact_address_after_payment', 'nearest_transport', 'distance_to_transport_meters', 'distance_to_center_meters', 'total_area', 'rooms_count', 'bathrooms_count', 'showers_count', 'kitchens_count', 'balconies_count', 'max_guests', 'current_guests_count', 'noise_level', 'cleanliness_level', 'safety_level', 'repair_state', 'has_heating', 'has_air_conditioning', 'has_hot_water', 'has_parking', 'has_security', 'has_cctv_common_areas', 'emergency_contact_name', 'emergency_contact_phone'],
            'property_translations' => ['property_id', 'locale', 'title', 'summary', 'description', 'neighborhood_description', 'getting_there', 'what_guests_like', 'what_to_know', 'suitable_for', 'not_suitable_for', 'check_in_instructions', 'check_out_instructions', 'house_rules_text', 'safety_notes'],
            'sleeping_place_translations' => ['sleeping_place_id', 'locale', 'title', 'summary', 'description', 'special_conditions', 'privacy_notes', 'accessibility_notes'],
            'rooms' => ['property_id', 'type', 'is_private', 'is_pass_through', 'status', 'room_number', 'floor', 'area', 'beds_count', 'max_guests', 'occupied_places_count', 'available_places_count', 'gender_policy', 'min_guest_age', 'max_guest_age', 'has_window', 'windows_count', 'window_view', 'has_lock', 'has_wardrobe', 'has_desk', 'has_chair', 'has_mirror', 'has_heating', 'has_air_conditioning', 'has_balcony', 'has_curtains', 'has_blackout_curtains', 'noise_level', 'light_level', 'ventilation_level', 'can_eat', 'can_work_at_night', 'can_turn_light_at_night', 'can_talk_at_night', 'room_rules_text'],
            'sleeping_places' => ['room_id', 'property_id', 'type', 'status', 'place_number', 'display_name', 'bunk_level', 'length_cm', 'width_cm', 'mattress_type', 'mattress_firmness', 'has_pillow', 'has_blanket', 'has_bedding', 'has_towel', 'has_curtain', 'has_lamp', 'has_power_socket', 'has_usb', 'has_shelf', 'has_hook', 'has_locker', 'locker_has_lock', 'has_luggage_space', 'near_window', 'near_door', 'near_radiator', 'near_air_conditioner', 'privacy_level', 'noise_level', 'is_accessible', 'suitable_for_tall_person', 'suitable_for_elderly', 'suitable_for_limited_mobility', 'max_guests', 'min_guest_age', 'max_guest_age', 'base_price_per_night', 'weekly_price', 'monthly_price', 'weekend_price', 'holiday_price', 'cleaning_fee', 'deposit_amount', 'currency', 'min_nights', 'max_nights', 'instant_booking_enabled', 'requires_host_approval'],
            'media_items' => ['owner_type', 'owner_id', 'mediable_type', 'mediable_id', 'owner_user_id', 'collection', 'disk', 'path', 'thumbnail_path', 'thumb_path', 'mobile_path', 'full_path', 'original_filename', 'mime', 'mime_type', 'size', 'size_bytes', 'width', 'height', 'alt_text', 'sort_order', 'is_primary', 'is_cover', 'status'],
            'media_item_translations' => ['media_item_id', 'locale', 'caption'],
            'availability_days' => ['sleeping_place_id', 'booking_id', 'date', 'status', 'price_override', 'min_nights_override', 'max_nights_override', 'check_in_allowed', 'check_out_allowed', 'note'],
            'bookings' => ['guest_user_id', 'host_user_id', 'property_id', 'room_id', 'sleeping_place_id', 'status', 'payment_status', 'check_in_date', 'check_out_date', 'check_in_time', 'check_out_time', 'nights_count', 'calendar_days_count', 'guests_count', 'currency', 'subtotal_amount', 'discount_amount', 'cleaning_fee_amount', 'service_fee_amount', 'deposit_amount', 'total_amount', 'refundable_amount', 'non_refundable_amount', 'guest_message', 'host_response', 'cancellation_policy', 'cancelled_by', 'cancelled_at', 'cancellation_reason', 'checked_in_at', 'checked_out_at'],
            'message_threads' => ['type', 'guest_user_id', 'host_user_id', 'booking_id', 'property_id', 'sleeping_place_id', 'last_message_at', 'status'],
            'messages' => ['thread_id', 'sender_user_id', 'recipient_user_id', 'booking_id', 'property_id', 'sleeping_place_id', 'body', 'attachments', 'read_at', 'important', 'system_message', 'locale'],
        ];

        foreach ($requiredColumns as $table => $columns) {
            $this->assertTableHasColumns($table, $columns);
        }

        $requiredIndexes = [
            'properties' => [
                ['city_id', 'status'],
                ['country_id', 'city_id'],
                ['rental_unit_type', 'status'],
                ['distance_to_transport_meters'],
            ],
            'guest_preferences' => [
                ['preferred_city_id', 'preferred_currency'],
            ],
            'rooms' => [
                ['property_id', 'status'],
            ],
            'sleeping_places' => [
                ['room_id', 'status'],
            ],
            'availability_days' => [
                ['sleeping_place_id', 'date'],
                ['booking_id', 'status'],
            ],
            'bookings' => [
                ['sleeping_place_id', 'check_in_date', 'check_out_date'],
                ['status', 'check_in_date'],
            ],
            'user_settings' => [
                ['user_id', 'locale'],
            ],
            'property_translations' => [
                ['property_id', 'locale'],
            ],
            'room_translations' => [
                ['room_id', 'locale'],
            ],
            'sleeping_place_translations' => [
                ['sleeping_place_id', 'locale'],
            ],
            'countries' => [
                ['iso2'],
                ['name_normalized'],
                ['status', 'name_normalized'],
            ],
            'cities' => [
                ['geoname_id'],
                ['name_normalized'],
                ['status', 'name_normalized'],
                ['country_id', 'status'],
            ],
            'amenities' => [
                ['name_normalized'],
            ],
            'rules' => [
                ['name_normalized'],
            ],
            'media_items' => [
                ['owner_type', 'owner_id', 'collection', 'sort_order'],
                ['is_primary'],
            ],
            'message_threads' => [
                ['guest_user_id', 'status', 'last_message_at'],
                ['host_user_id', 'status', 'last_message_at'],
                ['type', 'last_message_at'],
            ],
            'messages' => [
                ['thread_id', 'created_at'],
                ['sender_user_id', 'created_at'],
                ['recipient_user_id', 'read_at', 'created_at'],
                ['booking_id'],
                ['property_id'],
                ['sleeping_place_id'],
            ],
        ];

        foreach ($requiredIndexes as $table => $indexes) {
            foreach ($indexes as $columns) {
                $this->assertTableHasLeadingIndex($table, $columns);
            }
        }

        $this->assertForeignKeysHaveLeadingIndexes();
    }

    public function test_property_room_and_sleeping_place_graph_can_be_created_with_translations(): void
    {
        $country = Country::factory()->create();
        $region = Region::factory()->for($country)->create();
        $city = City::factory()->for($country)->for($region)->create();
        $host = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(HostProfile::factory(), 'hostProfile')
            ->has(UserSetting::factory(), 'setting')
            ->create();

        $property = Property::factory()
            ->for($host, 'host')
            ->for($country)
            ->for($region)
            ->for($city)
            ->has(PropertyTranslation::factory()->state(['locale' => 'en']), 'translations')
            ->create(['status' => PropertyStatus::Active]);

        $room = Room::factory()
            ->for($property)
            ->has(RoomTranslation::factory()->state(['locale' => 'en']), 'translations')
            ->create();

        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->has(SleepingPlaceTranslation::factory()->state(['locale' => 'en']), 'translations')
            ->create(['status' => SleepingPlaceStatus::Active]);

        $amenity = Amenity::factory()
            ->hasTranslations(1, ['locale' => 'en'])
            ->create();
        $rule = Rule::factory()
            ->hasTranslations(1, ['locale' => 'en'])
            ->create();

        $property->amenities()->attach($amenity);
        $room->amenities()->attach($amenity);
        $sleepingPlace->amenities()->attach($amenity);
        $property->rules()->attach($rule);
        $room->rules()->attach($rule);
        $sleepingPlace->rules()->attach($rule);

        MediaItem::factory()->for($property, 'mediable')->create();
        AvailabilityDay::factory()->for($sleepingPlace)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::Available,
        ]);
        PriceRule::factory()->for($sleepingPlace)->create();
        DiscountRule::factory()->for($sleepingPlace)->create();

        $this->assertTrue(Property::active()->inCity($city->id)->translated('en')->whereKey($property)->exists());
        $this->assertTrue($property->rooms()->whereKey($room)->exists());
        $this->assertTrue($property->sleepingPlaces()->whereKey($sleepingPlace)->exists());
        $this->assertTrue($sleepingPlace->amenities()->whereKey($amenity)->exists());
        $this->assertTrue($sleepingPlace->rules()->whereKey($rule)->exists());
        $this->assertTrue($sleepingPlace->availabilityDays()->whereDate('date', '2026-07-10')->exists());
    }

    public function test_booking_money_and_social_records_can_be_created_from_factories(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createBookableSleepingPlace();

        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace)
            ->create([
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
            ]);

        BookingGuest::factory()->for($booking)->create();
        BookingPriceLine::factory()->for($booking)->create();
        BookingStatusHistory::factory()->for($booking)->for($guest, 'changedBy')->create();
        PaymentRecord::factory()->for($booking)->for($guest, 'payer')->create();
        DepositRecord::factory()->for($booking)->create();
        RefundRequest::factory()->for($booking)->for($guest, 'requestedBy')->create();
        Favorite::factory()->for($guest)->for($sleepingPlace)->create();
        $city = City::findOrFail($property->city_id);
        SavedSearch::factory()->for($guest)->for($city)->create();
        WaitlistItem::factory()->for($guest)->for($sleepingPlace)->create();

        $thread = MessageThread::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($booking)
            ->for($sleepingPlace)
            ->create();
        Message::factory()->for($thread, 'thread')->for($guest, 'sender')->create();

        Review::factory()->for($booking)->for($guest, 'reviewer')->for($host, 'reviewee')->for($property)->for($room)->for($sleepingPlace)->create();
        Complaint::factory()->for($guest, 'reporter')->for($host, 'reportedUser')->for($booking)->for($property)->for($room)->for($sleepingPlace)->create();
        Notification::factory()->for($guest, 'user')->create();

        $this->assertTrue(Booking::forGuest($guest->id)->whereKey($booking)->exists());
        $this->assertTrue(Booking::forHost($host->id)->whereKey($booking)->exists());
        $this->assertTrue(Property::forGuest($guest->id)->whereKey($property)->exists());
        $this->assertTrue(Room::forGuest($guest->id)->whereKey($room)->exists());
        $this->assertTrue(SleepingPlace::forGuest($guest->id)->whereKey($sleepingPlace)->exists());
        $this->assertTrue($booking->priceLines()->exists());
        $this->assertTrue($booking->statusHistories()->exists());
        $this->assertTrue($thread->messages()->exists());
        $this->assertSame($city->id, $property->city_id);
    }

    public function test_available_between_scope_excludes_overlapping_bookings_and_blocked_days(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createBookableSleepingPlace();

        $this->assertTrue(
            SleepingPlace::availableBetween('2026-07-10', '2026-07-15')
                ->whereKey($sleepingPlace)
                ->exists()
        );
        $this->assertTrue(Property::availableBetween('2026-07-10', '2026-07-15')->whereKey($property)->exists());
        $this->assertTrue(Room::availableBetween('2026-07-10', '2026-07-15')->whereKey($room)->exists());
        $this->assertTrue(Room::inCity($property->city_id)->whereKey($room)->exists());
        $this->assertTrue(Room::forHost($host->id)->whereKey($room)->exists());

        Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace)
            ->create([
                'check_in_date' => '2026-07-12',
                'check_out_date' => '2026-07-14',
                'status' => BookingStatus::Confirmed,
            ]);

        $this->assertFalse(
            SleepingPlace::availableBetween('2026-07-10', '2026-07-15')
                ->whereKey($sleepingPlace)
                ->exists()
        );
    }

    /**
     * @param  list<string>  $columns
     */
    private function assertTableHasColumns(string $table, array $columns): void
    {
        $actual = Schema::getColumnListing($table);
        $missing = array_values(array_diff($columns, $actual));

        $this->assertSame([], $missing, "Table [{$table}] is missing columns.");
    }

    /**
     * @param  list<string>  $columns
     */
    private function assertTableHasLeadingIndex(string $table, array $columns): void
    {
        $hasIndex = collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => array_slice($index['columns'], 0, count($columns)) === $columns);

        $this->assertTrue($hasIndex, sprintf(
            'Table [%s] is missing leading index [%s].',
            $table,
            implode(', ', $columns)
        ));
    }

    private function assertForeignKeysHaveLeadingIndexes(): void
    {
        foreach (Schema::getTables() as $table) {
            $tableName = $table['name'];

            foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
                foreach ($foreignKey['columns'] as $column) {
                    $this->assertTableHasLeadingIndex($tableName, [$column]);
                }
            }
        }
    }

    /**
     * @return array{User, User, Property, Room, SleepingPlace}
     */
    private function createBookableSleepingPlace(): array
    {
        $country = Country::factory()->create();
        $region = Region::factory()->for($country)->create();
        $city = City::factory()->for($country)->for($region)->create();
        $guest = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(GuestPreference::factory(), 'guestPreference')
            ->create();
        $host = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(HostProfile::factory(), 'hostProfile')
            ->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->for($country)
            ->for($region)
            ->for($city)
            ->create(['status' => PropertyStatus::Active]);
        $room = Room::factory()->for($property)->create();
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(['status' => SleepingPlaceStatus::Active]);

        return [$guest, $host, $property, $room, $sleepingPlace];
    }
}
