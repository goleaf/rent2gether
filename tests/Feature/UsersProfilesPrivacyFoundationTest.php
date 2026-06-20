<?php

namespace Tests\Feature;

use App\Enums\UserRoleMode;
use App\Livewire\Guest\Profile\GuestCompatibilityForm;
use App\Livewire\Host\Profile\HostPublicProfileCard;
use App\Livewire\Profile\ProfilePage;
use App\Livewire\Users\PublicGuestProfileCard;
use App\Models\Booking;
use App\Models\BookingGuestIntake;
use App\Models\GuestProfile;
use App\Models\HostProfile;
use App\Models\HostRepresentative;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserActivitySummary;
use App\Models\UserDocument;
use App\Models\UserLanguage;
use App\Models\UserPrivacySetting;
use App\Models\UserVerification;
use App\Services\BookingGuestIntake\BookingGuestIntakeService;
use App\Services\Compatibility\GuestCompatibilityProfileService;
use App\Services\Users\HostRepresentativeService;
use App\Services\Users\UserActivitySummaryService;
use App\Services\Users\UserLanguageService;
use App\Services\Users\UserPrivacyService;
use App\Services\Users\UserProfileVisibilityService;
use App\Services\Users\UserRoleModeService;
use App\Services\Users\UserVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class UsersProfilesPrivacyFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_profile_foundation_tables_indexes_and_relationships_exist(): void
    {
        foreach ([
            'user_profiles',
            'guest_profiles',
            'host_profiles',
            'host_representatives',
            'user_verifications',
            'user_documents',
            'user_languages',
            'user_privacy_settings',
            'user_saved_preferences',
            'user_activity_summaries',
            'guest_compatibility_profiles',
            'booking_guest_intakes',
            'user_notification_preferences',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} table is missing.");
        }

        $this->assertTrue(Schema::hasColumn('users', 'last_seen_at'));
        $this->assertTrue(Schema::hasColumn('users', 'last_login_at'));
        $this->assertTrue(Schema::hasColumn('users', 'is_active'));
        $this->assertTrue(Schema::hasColumn('user_profiles', 'public_name'));
        $this->assertTrue(Schema::hasColumn('guest_profiles', 'needs_fast_wifi'));
        $this->assertTrue(Schema::hasColumn('host_profiles', 'host_type'));
        $this->assertTrue(Schema::hasColumn('booking_guest_intakes', 'guest_user_id'));

        $this->assertTrue(Schema::hasIndex('guest_profiles', ['user_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('host_representatives', ['host_user_id', 'active']));
        $this->assertTrue(Schema::hasIndex('user_verifications', ['user_id', 'verification_type']));
        $this->assertTrue(Schema::hasIndex('user_languages', ['user_id', 'language_code']));
        $this->assertTrue(Schema::hasIndex('user_activity_summaries', ['average_guest_rating']));

        $user = User::factory()->host()->create();
        $guestProfile = GuestProfile::factory()->for($user)->create();
        $hostProfile = HostProfile::factory()->for($user)->create();
        $language = UserLanguage::factory()->for($user)->create(['language_code' => 'ru']);
        $summary = UserActivitySummary::factory()->for($user)->create();

        $this->assertSame($guestProfile->id, $user->guestProfile->id);
        $this->assertSame($hostProfile->id, $user->hostProfile->id);
        $this->assertSame($language->id, $user->languages()->first()->id);
        $this->assertSame($summary->id, $user->activitySummary->id);
    }

    public function test_only_guest_host_and_guest_host_role_modes_are_supported(): void
    {
        $service = app(UserRoleModeService::class);
        $user = User::factory()->create();

        $this->assertSame(['guest', 'host', 'guest_host'], UserRoleMode::values());
        $this->assertSame(['guest', 'host', 'guest_host'], $service->allowedModes());

        $this->assertFalse(in_array('admin', $service->allowedModes(), true));
        $this->assertFalse(in_array('support', $service->allowedModes(), true));
        $this->assertFalse(in_array('manager', $service->allowedModes(), true));
        $this->assertFalse(in_array('cleaner', $service->allowedModes(), true));
        $this->assertFalse(in_array('finance', $service->allowedModes(), true));

        $service->switchToGuest($user);
        $this->assertTrue($user->refresh()->isGuest());
        $this->assertFalse($service->canCreateListing($user));
        $this->assertTrue($service->canBook($user));

        $service->switchToHost($user);
        $this->assertTrue($user->refresh()->isHost());
        $this->assertTrue($service->canCreateListing($user));
        $this->assertFalse($service->canBook($user));

        $service->enableGuestHostMode($user);
        $this->assertTrue($user->refresh()->isGuestHost());
        $this->assertTrue($service->canCreateListing($user));
        $this->assertTrue($service->canBook($user));
    }

    public function test_profiles_languages_privacy_verifications_and_representatives_can_be_managed(): void
    {
        $host = User::factory()->host()->create();
        $guest = User::factory()->create();

        $privacy = app(UserPrivacyService::class)->update($guest, [
            'show_real_name' => false,
            'show_city' => true,
            'show_phone_after_booking' => true,
        ]);
        $language = app(UserLanguageService::class)->add($guest, 'ru', 'native', true);
        $phone = app(UserVerificationService::class)->markVerified($guest, 'phone');
        $identity = app(UserVerificationService::class)->markVerified($guest, 'identity');
        $document = UserDocument::factory()->for($guest)->create([
            'document_type' => 'identity_document',
            'file_path' => 'private/documents/guest-passport.jpg',
        ]);
        $representative = app(HostRepresentativeService::class)->create($host, [
            'name' => 'Key pickup contact',
            'phone' => '+37060000000',
            'can_help_with_check_in' => true,
            'can_be_contacted_by_guest' => true,
        ]);

        $this->assertInstanceOf(UserPrivacySetting::class, $privacy);
        $this->assertSame('ru', $language->language_code);
        $this->assertSame('verified', $phone->status);
        $this->assertSame('verified', app(UserVerificationService::class)->getVerificationStatus($guest, 'identity'));
        $this->assertSame($identity->id, UserVerification::query()->where('verification_type', 'identity')->value('id'));
        $this->assertTrue($document->encrypted);
        $this->assertInstanceOf(HostRepresentative::class, $representative);
        $this->assertFalse(Schema::hasColumn('host_representatives', 'manager_role'));
    }

    public function test_host_view_of_guest_uses_allowlist_and_never_exposes_documents(): void
    {
        $host = User::factory()->host()->create(['name' => 'Private Host']);
        HostProfile::factory()->for($host)->create([
            'host_display_name' => 'Mila Host',
            'emergency_contact_phone' => '+37011111111',
        ]);

        $guest = User::factory()->create([
            'name' => 'Real Guest Name',
            'phone' => '+37069999999',
            'date_of_birth' => '1990-05-10',
        ]);
        $guest->profile()->create([
            'display_name' => 'Traveler',
            'first_name' => 'Real',
            'last_name' => 'Hidden',
            'public_name' => 'Traveler',
            'public_city_name' => 'Vilnius',
            'about' => 'Quiet guest.',
        ]);
        UserPrivacySetting::factory()->for($guest)->create([
            'show_real_name' => false,
            'show_age_range' => true,
            'show_city' => true,
        ]);
        UserDocument::factory()->for($guest)->create([
            'file_path' => 'private/documents/secret-id.png',
            'rejection_reason' => 'Sensitive internal note',
        ]);
        UserActivitySummary::factory()->for($guest)->create([
            'completed_stays_as_guest' => 3,
            'average_guest_rating' => 4.8,
            'confirmed_complaints_count' => 1,
        ]);
        app(UserVerificationService::class)->markVerified($guest, 'phone');
        app(UserVerificationService::class)->markVerified($guest, 'email');
        app(UserVerificationService::class)->markVerified($guest, 'identity');

        $visible = app(UserProfileVisibilityService::class)->buildHostViewOfGuest($host, $guest);
        $encoded = json_encode($visible, JSON_THROW_ON_ERROR);

        $this->assertSame('Traveler', $visible['public_name']);
        $this->assertSame('verified', $visible['verification']['identity']);
        $this->assertArrayHasKey('confirmed_complaints_count', $visible['activity']);
        $this->assertStringNotContainsString('secret-id.png', $encoded);
        $this->assertStringNotContainsString('Sensitive internal note', $encoded);
        $this->assertStringNotContainsString('+37069999999', $encoded);
        $this->assertStringNotContainsString('1990-05-10', $encoded);

        $publicHost = app(UserProfileVisibilityService::class)->buildPublicHostProfile($guest, $host);
        $hostEncoded = json_encode($publicHost, JSON_THROW_ON_ERROR);

        $this->assertSame('Mila Host', $publicHost['name']);
        $this->assertStringNotContainsString('+37011111111', $hostEncoded);
        $this->assertStringNotContainsString('emergency_contact_phone', $hostEncoded);
    }

    public function test_compatibility_warnings_intake_and_activity_summary_work_without_private_leaks(): void
    {
        [$guest, $host, $place] = $this->createUsersAndPlaceForWarnings();

        $profile = app(GuestCompatibilityProfileService::class)->update($guest, [
            'i_smoke' => true,
            'i_like_quiet' => true,
            'i_work_remotely' => true,
            'i_need_fast_internet' => true,
            'i_travel_with_pet' => true,
        ]);
        $warnings = app(GuestCompatibilityProfileService::class)->buildWarnings($guest, $place);

        $this->assertTrue($profile->i_smoke);
        $this->assertContains('smoking_conflict', collect($warnings)->pluck('key')->all());
        $this->assertContains('pet_conflict', collect($warnings)->pluck('key')->all());
        $this->assertContains('quiet_conflict', collect($warnings)->pluck('key')->all());

        $intake = app(BookingGuestIntakeService::class)->createForBooking($guest, [
            'sleeping_place_id' => $place->id,
            'trip_purpose' => 'work',
            'planned_arrival_time' => '23:00',
            'has_pet' => true,
            'smokes' => true,
            'needs_fast_wifi' => true,
            'message_to_host' => 'I will arrive late.',
        ]);
        $summary = app(BookingGuestIntakeService::class)->buildHostSummary($intake);

        $this->assertInstanceOf(BookingGuestIntake::class, $intake);
        $this->assertSame($guest->id, $intake->guest_user_id);
        $this->assertSame('work', $summary['trip_purpose']);
        $this->assertArrayNotHasKey('document_files', $summary);
        $this->assertArrayNotHasKey('file_path', $summary);
        $this->assertStringNotContainsString('private/documents', json_encode($summary, JSON_THROW_ON_ERROR));

        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($place)
            ->for($place->property)
            ->for($place->room)
            ->create(['status' => 'completed']);

        $activity = app(UserActivitySummaryService::class)
            ->incrementCompletedStayAsGuest($guest);
        app(UserActivitySummaryService::class)->incrementCompletedStayAsHost($host);
        app(UserActivitySummaryService::class)->recordNoShow($guest);
        app(UserActivitySummaryService::class)->refresh($guest);

        $this->assertInstanceOf(UserActivitySummary::class, $activity);
        $this->assertGreaterThanOrEqual(1, $guest->activitySummary()->first()->completed_stays_as_guest);
        $this->assertSame($booking->guest_user_id, $guest->id);
    }

    public function test_profile_livewire_components_render_translated_mobile_sections(): void
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        HostProfile::factory()->for($host)->create(['host_display_name' => 'Mila Host']);
        GuestProfile::factory()->for($guest)->create();

        Livewire::actingAs($guest)
            ->test(ProfilePage::class)
            ->assertSee(__('profiles.sections.basic', [], 'en'))
            ->assertSee(__('profiles.sections.privacy', [], 'en'));

        Livewire::actingAs($guest)
            ->test(GuestCompatibilityForm::class)
            ->assertSee(__('guest_profile.compatibility.title', [], 'en'));

        Livewire::test(PublicGuestProfileCard::class, ['userId' => $guest->id])
            ->assertSee(__('profiles.public.guest_title', [], 'en'));

        Livewire::test(HostPublicProfileCard::class, ['userId' => $host->id])
            ->assertSee(__('host_profile.public.title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(ProfilePage::class)
            ->assertSee(__('profiles.sections.basic', [], 'ru'));
    }

    public function test_documentation_and_forbidden_surfaces_contracts_exist(): void
    {
        foreach ([
            'docs/USERS_AND_PROFILES.md',
            'docs/GUEST_PROFILE_AND_COMPATIBILITY.md',
            'docs/HOST_PROFILE.md',
            'docs/USER_PRIVACY_RULES.md',
            'docs/USER_VERIFICATION_RULES.md',
        ] as $path) {
            $this->assertFileExists(base_path($path));
        }

        $this->assertDirectoryDoesNotExist(app_path('Filament'));
        $this->assertFalse(collect(glob(app_path('Livewire/**/*.php'), GLOB_BRACE))->contains(
            fn (string $path): bool => str_contains($path, 'Volt'),
        ));
    }

    /**
     * @return array{0:User,1:User,2:SleepingPlace}
     */
    private function createUsersAndPlaceForWarnings(): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'rules' => ['no_smoking', 'no_pets'],
                'amenities' => [],
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'user_id' => $host->id,
                'noise_level' => 'high',
                'has_desk' => false,
            ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'user_id' => $host->id,
                'has_locker' => false,
                'has_power_socket' => false,
                'has_socket' => false,
            ]);

        return [$guest, $host, $place];
    }
}
