<?php

namespace Tests\Feature;

use App\Livewire\Bookings\GuestIntake\GuestIntakeSummary;
use App\Livewire\Bookings\GuestIntake\GuestIntakeWizard;
use App\Livewire\Bookings\GuestIntake\HostIntakeSummary;
use App\Models\Booking;
use App\Models\BookingGuestIntake;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\BookingGuestIntake\BookingGuestIntakeMessageService;
use App\Services\BookingGuestIntake\BookingGuestIntakePrivacyService;
use App\Services\BookingGuestIntake\BookingGuestIntakeService;
use App\Services\BookingGuestIntake\BookingGuestIntakeSummaryService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BookingGuestIntakeFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 10:00:00');
        CarbonImmutable::setTestNow('2026-06-20 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_intake_schema_relationships_scopes_and_cascade_delete_exist(): void
    {
        $this->assertTrue(Schema::hasTable('booking_guest_intakes'));
        $this->assertTrue(Schema::hasColumn('booking_guest_intakes', 'trip_purpose_visibility'));
        $this->assertTrue(Schema::hasColumn('booking_guest_intakes', 'warnings_json'));
        $this->assertTrue(Schema::hasIndex('booking_guest_intakes', ['user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_guest_intakes', ['sleeping_place_id']));
        $this->assertTrue(Schema::hasIndex('booking_guest_intakes', ['compatibility_status']));

        [$guest, $place] = $this->createPlace();
        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create();

        $intake = BookingGuestIntake::factory()
            ->for($guest, 'user')
            ->for($booking)
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'status' => 'completed',
                'warnings_json' => [['key' => 'workspace_missing']],
                'blocking_reasons_json' => [['key' => 'pet_forbidden']],
            ]);

        $this->assertSame($guest->id, $intake->user->id);
        $this->assertSame($booking->id, $intake->booking->id);
        $this->assertSame($place->id, $intake->sleepingPlace->id);
        $this->assertSame(1, BookingGuestIntake::completed()->count());
        $this->assertSame(1, BookingGuestIntake::forUser($guest)->count());
        $this->assertSame(1, BookingGuestIntake::forBooking($booking)->count());
        $this->assertSame(1, BookingGuestIntake::withWarnings()->count());
        $this->assertSame(1, BookingGuestIntake::withBlockingReasons()->count());

        $guest->delete();

        $this->assertDatabaseMissing('booking_guest_intakes', ['id' => $intake->id]);
    }

    public function test_intake_draft_updates_completes_attaches_and_blocks_other_users(): void
    {
        [$guest, $place] = $this->createPlace();
        $service = app(BookingGuestIntakeService::class);

        $intake = $service->createDraft($guest, $place, [
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-13',
        ]);

        $this->assertSame('draft', $intake->status);
        $this->assertSame($place->property_id, $intake->property_id);

        $updated = $service->updateDraft($guest, $intake, [
            'trip_purpose' => 'work',
            'planned_arrival_time' => '19:00',
            'baggage_level' => 'one_bag',
            'baggage_count' => 1,
            'needs_workspace' => true,
            'needs_fast_wifi' => true,
            'host_message' => 'I will arrive quietly.',
        ]);

        $this->assertTrue($updated->needs_workspace);
        $this->assertSame('I will arrive quietly.', $updated->host_message);

        $this->expectException(AuthorizationException::class);
        $service->updateDraft(User::factory()->create(), $updated, ['trip_purpose' => 'study']);
    }

    public function test_complete_requires_rules_and_attach_to_booking(): void
    {
        [$guest, $place] = $this->createPlace();
        $service = app(BookingGuestIntakeService::class);

        $intake = $service->createDraft($guest, $place, []);
        $service->updateDraft($guest, $intake, [
            'trip_purpose' => 'work',
            'planned_arrival_time' => '18:30',
            'host_message' => 'I am coming for work.',
        ]);

        try {
            $service->complete($guest, $intake->refresh());
            $this->fail('Rules acceptance should be required.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('rules_accepted', $exception->errors());
        }

        $completed = $service->updateDraft($guest, $intake->refresh(), [
            'rules_accepted' => true,
        ]);
        $completed = $service->complete($guest, $completed->refresh());
        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create();
        $attached = $service->attachToBooking($completed, $booking);

        $this->assertSame('completed', $completed->status);
        $this->assertNotNull($completed->rules_accepted_at);
        $this->assertSame($booking->id, $attached->booking_id);
    }

    public function test_sensitive_medical_purpose_is_hidden_unless_guest_allows_exact_visibility(): void
    {
        [$guest, $place] = $this->createPlace();
        $intake = BookingGuestIntake::factory()
            ->for($guest, 'user')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'trip_purpose' => 'medical',
                'trip_purpose_visibility' => 'safe',
                'rules_accepted' => true,
            ]);

        $privacy = app(BookingGuestIntakePrivacyService::class);
        $summary = app(BookingGuestIntakeSummaryService::class)->buildHostSummary($intake);

        $this->assertTrue($privacy->shouldHideSensitiveTripPurpose($intake));
        $this->assertSame(__('guest_intake.trip_purposes.private_trip'), $privacy->getSafeTripPurposeLabel($intake, 'en'));
        $this->assertSame(__('guest_intake.trip_purposes.private_trip'), $summary['trip_purpose']);

        $intake->forceFill(['trip_purpose_visibility' => 'exact'])->save();

        $this->assertFalse($privacy->shouldHideSensitiveTripPurpose($intake->refresh()));
        $this->assertSame(__('guest_intake.trip_purposes.medical'), $privacy->getSafeTripPurposeLabel($intake, 'en'));
    }

    public function test_validation_warnings_blocking_reasons_and_generated_message_use_safe_fields(): void
    {
        [$guest, $place] = $this->createPlace(
            propertyOverrides: [
                'rules' => ['no_pets', 'no_smoking'],
                'amenities' => [],
            ],
            roomOverrides: [
                'noise_level' => 'high',
                'has_desk' => false,
                'has_chair' => false,
            ],
            placeOverrides: [
                'early_check_in_allowed' => false,
                'late_check_out_allowed' => false,
                'has_luggage_space' => false,
            ],
        );

        $intake = app(BookingGuestIntakeService::class)->createDraft($guest, $place, [
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-13',
        ]);

        $intake = app(BookingGuestIntakeService::class)->updateDraft($guest, $intake, [
            'trip_purpose' => 'medical',
            'trip_purpose_visibility' => 'safe',
            'planned_arrival_time' => '08:00',
            'early_check_in_requested' => true,
            'late_check_out_requested' => true,
            'has_pet' => true,
            'smokes' => true,
            'needs_quiet' => true,
            'needs_workspace' => true,
            'needs_fast_wifi' => true,
            'needs_luggage_storage_before_checkin' => true,
            'needs_registration' => true,
            'needs_work_documents' => true,
            'rules_accepted' => true,
        ]);
        $completed = app(BookingGuestIntakeService::class)->complete($guest, $intake->refresh());

        $this->assertSame('needs_attention', $completed->compatibility_status);
        $this->assertContains('pet_forbidden', collect($completed->blocking_reasons_json)->pluck('key')->all());
        $this->assertContains('early_check_in_unavailable', collect($completed->warnings_json)->pluck('key')->all());
        $this->assertContains('workspace_missing', collect($completed->warnings_json)->pluck('key')->all());
        $this->assertContains('documents_need_confirmation', collect($completed->warnings_json)->pluck('key')->all());

        $message = app(BookingGuestIntakeMessageService::class)->generateHostMessage($completed, 'en');

        $this->assertStringContainsString('private trip', str($message)->lower()->toString());
        $this->assertStringNotContainsString('medical', str($message)->lower()->toString());
        $this->assertStringContainsString('quiet', str($message)->lower()->toString());
    }

    public function test_livewire_wizard_summary_and_host_summary_render_in_english_and_russian(): void
    {
        [$guest, $place] = $this->createPlace();

        Livewire::actingAs($guest)
            ->test(GuestIntakeWizard::class, ['sleepingPlaceId' => $place->id])
            ->assertSee(__('guest_intake.title', [], 'en'))
            ->set('tripPurpose', 'work')
            ->set('plannedArrivalTime', '19:00')
            ->set('baggageLevel', 'one_bag')
            ->set('needsQuiet', true)
            ->set('needsWorkspace', true)
            ->set('rulesAccepted', true)
            ->call('saveCurrentStep')
            ->assertHasNoErrors()
            ->assertSee(__('guest_intake.messages.draft_saved', [], 'en'));

        $intake = BookingGuestIntake::query()->where('user_id', $guest->id)->firstOrFail();

        Livewire::actingAs($guest)
            ->test(GuestIntakeSummary::class, ['intakeId' => $intake->id])
            ->assertSee(__('guest_intake.summary.guest_title', [], 'en'));

        Livewire::test(HostIntakeSummary::class, ['intakeId' => $intake->id])
            ->assertSee(__('guest_intake.summary.host_title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(GuestIntakeWizard::class, ['sleepingPlaceId' => $place->id])
            ->assertSee(__('guest_intake.title', [], 'ru'));
    }

    /**
     * @param  array<string, mixed>  $propertyOverrides
     * @param  array<string, mixed>  $roomOverrides
     * @param  array<string, mixed>  $placeOverrides
     * @return array{0:User,1:SleepingPlace}
     */
    private function createPlace(array $propertyOverrides = [], array $roomOverrides = [], array $placeOverrides = []): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create();
        $property = Property::factory()->for($host, 'host')->create(array_merge([
            'user_id' => $host->id,
            'host_user_id' => $host->id,
            'rules' => ['quiet_hours', 'no_smoking'],
            'amenities' => ['wifi', 'fast_wifi', 'workspace', 'luggage_storage'],
        ], $propertyOverrides));
        $room = Room::factory()->for($property)->create(array_merge([
            'noise_level' => 'quiet',
            'has_desk' => true,
            'has_chair' => true,
        ], $roomOverrides));
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(array_merge([
                'base_price_per_night' => 20,
                'max_guests' => 1,
                'early_check_in_allowed' => true,
                'late_check_out_allowed' => true,
                'has_luggage_space' => true,
            ], $placeOverrides));

        return [$guest, $place];
    }
}
