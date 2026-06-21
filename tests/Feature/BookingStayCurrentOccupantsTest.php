<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Host\Stays\HostCurrentResidentsPage;
use App\Livewire\Stays\StayRoommatesPreview;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingStayEvent;
use App\Models\BookingStayStatusLog;
use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CheckIn\BookingCheckInService;
use App\Services\Stays\BookingStayService;
use App\Services\Stays\CurrentOccupancyService;
use App\Services\Stays\GuestRoommatesPreviewService;
use App\Services\Stays\HostCurrentResidentsService;
use App\Services\Stays\StayCompatibilityService;
use App\Services\Stays\StayNoteService;
use App\Services\Stays\StayPrivacyService;
use App\Services\Stays\StayStatusService;
use App\Services\Stays\StayVisibilityService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class BookingStayCurrentOccupantsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2026-07-12 12:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_point_eleven_schema_and_relationship_contract_exists(): void
    {
        $this->assertTrue(Schema::hasTable('booking_stays'));
        $this->assertTrue(Schema::hasTable('booking_stay_occupants'));
        $this->assertTrue(Schema::hasTable('room_current_occupancy_snapshots'));
        $this->assertTrue(Schema::hasTable('property_current_occupancy_snapshots'));
        $this->assertTrue(Schema::hasTable('stay_visibility_preferences'));
        $this->assertTrue(Schema::hasTable('booking_stay_status_logs'));
        $this->assertTrue(Schema::hasTable('booking_stay_notes'));
        $this->assertTrue(Schema::hasTable('booking_stay_events'));

        $this->assertTrue(Schema::hasIndex('booking_stays', ['stay_number'], 'unique'));
        $this->assertTrue(Schema::hasIndex('booking_stays', ['booking_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('booking_stays', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_stay_occupants', ['booking_stay_id']));
        $this->assertTrue(Schema::hasIndex('room_current_occupancy_snapshots', ['room_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('property_current_occupancy_snapshots', ['property_id'], 'unique'));
        $this->assertTrue(Schema::hasIndex('booking_stay_status_logs', ['booking_stay_id']));
        $this->assertTrue(Schema::hasIndex('booking_stay_events', ['booking_stay_id']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $stay = app(BookingStayService::class)->createFromCheckIn($checkIn);

        $this->assertSame($booking->id, $stay->booking->id);
        $this->assertSame($listing['place']->id, $stay->sleepingPlace->id);
        $this->assertSame($stay->id, $booking->fresh()->stay->id);
        $this->assertMatchesRegularExpression('/^STAY-\d{4}-\d{6}$/', $stay->stay_number);
    }

    public function test_stay_is_created_from_check_in_with_occupants_visibility_and_events(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        BookingGuest::factory()->for($booking)->create([
            'user_id' => null,
            'guest_name' => 'Second Guest',
            'full_name' => 'Second Guest',
            'guest_type' => 'second_guest',
            'is_main_guest' => false,
        ]);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $checkIn->forceFill([
            'guest_confirmed_at' => now()->subMinute(),
            'checked_in_at' => now(),
            'actual_check_in_at' => now(),
            'status' => 'checked_in',
        ])->save();

        $stay = app(BookingStayService::class)->createFromCheckIn($checkIn->refresh());

        $this->assertSame('active', $stay->status);
        $this->assertSame($booking->id, $stay->booking_id);
        $this->assertSame($booking->sleeping_place_id, $stay->sleeping_place_id);
        $this->assertSame(3, $stay->nights_count);
        $this->assertSame(2, $stay->nights_passed);
        $this->assertSame(1, $stay->nights_remaining);
        $this->assertSame(2, $stay->occupants()->count());
        $this->assertTrue($stay->visibilityPreference()->exists());
        $this->assertTrue($stay->events()->where('event_key', 'stay_started')->exists());

        $this->assertDatabaseHas('booking_stay_occupants', [
            'booking_stay_id' => $stay->id,
            'occupant_name' => 'Second Guest',
            'occupant_type' => 'second_guest',
        ]);
    }

    public function test_guest_host_privacy_and_notes_keep_roommate_summary_safe(): void
    {
        $listing = $this->listing();
        $other = $this->listing();
        $booking = $this->booking($listing, guestOverrides: [
            'name' => 'Private Full Name',
            'email' => 'private@example.test',
            'phone' => '+37060000009',
        ]);
        $stay = app(BookingStayService::class)->createForBooking($booking);
        $occupant = $stay->occupants()->firstOrFail();
        $occupant->forceFill([
            'age_range' => '25-34',
            'city_name' => 'Vilnius',
            'languages_json' => ['en', 'ru'],
            'stay_purpose' => 'work',
            'sleep_schedule' => 'sleeps_late',
            'smoking_status' => 'does_not_smoke',
            'sociability_level' => 'prefers_quiet',
            'public_visibility' => 'roommates_only',
        ])->save();

        app(StayVisibilityService::class)->updateVisibility($listing['guest'], $stay, [
            'show_public_name' => false,
            'show_city' => false,
            'show_sleep_schedule' => true,
            'show_sociability_level' => true,
        ]);
        app(StayNoteService::class)->addHostNote($listing['host'], $stay, 'Host-only operational note.');
        app(StayNoteService::class)->addGuestNote($listing['guest'], $stay, 'Guest-visible note.');

        $privacy = app(StayPrivacyService::class);
        $guestFiltered = $privacy->filterStayForGuest($listing['guest'], $stay->refresh());
        $hostFiltered = $privacy->filterStayForHost($listing['host'], $stay->refresh());
        $summary = app(GuestRoommatesPreviewService::class)->getRoommatesForBooking($listing['guest'], $booking);
        $encodedSummary = json_encode($summary->toArray(), JSON_THROW_ON_ERROR);

        $this->assertTrue($privacy->canGuestViewStay($listing['guest'], $stay));
        $this->assertFalse($privacy->canGuestViewStay($other['guest'], $stay));
        $this->assertTrue($privacy->canHostViewStay($listing['host'], $stay));
        $this->assertFalse($privacy->canHostViewStay($other['host'], $stay));
        $this->assertSame('Guest-visible note.', $guestFiltered['notes'][0]['note']);
        $this->assertStringNotContainsString('Host-only operational note.', json_encode($guestFiltered, JSON_THROW_ON_ERROR));
        $this->assertStringContainsString('Host-only operational note.', json_encode($hostFiltered, JSON_THROW_ON_ERROR));
        $this->assertStringNotContainsString('Private Full Name', $encodedSummary);
        $this->assertStringNotContainsString('private@example.test', $encodedSummary);
        $this->assertStringNotContainsString('+37060000009', $encodedSummary);
    }

    public function test_occupancy_snapshots_recalculate_after_check_in_and_checkout(): void
    {
        $listing = $this->listing();
        SleepingPlace::factory()
            ->for($listing['property'])
            ->for($listing['room'])
            ->create(['display_name' => 'Bed 3', 'place_number' => '3']);
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $stay = app(BookingStayService::class)->createFromCheckIn($checkIn);

        app(CurrentOccupancyService::class)->recalculateAfterCheckIn($checkIn);

        $roomSnapshot = RoomCurrentOccupancySnapshot::query()->where('room_id', $listing['room']->id)->firstOrFail();
        $propertySnapshot = PropertyCurrentOccupancySnapshot::query()->where('property_id', $listing['property']->id)->firstOrFail();

        $this->assertSame(1, $roomSnapshot->current_occupants_count);
        $this->assertSame(1, $roomSnapshot->occupied_sleeping_places_count);
        $this->assertSame(1, $roomSnapshot->free_sleeping_places_count);
        $this->assertSame(1, $propertySnapshot->current_occupants_count);
        $this->assertSame(1, $propertySnapshot->occupied_sleeping_places_count);

        $stay->forceFill([
            'actual_check_out_at' => now(),
            'status' => 'completed',
            'ended_at' => now(),
        ])->save();

        app(CurrentOccupancyService::class)->recalculateRoom($listing['room']->fresh());
        app(CurrentOccupancyService::class)->recalculateProperty($listing['property']->fresh());

        $this->assertSame(0, $roomSnapshot->fresh()->current_occupants_count);
        $this->assertSame(2, $roomSnapshot->fresh()->free_sleeping_places_count);
        $this->assertSame(0, $propertySnapshot->fresh()->current_occupants_count);
    }

    public function test_stay_status_host_resident_filters_and_compatibility_work(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $stay = app(BookingStayService::class)->createForBooking($booking);

        app(StayStatusService::class)->transition($stay, 'checkout_soon', $listing['host']);
        app(StayStatusService::class)->transition($stay->refresh(), 'completed', $listing['host']);
        app(StayCompatibilityService::class)->buildRoommateCompatibilityWarnings($listing['guest'], $listing['room']);

        $residents = app(HostCurrentResidentsService::class)->getCurrentResidents($listing['host'], ['status' => 'completed']);
        $checkoutSoon = app(HostCurrentResidentsService::class)->getCheckoutSoonResidents($listing['host']);

        $this->assertSame('completed', $stay->fresh()->status);
        $this->assertGreaterThanOrEqual(2, BookingStayStatusLog::query()->where('booking_stay_id', $stay->id)->count());
        $this->assertTrue(BookingStayEvent::query()->where('booking_stay_id', $stay->id)->where('event_key', 'stay_completed')->exists());
        $this->assertSame(1, $residents->count());
        $this->assertSame(0, $checkoutSoon->count());
    }

    public function test_required_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $stay = app(BookingStayService::class)->createForBooking($booking);

        Livewire::actingAs($listing['guest'])
            ->test(StayRoommatesPreview::class, ['stay' => $stay])
            ->assertSee(__('occupants.title', [], 'en'));

        Livewire::actingAs($listing['host'])
            ->test(HostCurrentResidentsPage::class)
            ->assertSee(__('stays.host_title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($listing['guest'])
            ->test(StayRoommatesPreview::class, ['stay' => $stay])
            ->assertSee(__('occupants.title', [], 'ru'));

        Livewire::actingAs($listing['host'])
            ->test(HostCurrentResidentsPage::class)
            ->assertSee(__('stays.host_title', [], 'ru'));
    }

    /**
     * @return array{guest:User, host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(): array
    {
        $guest = User::factory()->create(['name' => 'Stay Guest']);
        $host = User::factory()->host()->create(['name' => 'Stay Host']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'title' => 'Stay Property',
                'city' => 'Vilnius',
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'user_id' => $host->id,
                'title' => 'Room 1',
                'sleeping_places_count' => 2,
            ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'display_name' => 'Bed 2',
                'place_number' => '2',
                'base_price_per_night' => 20,
                'currency' => 'EUR',
            ]);

        return [
            'guest' => $guest,
            'host' => $host,
            'property' => $property,
            'room' => $room,
            'place' => $place,
        ];
    }

    /**
     * @param  array{guest:User, host:User, property:Property, room:Room, place:SleepingPlace}  $listing
     * @param  array<string, mixed>  $overrides
     * @param  array<string, mixed>  $guestOverrides
     */
    private function booking(array $listing, array $overrides = [], array $guestOverrides = []): Booking
    {
        if ($guestOverrides !== []) {
            $listing['guest']->forceFill($guestOverrides)->save();
        }

        return Booking::factory()
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create(array_merge([
                'guest_id' => $listing['guest']->id,
                'host_id' => $listing['host']->id,
                'guest_user_id' => $listing['guest']->id,
                'host_user_id' => $listing['host']->id,
                'property_id' => $listing['property']->id,
                'room_id' => $listing['room']->id,
                'sleeping_place_id' => $listing['place']->id,
                'status' => BookingStatus::StayInProgress->value,
                'payment_status' => PaymentStatus::Paid->value,
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-13',
                'check_in' => '2026-07-10',
                'check_out' => '2026-07-13',
                'check_in_time' => '15:00',
                'check_out_time' => '10:00',
                'nights_count' => 3,
                'nights' => 3,
                'calendar_presence_days_count' => 4,
                'calendar_days_count' => 4,
                'total_amount' => 126,
                'total_payable' => 126,
                'total' => 126,
                'currency' => 'EUR',
                'paid_at' => now()->subDays(3),
                'checked_in_at' => now()->subDays(2),
            ], $overrides));
    }
}
