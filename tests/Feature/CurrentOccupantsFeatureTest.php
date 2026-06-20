<?php

namespace Tests\Feature;

use App\Data\Occupants\DateRange;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\Rooms\RoomOccupantsPreview;
use App\Livewire\Listings\Detail\CurrentOccupantsSection;
use App\Livewire\Profile\CoLivingPrivacySettings;
use App\Livewire\Profile\CoLivingProfileForm;
use App\Models\Booking;
use App\Models\CoLivingProfile;
use App\Models\CoLivingVisibilitySetting;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomOccupantSnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Occupants\CoLivingPrivacyService;
use App\Services\Occupants\RoommateCompatibilityService;
use App\Services\Occupants\RoomOccupantSnapshotService;
use App\Services\Occupants\RoomOccupantSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class CurrentOccupantsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_co_living_tables_relationships_indexes_and_cascade_delete_exist(): void
    {
        $this->assertTrue(Schema::hasTable('co_living_profiles'));
        $this->assertTrue(Schema::hasTable('co_living_visibility_settings'));
        $this->assertTrue(Schema::hasTable('room_occupant_snapshots'));
        $this->assertTrue(Schema::hasColumn('co_living_profiles', 'public_alias'));
        $this->assertTrue(Schema::hasColumn('co_living_visibility_settings', 'allow_profile_in_prebooking_summary'));
        $this->assertTrue(Schema::hasColumn('room_occupant_snapshots', 'languages_json_snapshot'));
        $this->assertTrue(Schema::hasIndex('co_living_profiles', ['user_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('co_living_profiles', ['prefers_quiet']));
        $this->assertTrue(Schema::hasIndex('co_living_visibility_settings', ['user_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('room_occupant_snapshots', ['room_id', 'check_in_date', 'check_out_date']));

        $booking = $this->occupantBooking();
        $profile = CoLivingProfile::factory()->for($booking->guest, 'user')->create();
        $settings = CoLivingVisibilitySetting::factory()->for($booking->guest, 'user')->create();
        $snapshot = RoomOccupantSnapshot::factory()
            ->for($booking->room)
            ->for($booking->sleepingPlace)
            ->for($booking)
            ->for($booking->guest, 'user')
            ->create();

        $this->assertSame($booking->guest->id, $profile->user->id);
        $this->assertSame($booking->guest->id, $settings->user->id);
        $this->assertSame($booking->id, $snapshot->booking->id);
        $this->assertSame($booking->room->id, $snapshot->room->id);

        $booking->guest->delete();

        $this->assertDatabaseMissing('co_living_profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('co_living_visibility_settings', ['id' => $settings->id]);
        $this->assertDatabaseMissing('room_occupant_snapshots', ['id' => $snapshot->id]);
    }

    public function test_snapshot_from_booking_and_prebooking_summary_are_privacy_safe(): void
    {
        $booking = $this->occupantBooking([
            'check_in_date' => '2026-07-08',
            'check_out_date' => '2026-07-12',
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ], [
            'name' => 'Full Private Name',
            'email' => 'private@example.test',
            'phone' => '+37060000001',
        ]);
        $this->profileFor($booking->guest, [
            'public_alias' => 'Alex',
            'age_range' => '25-34',
            'languages_json' => ['en', 'ru'],
            'guest_type' => 'long_term_guest',
            'tourist' => false,
            'working' => true,
            'remote_worker' => true,
            'long_term_guest' => true,
            'sleep_schedule' => 'early_bird',
            'home_presence_level' => 'often_home',
            'smokes' => false,
            'social_level' => 'calm',
            'prefers_quiet' => true,
            'roommate_rating_average' => 4.8,
            'roommate_reviews_count' => 6,
        ]);

        $snapshot = app(RoomOccupantSnapshotService::class)->createFromBooking($booking);
        $summary = app(RoomOccupantSummaryService::class)->getPreBookingSummary(
            $booking->room,
            new DateRange('2026-07-10', '2026-07-15'),
        );

        $encoded = json_encode($summary->toArray(), JSON_THROW_ON_ERROR);

        $this->assertSame($booking->id, $snapshot->booking_id);
        $this->assertSame(1, $summary->occupantsCount);
        $this->assertStringContainsString(__('occupants.long_term_guest'), $encoded);
        $this->assertStringContainsString(__('occupants.remote_worker'), $encoded);
        $this->assertStringContainsString(__('occupants.quiet'), $encoded);
        $this->assertStringNotContainsString('Full Private Name', $encoded);
        $this->assertStringNotContainsString('private@example.test', $encoded);
        $this->assertStringNotContainsString('+37060000001', $encoded);
    }

    public function test_date_overlap_rules_ignore_checkout_boundary_and_cancelled_bookings(): void
    {
        $booking = $this->occupantBooking([
            'check_in_date' => '2026-07-08',
            'check_out_date' => '2026-07-12',
            'status' => BookingStatus::Confirmed,
        ]);
        $this->profileFor($booking->guest);
        app(RoomOccupantSnapshotService::class)->createFromBooking($booking);

        $boundary = $this->occupantBooking([
            'room_id' => $booking->room_id,
            'property_id' => $booking->property_id,
            'check_in_date' => '2026-07-15',
            'check_out_date' => '2026-07-20',
            'status' => BookingStatus::Confirmed,
        ]);
        $this->profileFor($boundary->guest);
        app(RoomOccupantSnapshotService::class)->createFromBooking($boundary);

        $cancelled = $this->occupantBooking([
            'room_id' => $booking->room_id,
            'property_id' => $booking->property_id,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-14',
            'status' => BookingStatus::CancelledByGuest,
        ]);
        $this->profileFor($cancelled->guest);
        app(RoomOccupantSnapshotService::class)->createFromBooking($cancelled);

        $service = app(RoomOccupantSummaryService::class);

        $this->assertSame(1, $service->countOccupantsForDates($booking->room, new DateRange('2026-07-10', '2026-07-15')));
        $this->assertSame(0, $service->countOccupantsForDates($booking->room, new DateRange('2026-07-12', '2026-07-15')));
    }

    public function test_confirmed_booking_summary_shows_only_allowed_roommate_fields(): void
    {
        $occupantBooking = $this->occupantBooking();
        $this->profileFor($occupantBooking->guest, [
            'public_alias' => 'Alex',
            'age_range' => '25-34',
            'languages_json' => ['en', 'ru'],
            'stay_purpose' => 'work',
            'guest_type' => 'remote_worker',
            'remote_worker' => true,
            'prefers_quiet' => true,
            'roommate_rating_average' => 4.8,
            'roommate_reviews_count' => 6,
        ]);
        app(RoomOccupantSnapshotService::class)->createFromBooking($occupantBooking);

        $confirmedGuest = User::factory()->create();
        $viewerBooking = $this->bookingForRoom($occupantBooking->room, $confirmedGuest, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $summary = app(RoomOccupantSummaryService::class)->getConfirmedBookingSummary(
            $occupantBooking->room,
            new DateRange('2026-07-10', '2026-07-15'),
            $confirmedGuest,
            $viewerBooking,
        );
        $encoded = json_encode($summary->toArray(), JSON_THROW_ON_ERROR);

        $this->assertSame(1, $summary->occupantsCount);
        $this->assertStringContainsString('Alex', $encoded);
        $this->assertStringContainsString('25-34', $encoded);
        $this->assertStringContainsString('EN', $encoded);
        $this->assertStringContainsString(__('occupants.checkout_date', ['date' => 'Jul 15, 2026']), $encoded);
        $this->assertStringNotContainsString($occupantBooking->guest->email, $encoded);
        $this->assertStringNotContainsString((string) $occupantBooking->guest->phone, $encoded);
    }

    public function test_profile_and_privacy_livewire_forms_save_and_render_localized_copy(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CoLivingProfileForm::class)
            ->set('publicAlias', 'Quiet Alex')
            ->set('ageRange', '25-34')
            ->set('languages', 'en, ru')
            ->set('guestType', 'remote_worker')
            ->set('remoteWorker', true)
            ->set('prefersQuiet', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('occupants.messages.profile_saved'));

        Livewire::actingAs($user)
            ->test(CoLivingPrivacySettings::class)
            ->set('showRealFirstName', false)
            ->set('showAvatar', false)
            ->set('allowProfileInPrebookingSummary', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('occupants.messages.privacy_saved'));

        $this->assertDatabaseHas('co_living_profiles', [
            'user_id' => $user->id,
            'public_alias' => 'Quiet Alex',
            'remote_worker' => true,
            'prefers_quiet' => true,
        ]);
        $this->assertDatabaseHas('co_living_visibility_settings', [
            'user_id' => $user->id,
            'allow_profile_in_prebooking_summary' => false,
        ]);
    }

    public function test_listing_and_dedicated_sections_render_privacy_safe_current_occupants(): void
    {
        $booking = $this->occupantBooking([
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
        ], ['name' => 'Do Not Render']);
        $this->profileFor($booking->guest, [
            'public_alias' => 'Hidden Alias',
            'guest_type' => 'tourist',
            'tourist' => true,
            'prefers_quiet' => true,
            'smokes' => false,
        ]);
        app(RoomOccupantSnapshotService::class)->createFromBooking($booking);

        $target = SleepingPlace::factory()
            ->for($booking->room)
            ->for($booking->property)
            ->create(['status' => SleepingPlaceStatus::Active]);

        $this->get(route('places.show', [
            'locale' => 'en',
            'sleepingPlace' => $target,
            'in' => '2026-07-11',
            'out' => '2026-07-13',
        ]))
            ->assertOk()
            ->assertSee(__('occupants.title'))
            ->assertSee(__('occupants.privacy_note'))
            ->assertSee(__('occupants.tourist'))
            ->assertDontSee('Do Not Render')
            ->assertDontSee('Hidden Alias');

        Livewire::test(CurrentOccupantsSection::class, [
            'roomId' => $booking->room_id,
            'checkIn' => '2026-07-11',
            'checkOut' => '2026-07-13',
        ])
            ->assertSee(__('occupants.title'))
            ->assertSee(__('occupants.tourist'))
            ->assertDontSee('Hidden Alias');
    }

    public function test_compatibility_warnings_and_host_preview_are_privacy_safe(): void
    {
        $booking = $this->occupantBooking([
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
        ]);
        $this->profileFor($booking->guest, [
            'public_alias' => 'Quiet person',
            'prefers_quiet' => true,
            'smokes' => true,
            'sleep_schedule' => 'early_bird',
        ]);
        app(RoomOccupantSnapshotService::class)->createFromBooking($booking);

        $guest = User::factory()->create([
            'prefers_quiet' => false,
            'is_smoker' => false,
            'sleep_schedule' => 'night_owl',
        ]);

        $compatibility = app(RoommateCompatibilityService::class)->compareGuestWithOccupants(
            $guest,
            $booking->room,
            new DateRange('2026-07-11', '2026-07-14'),
        );
        $encoded = json_encode($compatibility->toArray(), JSON_THROW_ON_ERROR);

        $this->assertStringContainsString(__('occupants.warnings.quiet_conflict'), $encoded);
        $this->assertStringContainsString(__('occupants.warnings.smoking_conflict'), $encoded);
        $this->assertStringContainsString(__('occupants.warnings.sleep_schedule_conflict'), $encoded);

        $ownedRoom = $booking->room->fresh();
        $roomHost = User::query()->findOrFail($ownedRoom->property->host_user_id);

        Livewire::actingAs($roomHost)
            ->test(RoomOccupantsPreview::class, [
                'room' => $ownedRoom,
                'checkIn' => '2026-07-11',
                'checkOut' => '2026-07-14',
            ])
            ->assertSee(__('occupants.host_preview.title'))
            ->assertSee(__('occupants.occupants_count', ['count' => 1]))
            ->assertDontSee($booking->guest->email);

        Livewire::actingAs(User::factory()->create(['is_host' => true]))
            ->test(RoomOccupantsPreview::class, [
                'room' => $ownedRoom,
                'checkIn' => '2026-07-11',
                'checkOut' => '2026-07-14',
            ])
            ->assertForbidden();
    }

    public function test_hidden_visibility_settings_prevent_profile_details_from_rendering(): void
    {
        $booking = $this->occupantBooking();
        $this->profileFor($booking->guest, [
            'public_alias' => 'Secret Alias',
            'languages_json' => ['en'],
            'guest_type' => 'student',
            'student' => true,
            'prefers_quiet' => true,
        ], [
            'allow_profile_in_prebooking_summary' => false,
            'allow_profile_after_confirmed_booking' => false,
        ]);
        app(RoomOccupantSnapshotService::class)->createFromBooking($booking);

        $privacy = app(CoLivingPrivacyService::class);
        $this->assertFalse($privacy->canShowBeforeBooking($booking->guest));

        $summary = app(RoomOccupantSummaryService::class)->getPreBookingSummary(
            $booking->room,
            new DateRange('2026-07-10', '2026-07-15'),
        );
        $encoded = json_encode($summary->toArray(), JSON_THROW_ON_ERROR);

        $this->assertSame(1, $summary->occupantsCount);
        $this->assertStringNotContainsString('Secret Alias', $encoded);
        $this->assertStringNotContainsString(__('occupants.student'), $encoded);
    }

    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @param  array<string, mixed>  $guestOverrides
     */
    private function occupantBooking(array $bookingOverrides = [], array $guestOverrides = []): Booking
    {
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create(array_merge([
            'name' => 'Roommate Private Name',
            'phone' => '+37060000002',
            'email' => 'roommate-'.uniqid().'@example.test',
        ], $guestOverrides));
        $property = Property::factory()->for($host, 'host')->create([
            'user_id' => $host->id,
            'host_user_id' => $host->id,
            'status' => PropertyStatus::Active,
        ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'sleeping_places_count' => 4,
            'max_guests' => 4,
        ]);

        return $this->bookingForRoom($room, $guest, array_merge([
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-15',
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ], $bookingOverrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function bookingForRoom(Room $room, User $guest, array $overrides = []): Booking
    {
        $property = $room->property ?: Property::query()->find($room->property_id);
        if (! $property) {
            $host = User::factory()->create(['is_host' => true]);
            $property = Property::factory()->for($host, 'host')->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'status' => PropertyStatus::Active,
            ]);
            $room->forceFill(['property_id' => $property->id])->save();
        }

        $hostId = (int) ($overrides['host_user_id'] ?? $property->host_user_id);
        $propertyId = (int) ($overrides['property_id'] ?? $property->id);
        $sleepingPlace = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create(['status' => SleepingPlaceStatus::Active]);

        $checkIn = (string) ($overrides['check_in_date'] ?? '2026-07-10');
        $checkOut = (string) ($overrides['check_out_date'] ?? '2026-07-15');

        return Booking::factory()->create(array_merge([
            'guest_user_id' => $guest->id,
            'host_user_id' => $hostId,
            'property_id' => $propertyId,
            'room_id' => $room->id,
            'sleeping_place_id' => $sleepingPlace->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $profileOverrides
     * @param  array<string, mixed>  $visibilityOverrides
     */
    private function profileFor(User $user, array $profileOverrides = [], array $visibilityOverrides = []): CoLivingProfile
    {
        $profile = CoLivingProfile::factory()->for($user, 'user')->create(array_merge([
            'public_alias' => 'Roommate',
            'age_range' => '25-34',
            'languages_json' => ['en'],
            'guest_type' => 'short_term_guest',
            'short_term_guest' => true,
            'smokes' => false,
            'prefers_quiet' => true,
        ], $profileOverrides));

        CoLivingVisibilitySetting::factory()->for($user, 'user')->create(array_merge([
            'show_public_alias' => true,
            'show_age_range' => true,
            'show_languages' => true,
            'show_guest_type' => true,
            'show_sleep_schedule' => true,
            'show_smoking_status' => true,
            'show_quiet_preference' => true,
            'show_roommate_rating' => true,
            'show_checkout_date_to_future_roommates' => true,
            'allow_profile_in_prebooking_summary' => true,
            'allow_profile_after_confirmed_booking' => true,
        ], $visibilityOverrides));

        return $profile;
    }
}
