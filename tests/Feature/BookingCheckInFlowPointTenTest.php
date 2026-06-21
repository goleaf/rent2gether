<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\CheckIn\CheckInAccessDetailsCard;
use App\Livewire\Bookings\CheckIn\CheckInInstructionCard;
use App\Livewire\Bookings\CheckIn\CheckInMediaUploader;
use App\Livewire\Bookings\CheckIn\CheckInProblemSheet;
use App\Livewire\Bookings\CheckIn\CheckInStepsList;
use App\Livewire\Bookings\CheckIn\GuestArrivalButtons;
use App\Livewire\Bookings\CheckIn\GuestCheckInPage;
use App\Livewire\Host\CheckIn\HostCheckInDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckInMedia;
use App\Models\BookingCheckInProblem;
use App\Models\BookingCheckInStatusLog;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CheckIn\BookingCheckInAccessDisclosureService;
use App\Services\CheckIn\BookingCheckInInstructionService;
use App\Services\CheckIn\BookingCheckInInventoryService;
use App\Services\CheckIn\BookingCheckInMediaService;
use App\Services\CheckIn\BookingCheckInPrivacyService;
use App\Services\CheckIn\BookingCheckInProblemService;
use App\Services\CheckIn\BookingCheckInService;
use App\Services\CheckIn\BookingCheckInStepService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCheckInFlowPointTenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-06-21 09:00:00');
    }

    public function test_point_ten_schema_and_relationship_contract_exists(): void
    {
        $this->assertTrue(Schema::hasTable('booking_check_ins'));
        $this->assertTrue(Schema::hasTable('booking_check_in_instructions'));
        $this->assertTrue(Schema::hasTable('booking_check_in_access_disclosures'));
        $this->assertTrue(Schema::hasTable('booking_check_in_steps'));
        $this->assertTrue(Schema::hasTable('booking_check_in_media'));
        $this->assertTrue(Schema::hasTable('booking_check_in_problems'));
        $this->assertTrue(Schema::hasTable('booking_check_in_status_logs'));

        $this->assertTrue(Schema::hasIndex('booking_check_ins', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_instructions', ['booking_check_in_id']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_access_disclosures', ['booking_check_in_id']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_steps', ['booking_check_in_id', 'step_key']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_media', ['booking_check_in_id']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_problems', ['host_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_status_logs', ['booking_check_in_id']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);

        $this->assertSame($booking->id, $checkIn->booking->id);
        $this->assertSame($listing['place']->id, $checkIn->sleepingPlace->id);
        $this->assertNotNull($checkIn->instruction);
        $this->assertGreaterThanOrEqual(14, $checkIn->steps()->count());
    }

    public function test_instruction_snapshot_hides_and_logs_sensitive_access_when_allowed(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $instructions = app(BookingCheckInInstructionService::class);

        $checkIn->instruction->forceFill(['visible_from' => now()->addHours(2)])->save();

        $hidden = $instructions->getVisibleInstructions($listing['guest'], $checkIn->refresh());

        $this->assertFalse($hidden['exact_address_visible']);
        $this->assertNull($hidden['exact_address']);
        $this->assertFalse($hidden['access_codes_visible']);
        $this->assertNull($hidden['door_code']);

        $checkIn->instruction->forceFill(['visible_from' => now()->subMinute()])->save();

        $visible = $instructions->getVisibleInstructions($listing['guest'], $checkIn->refresh());

        $this->assertTrue($visible['exact_address_visible']);
        $this->assertSame('Peace Street 12, apt. 7', $visible['exact_address']);
        $this->assertTrue($visible['access_codes_visible']);
        $this->assertSame('2468', $visible['door_code']);

        $this->assertTrue(app(BookingCheckInAccessDisclosureService::class)->hasSeen($listing['guest'], $checkIn, 'exact_address'));
        $this->assertTrue(app(BookingCheckInAccessDisclosureService::class)->hasSeen($listing['guest'], $checkIn, 'door_code'));
        $this->assertDatabaseHas('booking_check_in_access_disclosures', [
            'booking_check_in_id' => $checkIn->id,
            'booking_id' => $booking->id,
            'guest_user_id' => $listing['guest']->id,
            'disclosure_type' => 'exact_address',
        ]);

        $rawCode = $checkIn->instruction()->firstOrFail()->getRawOriginal('door_code_encrypted');
        $this->assertNotSame('2468', $rawCode);
    }

    public function test_guest_and_host_confirmation_updates_check_in_steps_logs_and_booking_status(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, ['status' => BookingStatus::ReadyForCheckInCore->value]);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $service = app(BookingCheckInService::class);

        $service->markGuestOnTheWay($listing['guest'], $checkIn);
        $arrived = $service->markGuestArrived($listing['guest'], $checkIn->refresh());
        $guestConfirmed = $service->confirmByGuest($listing['guest'], $arrived);

        $this->assertSame('guest_checked_in', $booking->fresh()->status->value);
        $this->assertNotNull($guestConfirmed->guest_confirmed_at);
        $this->assertDatabaseHas('booking_check_in_steps', [
            'booking_check_in_id' => $checkIn->id,
            'step_key' => 'guest_confirmed',
            'status' => 'completed',
        ]);

        $hostConfirmed = $service->confirmByHost($listing['host'], $guestConfirmed);

        $this->assertSame('checked_in', $hostConfirmed->status);
        $this->assertSame('stay_in_progress', $booking->fresh()->status->value);
        $this->assertDatabaseHas('booking_check_in_status_logs', [
            'booking_check_in_id' => $checkIn->id,
            'booking_id' => $booking->id,
            'new_status' => 'checked_in',
        ]);
        $this->assertGreaterThanOrEqual(4, BookingCheckInStatusLog::query()->where('booking_check_in_id', $checkIn->id)->count());
    }

    public function test_problem_media_inventory_and_privacy_services_work(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, ['status' => BookingStatus::ReadyForCheckInCore->value]);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);

        app(BookingCheckInInventoryService::class)->issueKeys($checkIn, ['door_code']);
        app(BookingCheckInInventoryService::class)->issueBedding($checkIn->refresh());
        app(BookingCheckInInventoryService::class)->issueTowel($checkIn->refresh());
        app(BookingCheckInInventoryService::class)->assignLocker($checkIn->refresh(), ['locker' => 'A-14']);

        $media = app(BookingCheckInMediaService::class)->recordMedia($listing['guest'], $checkIn->refresh(), [
            'media_type' => 'photo',
            'media_role' => 'before_check_in_sleeping_place',
            'path' => 'check-ins/before-place.jpg',
            'visibility' => 'guest_and_host',
        ]);

        $problem = app(BookingCheckInProblemService::class)->reportProblem($listing['guest'], $checkIn->refresh(), [
            'problem_type' => 'host_not_answering',
            'severity' => 'urgent',
            'description' => 'The host is not answering at the entrance.',
            'guest_wants_help' => true,
        ]);

        $this->assertInstanceOf(BookingCheckInMedia::class, $media);
        $this->assertInstanceOf(BookingCheckInProblem::class, $problem);
        $this->assertNotNull($problem->source_created_host_unresponsive_case_id);
        $this->assertSame('host_unresponsive', $booking->fresh()->status->value);
        $this->assertTrue($checkIn->fresh()->keys_handed_over);
        $this->assertTrue($checkIn->fresh()->bedding_issued);
        $this->assertTrue($checkIn->fresh()->towel_issued);
        $this->assertTrue($checkIn->fresh()->locker_assigned);

        $privacy = app(BookingCheckInPrivacyService::class);

        $this->assertTrue($privacy->canGuestView($listing['guest'], $checkIn->refresh()));
        $this->assertFalse($privacy->canGuestView(User::factory()->create(), $checkIn->refresh()));
        $this->assertTrue($privacy->canHostView($listing['host'], $checkIn->refresh()));
        $this->assertFalse($privacy->canHostView(User::factory()->create(['is_host' => true]), $checkIn->refresh()));
        $this->assertTrue($privacy->canViewMedia($listing['guest'], $media));

        $internalMedia = BookingCheckInMedia::factory()->for($checkIn)->for($booking)->for($listing['host'], 'uploadedBy')->create([
            'visibility' => 'internal',
        ]);

        $this->assertFalse($privacy->canViewMedia($listing['guest'], $internalMedia));
    }

    public function test_required_check_in_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        app(BookingCheckInService::class)->createForBooking($booking);

        foreach ($this->guestComponents() as $component) {
            Livewire::actingAs($listing['guest'])
                ->test($component, ['booking' => $booking])
                ->assertSee(__('check_in.title', [], 'en'));
        }

        Livewire::actingAs($listing['host'])
            ->test(HostCheckInDetailsSheet::class, ['booking' => $booking])
            ->assertSee(__('check_in.host_title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($listing['guest'])
            ->test(GuestCheckInPage::class, ['booking' => $booking])
            ->assertSee(__('check_in.title', [], 'ru'));

        Livewire::actingAs($listing['host'])
            ->test(HostCheckInDetailsSheet::class, ['booking' => $booking])
            ->assertSee(__('check_in.host_title', [], 'ru'));
    }

    public function test_step_service_can_complete_required_steps(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);

        $step = app(BookingCheckInStepService::class)->markStepCompleted($checkIn, 'rules_explained', $listing['host']);

        $this->assertSame('completed', $step->status);
        $this->assertTrue($checkIn->fresh()->rules_explained);
        $this->assertFalse(app(BookingCheckInStepService::class)->getRequiredIncompleteSteps($checkIn->fresh())->contains('step_key', 'rules_explained'));
    }

    /**
     * @return array{guest:User, host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(): array
    {
        $guest = User::factory()->create(['name' => 'Point Ten Guest']);
        $host = User::factory()->create([
            'is_host' => true,
            'name' => 'Point Ten Host',
            'phone' => '+37060000000',
            'phone_verified' => true,
        ]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Check In Property',
                'city' => 'Vilnius',
                'district' => 'Center',
                'address_line_1' => 'Peace Street',
                'house_number' => '12',
                'apartment_number' => '7',
                'show_exact_address_after_confirmation' => true,
                'show_exact_address_after_payment' => true,
            ]);
        $room = Room::factory()->for($property)->create([
            'title' => 'Room 1',
            'room_number' => '1',
        ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'check_in_instruction' => 'Use the main entrance.',
            'key_pickup_instruction' => 'Use the key safe near the entrance.',
            'night_entry_instruction' => 'Enter quietly after dark.',
            'door_code_encrypted' => '2468',
            'intercom_code_encrypted' => '1357',
            'key_safe_code_encrypted' => '8642',
            'show_access_details_after_booking' => true,
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
     */
    private function booking(array $listing, array $overrides = []): Booking
    {
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
                'status' => BookingStatus::Confirmed->value,
                'payment_status' => PaymentStatus::Paid->value,
                'check_in_date' => '2026-06-21',
                'check_out_date' => '2026-06-24',
                'check_in' => '2026-06-21',
                'check_out' => '2026-06-24',
                'check_in_time' => '15:00',
                'check_out_time' => '10:00',
                'arrival_time' => '16:00',
                'nights_count' => 3,
                'nights' => 3,
                'total_amount' => 126,
                'total_payable' => 126,
                'total' => 126,
                'currency' => 'EUR',
                'payment_paid_at' => now(),
                'check_in_instructions' => "Use the main entrance.\nFind Room 1.\nBed 2 is near the window.",
            ], $overrides));
    }

    /**
     * @return list<class-string<Component>>
     */
    private function guestComponents(): array
    {
        return [
            GuestCheckInPage::class,
            CheckInInstructionCard::class,
            CheckInAccessDetailsCard::class,
            GuestArrivalButtons::class,
            CheckInProblemSheet::class,
            CheckInMediaUploader::class,
            CheckInStepsList::class,
        ];
    }
}
