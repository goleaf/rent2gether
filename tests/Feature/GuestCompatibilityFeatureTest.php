<?php

namespace Tests\Feature;

use App\Data\Occupants\DateRange;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Bookings\CompatibilityCheckBeforeBooking;
use App\Livewire\Listings\Detail\CompatibilitySummarySection;
use App\Livewire\Profile\GuestCompatibilityPrivacySettings;
use App\Livewire\Profile\GuestCompatibilityProfileForm;
use App\Livewire\Search\CompatibilityBadge;
use App\Livewire\Search\CompatibilityFilter;
use App\Models\CompatibilityResult;
use App\Models\GuestCompatibilityProfile;
use App\Models\GuestCompatibilityVisibilitySetting;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomCompatibilityProfile;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCompatibilityProfile;
use App\Models\User;
use App\Services\Compatibility\CompatibilityCacheService;
use App\Services\Compatibility\CompatibilityCalculatorService;
use App\Services\Compatibility\RoomCompatibilityProfileService;
use App\Services\Compatibility\SleepingPlaceCompatibilityProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class GuestCompatibilityFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_compatibility_tables_relationships_indexes_and_cascade_delete_exist(): void
    {
        $this->assertTrue(Schema::hasTable('guest_compatibility_profiles'));
        $this->assertTrue(Schema::hasTable('guest_compatibility_visibility_settings'));
        $this->assertTrue(Schema::hasTable('room_compatibility_profiles'));
        $this->assertTrue(Schema::hasTable('sleeping_place_compatibility_profiles'));
        $this->assertTrue(Schema::hasTable('compatibility_results'));
        $this->assertTrue(Schema::hasColumn('guest_compatibility_profiles', 'needs_fast_wifi'));
        $this->assertTrue(Schema::hasColumn('compatibility_results', 'blocking_reasons_json'));
        $this->assertTrue(Schema::hasIndex('guest_compatibility_profiles', ['user_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('room_compatibility_profiles', ['room_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('sleeping_place_compatibility_profiles', ['sleeping_place_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('compatibility_results', ['user_id', 'sleeping_place_id']));

        $guest = User::factory()->create();
        $place = $this->place();

        $profile = GuestCompatibilityProfile::factory()->for($guest, 'user')->create();
        $settings = GuestCompatibilityVisibilitySetting::factory()->for($guest, 'user')->create();
        $roomProfile = RoomCompatibilityProfile::factory()->for($place->room)->create();
        $placeProfile = SleepingPlaceCompatibilityProfile::factory()->for($place)->create();
        $result = CompatibilityResult::factory()
            ->for($guest, 'user')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create();

        $this->assertSame($guest->id, $profile->user->id);
        $this->assertSame($guest->id, $settings->user->id);
        $this->assertSame($place->room_id, $roomProfile->room->id);
        $this->assertSame($place->id, $placeProfile->sleepingPlace->id);
        $this->assertSame($place->id, $result->sleepingPlace->id);

        $guest->delete();
        $place->room->delete();
        $place->delete();

        $this->assertDatabaseMissing('guest_compatibility_profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('guest_compatibility_visibility_settings', ['id' => $settings->id]);
        $this->assertDatabaseMissing('compatibility_results', ['id' => $result->id]);
        $this->assertDatabaseMissing('room_compatibility_profiles', ['id' => $roomProfile->id]);
        $this->assertDatabaseMissing('sleeping_place_compatibility_profiles', ['id' => $placeProfile->id]);
    }

    public function test_room_and_sleeping_place_profiles_sync_from_current_models(): void
    {
        $place = $this->place(
            roomOverrides: [
                'gender_policy' => GenderType::Mixed->value,
                'sleeping_places_count' => 4,
                'current_guests_count' => 2,
                'noise_level' => 'quiet',
                'light_level' => 'moderate',
                'has_desk' => true,
                'has_chair' => true,
                'has_lock' => true,
                'has_window' => true,
                'can_work_at_night' => true,
                'can_turn_light_at_night' => true,
                'is_for_long_stay' => true,
            ],
            placeOverrides: [
                'type' => SleepingPlaceType::BunkBottom->value,
                'sleeping_place_type' => SleepingPlaceType::BunkBottom->value,
                'is_bottom_bunk' => true,
                'has_curtain' => true,
                'has_locker' => true,
                'locker_has_lock' => true,
                'has_power_socket' => true,
                'has_usb' => true,
                'has_lamp' => true,
                'has_shelf' => true,
                'has_luggage_space' => true,
                'has_bedding' => true,
                'has_towel' => true,
                'privacy_level' => 'high',
            ],
        );

        $roomProfile = app(RoomCompatibilityProfileService::class)->syncFromRoom($place->room);
        $placeProfile = app(SleepingPlaceCompatibilityProfileService::class)->syncFromSleepingPlace($place);

        $this->assertSame('mixed', $roomProfile->gender_policy);
        $this->assertSame(4, $roomProfile->max_people_in_room);
        $this->assertTrue($roomProfile->has_workspace);
        $this->assertTrue($roomProfile->can_work_at_night);
        $this->assertTrue($roomProfile->long_stay_allowed);
        $this->assertSame(SleepingPlaceType::BunkBottom->value, $placeProfile->sleeping_place_type);
        $this->assertTrue($placeProfile->is_bottom_bunk);
        $this->assertTrue($placeProfile->has_locker);
        $this->assertTrue($placeProfile->has_power_socket);
    }

    public function test_calculator_scores_matches_warnings_blocking_reasons_and_cache(): void
    {
        $guest = User::factory()->create();
        GuestCompatibilityProfile::factory()->for($guest, 'user')->create([
            'needs_quiet_at_night' => true,
            'remote_worker' => true,
            'needs_workspace' => true,
            'needs_fast_wifi' => true,
            'needs_locker' => true,
            'avoids_upper_bunk' => true,
            'travelling_with_pet' => true,
            'needs_24_7_access' => true,
            'max_people_in_room' => 2,
        ]);
        GuestCompatibilityVisibilitySetting::factory()->for($guest, 'user')->create();

        $place = $this->place(
            roomOverrides: [
                'noise_level' => 'high',
                'has_desk' => false,
                'has_chair' => false,
                'can_work_at_night' => false,
                'can_turn_light_at_night' => false,
                'sleeping_places_count' => 6,
                'max_guests' => 6,
                'current_guests_count' => 5,
            ],
            placeOverrides: [
                'type' => SleepingPlaceType::BunkTop->value,
                'sleeping_place_type' => SleepingPlaceType::BunkTop->value,
                'is_top_bunk' => true,
                'has_locker' => false,
                'has_power_socket' => false,
                'min_nights' => 1,
                'max_nights' => 5,
            ],
            propertyOverrides: [
                'rules' => ['no_pets', 'no_smoking'],
                'amenities' => [],
            ],
        );

        app(RoomCompatibilityProfileService::class)->syncFromRoom($place->room)->forceFill([
            'pets_allowed' => false,
            'pets_present' => false,
            'smoking_allowed' => false,
            'late_entry_allowed' => false,
        ])->save();
        app(SleepingPlaceCompatibilityProfileService::class)->syncFromSleepingPlace($place);

        $range = new DateRange('2026-07-10', '2026-07-20');
        $result = app(CompatibilityCalculatorService::class)->calculate($guest, $place, $range);
        $cached = app(CompatibilityCacheService::class)->getCached($guest, $place, $range);

        $this->assertSame('not_suitable', $result->fitStatus);
        $this->assertContains('pet_forbidden', collect($result->blockingReasons)->pluck('key')->all());
        $this->assertContains('upper_bunk_conflict', collect($result->warningReasons)->pluck('key')->all());
        $this->assertContains('locker_missing', collect($result->warningReasons)->pluck('key')->all());
        $this->assertContains('workspace_missing', collect($result->warningReasons)->pluck('key')->all());
        $this->assertLessThan(70, $result->score);
        $this->assertNotNull($cached);
        $this->assertSame($result->fitStatus, $cached->fit_status);

        app(CompatibilityCacheService::class)->forgetForUser($guest);

        $this->assertNull(app(CompatibilityCacheService::class)->getCached($guest, $place, $range));
    }

    public function test_perfect_match_returns_great_and_translated_reasons(): void
    {
        $guest = User::factory()->create();
        GuestCompatibilityProfile::factory()->for($guest, 'user')->create([
            'needs_quiet_at_night' => true,
            'remote_worker' => true,
            'needs_workspace' => true,
            'needs_fast_wifi' => true,
            'needs_locker' => true,
            'wants_lower_bunk' => true,
            'comfortable_with_shared_room' => true,
            'max_people_in_room' => 4,
        ]);
        GuestCompatibilityVisibilitySetting::factory()->for($guest, 'user')->create();

        $place = $this->place(
            roomOverrides: [
                'noise_level' => 'quiet',
                'has_desk' => true,
                'has_chair' => true,
                'can_work_at_night' => true,
                'sleeping_places_count' => 4,
                'max_guests' => 4,
            ],
            placeOverrides: [
                'type' => SleepingPlaceType::BunkBottom->value,
                'sleeping_place_type' => SleepingPlaceType::BunkBottom->value,
                'is_bottom_bunk' => true,
                'has_locker' => true,
                'locker_has_lock' => true,
                'has_power_socket' => true,
                'has_luggage_space' => true,
            ],
            propertyOverrides: [
                'rules' => ['quiet_hours', 'no_smoking'],
                'amenities' => ['fast_wifi', 'workspace', 'washing_machine'],
            ],
        );
        app(RoomCompatibilityProfileService::class)->syncFromRoom($place->room)->forceFill([
            'quiet_hours_enabled' => true,
            'has_workspace' => true,
            'has_personal_lockers' => true,
            'pets_allowed' => true,
            'late_entry_allowed' => true,
        ])->save();
        app(SleepingPlaceCompatibilityProfileService::class)->syncFromSleepingPlace($place);

        $result = app(CompatibilityCalculatorService::class)->calculate(
            $guest,
            $place,
            new DateRange('2026-07-10', '2026-07-13'),
        );
        $encoded = json_encode($result->toArray(), JSON_THROW_ON_ERROR);

        $this->assertSame('great', $result->fitStatus);
        $this->assertGreaterThanOrEqual(85, $result->score);
        $this->assertStringContainsString(__('compatibility.positive.quiet_match'), $encoded);
        $this->assertStringContainsString(__('compatibility.positive.workspace_match'), $encoded);
        $this->assertStringContainsString(__('compatibility.positive.wifi_match'), $encoded);
    }

    public function test_livewire_profile_privacy_summary_badge_filter_and_booking_check_render(): void
    {
        $guest = User::factory()->create();
        $place = $this->place(propertyOverrides: ['rules' => ['no_pets']]);

        Livewire::actingAs($guest)
            ->test(GuestCompatibilityProfileForm::class)
            ->set('smokes', false)
            ->set('needsQuietAtNight', true)
            ->set('remoteWorker', true)
            ->set('needsWorkspace', true)
            ->set('needsFastWifi', true)
            ->set('comfortableWithSharedRoom', true)
            ->set('maxPeopleInRoom', 4)
            ->set('needsLocker', true)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('compatibility.messages.profile_saved'));

        Livewire::actingAs($guest)
            ->test(GuestCompatibilityPrivacySettings::class)
            ->set('allowUseForMatching', true)
            ->set('allowShowToHosts', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee(__('compatibility.messages.privacy_saved'));

        app(RoomCompatibilityProfileService::class)->syncFromRoom($place->room);
        app(SleepingPlaceCompatibilityProfileService::class)->syncFromSleepingPlace($place);

        Livewire::actingAs($guest)
            ->test(CompatibilitySummarySection::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('compatibility.summary_title'))
            ->assertSee('%');

        Livewire::actingAs($guest)
            ->test(CompatibilityBadge::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('compatibility.title'));

        Livewire::test(CompatibilityFilter::class)
            ->set('minimumFit', 'good')
            ->set('hideNotSuitable', true)
            ->call('apply')
            ->assertDispatched('compatibility-filter-updated');

        GuestCompatibilityProfile::query()->where('user_id', $guest->id)->update([
            'travelling_with_pet' => true,
        ]);
        app(CompatibilityCacheService::class)->forgetForUser($guest);

        Livewire::actingAs($guest)
            ->test(CompatibilityCheckBeforeBooking::class, [
                'sleepingPlaceId' => $place->id,
                'checkIn' => '2026-07-10',
                'checkOut' => '2026-07-13',
            ])
            ->assertSee(__('compatibility.before_booking.title'))
            ->assertSee(__('compatibility.blocking.pet_forbidden'))
            ->call('continueAnyway')
            ->assertHasErrors(['compatibility']);
    }

    public function test_english_and_russian_copy_render(): void
    {
        $guest = User::factory()->create();
        GuestCompatibilityProfile::factory()->for($guest, 'user')->create();

        Livewire::actingAs($guest)
            ->test(GuestCompatibilityProfileForm::class)
            ->assertSee(__('compatibility.profile_title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(GuestCompatibilityProfileForm::class)
            ->assertSee(__('compatibility.profile_title', [], 'ru'));
    }

    /**
     * @param  array<string, mixed>  $roomOverrides
     * @param  array<string, mixed>  $placeOverrides
     * @param  array<string, mixed>  $propertyOverrides
     */
    private function place(array $roomOverrides = [], array $placeOverrides = [], array $propertyOverrides = []): SleepingPlace
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()->for($host, 'host')->create(array_merge([
            'user_id' => $host->id,
            'host_user_id' => $host->id,
            'status' => PropertyStatus::Active,
            'rules' => ['quiet_hours', 'no_smoking'],
            'amenities' => ['wifi', 'fast_wifi', 'workspace'],
        ], $propertyOverrides));
        $room = Room::factory()->for($property)->create(array_merge([
            'status' => RoomStatus::Active,
            'gender_policy' => GenderType::Mixed->value,
            'sleeping_places_count' => 4,
            'max_guests' => 4,
            'current_guests_count' => 1,
            'noise_level' => 'quiet',
            'has_desk' => true,
            'has_chair' => true,
        ], $roomOverrides));

        return SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single->value,
                'sleeping_place_type' => SleepingPlaceType::Single->value,
                'has_locker' => true,
                'locker_has_lock' => true,
                'has_power_socket' => true,
                'base_price_per_night' => 20,
                'min_nights' => 1,
                'max_nights' => 30,
            ], $placeOverrides));
    }
}
