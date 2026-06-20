<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\CheckIn\CheckInProblemButton;
use App\Livewire\Bookings\CheckIn\CheckInProblemPanel;
use App\Livewire\Bookings\CheckIn\CheckInProblemReportSheet;
use App\Livewire\Bookings\CheckIn\CheckInStatusBadge;
use App\Livewire\Bookings\CheckIn\GuestArrivalButton;
use App\Livewire\Bookings\CheckIn\GuestCheckInConfirmButton;
use App\Livewire\Bookings\CheckIn\GuestCheckInInstructions;
use App\Livewire\Bookings\CheckIn\GuestCheckInPage;
use App\Livewire\Bookings\CheckIn\HostCheckInChecklist;
use App\Livewire\Bookings\CheckIn\HostCheckInConfirmButton;
use App\Livewire\Bookings\CheckIn\HostCheckInPanel;
use App\Models\Booking;
use App\Models\BookingCheckInAlert;
use App\Models\BookingCheckInChecklistItem;
use App\Models\BookingCheckInProblemReport;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CheckIn\BookingCheckInChecklistService;
use App\Services\CheckIn\BookingCheckInConfirmationService;
use App\Services\CheckIn\BookingCheckInInstructionService;
use App\Services\CheckIn\BookingCheckInProblemService;
use App\Services\CheckIn\BookingCheckInReminderService;
use App\Services\CheckIn\BookingCheckInService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCheckInFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-06-20 10:00:00');
    }

    public function test_check_in_tables_models_relationships_indexes_and_default_checklist_exist(): void
    {
        $this->assertTrue(Schema::hasTable('booking_check_ins'));
        $this->assertTrue(Schema::hasTable('booking_check_in_checklist_items'));
        $this->assertTrue(Schema::hasTable('booking_check_in_problem_reports'));
        $this->assertTrue(Schema::hasTable('booking_check_in_alerts'));
        $this->assertTrue(Schema::hasIndex('booking_check_ins', ['booking_id']));
        $this->assertTrue(Schema::hasIndex('booking_check_ins', ['guest_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_checklist_items', ['booking_check_in_id', 'item_key']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_problem_reports', ['host_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_check_in_alerts', ['booking_check_in_id', 'status']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $items = app(BookingCheckInChecklistService::class)->createDefaultChecklist($checkIn);
        $report = BookingCheckInProblemReport::factory()
            ->for($checkIn)
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->create();
        $alert = BookingCheckInAlert::factory()
            ->for($checkIn)
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->create();

        $this->assertSame($booking->id, $checkIn->booking->id);
        $this->assertSame($listing['guest']->id, $checkIn->guest->id);
        $this->assertSame($listing['host']->id, $checkIn->host->id);
        $this->assertSame($listing['place']->id, $checkIn->sleepingPlace->id);
        $this->assertGreaterThanOrEqual(13, $items->count());
        $this->assertContainsOnlyInstancesOf(BookingCheckInChecklistItem::class, $items);
        $this->assertSame($checkIn->id, $report->checkIn->id);
        $this->assertSame($checkIn->id, $alert->checkIn->id);
    }

    public function test_guest_instruction_privacy_arrival_problem_alert_and_resolution_flow(): void
    {
        $listing = $this->listing([
            'show_exact_address_after_confirmation' => false,
            'show_exact_address_after_payment' => false,
        ]);
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $instructions = app(BookingCheckInInstructionService::class);

        $this->assertFalse($instructions->canShowExactAddress($listing['guest'], $booking));
        $this->assertNull($instructions->getGuestInstructions($listing['guest'], $booking)['exact_address']);

        $listing['property']->forceFill(['show_exact_address_after_confirmation' => true])->save();

        $visible = $instructions->getGuestInstructions($listing['guest'], $booking->refresh());
        $this->assertTrue($visible['exact_address_visible']);
        $this->assertStringContainsString('Peace Street', $visible['exact_address']);

        $arrived = app(BookingCheckInService::class)->markGuestArrived($listing['guest'], $checkIn);

        $this->assertSame('guest_arrived', $arrived->status);
        $this->assertNotNull($arrived->actual_arrival_at);

        $report = app(BookingCheckInProblemService::class)->reportProblem($listing['guest'], $arrived, [
            'problem_type' => 'code_not_working',
            'severity' => 'high',
            'description' => 'The door code does not open the building entrance.',
            'photo_paths' => ['check-in/door.jpg'],
        ]);

        $this->assertSame('open', $report->status);
        $this->assertDatabaseHas('booking_check_in_alerts', [
            'booking_check_in_id' => $arrived->id,
            'booking_id' => $booking->id,
            'alert_type' => 'check_in_problem',
            'severity' => 'high',
            'status' => 'notified_host',
        ]);
        $this->assertDatabaseHas('booking_check_ins', [
            'id' => $arrived->id,
            'has_problem' => true,
            'status' => 'check_in_problem',
        ]);

        $this->expectException(AuthorizationException::class);
        app(BookingCheckInProblemService::class)->markResolved(User::factory()->create(['is_host' => true]), $report);
    }

    public function test_guest_and_host_confirmations_start_stay_and_update_current_occupants_and_calendar(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, [
            'check_in_date' => '2026-06-20',
            'check_out_date' => '2026-06-24',
        ]);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $confirmation = app(BookingCheckInConfirmationService::class);

        $guestConfirmed = $confirmation->guestConfirm($listing['guest'], $checkIn);
        $checkedIn = $confirmation->hostConfirm($listing['host'], $guestConfirmed);

        $booking->refresh();

        $this->assertSame('checked_in', $checkedIn->status);
        $this->assertTrue($booking->status === BookingStatus::InProgress);
        $this->assertNotNull($booking->guest_checked_in_at);
        $this->assertNotNull($booking->host_confirmed_checkin_at);
        $this->assertNotNull($booking->checked_in_at);
        $this->assertDatabaseHas('host_current_stay_snapshots', [
            'booking_id' => $booking->id,
            'user_id' => $listing['host']->id,
            'stay_status' => 'living_now',
            'check_in_status' => 'checked_in',
        ]);
        $this->assertDatabaseHas('host_calendar_events', [
            'booking_id' => $booking->id,
            'event_type' => 'check_in',
            'check_in_status' => 'checked_in',
        ]);
    }

    public function test_open_severe_problem_blocks_automatic_checked_in_until_resolved(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);
        $report = app(BookingCheckInProblemService::class)->reportProblem($listing['guest'], $checkIn, [
            'problem_type' => 'unsafe',
            'severity' => 'high',
            'description' => 'The room feels unsafe and the listed place is not ready.',
        ]);

        $confirmation = app(BookingCheckInConfirmationService::class);
        $confirmation->guestConfirm($listing['guest'], $checkIn->refresh());
        $blocked = $confirmation->hostConfirm($listing['host'], $checkIn->refresh());

        $this->assertSame('waiting_for_resolution', $blocked->status);
        $this->assertTrue($booking->fresh()->status === BookingStatus::Confirmed);
        $this->assertDatabaseMissing('host_current_stay_snapshots', ['booking_id' => $booking->id]);

        app(BookingCheckInProblemService::class)->markResolved($listing['host'], $report);
        $checkedIn = app(BookingCheckInService::class)->startStayIfReady($checkIn->refresh());

        $this->assertSame('checked_in', $checkedIn->status);
        $this->assertTrue($booking->fresh()->status === BookingStatus::InProgress);
    }

    public function test_due_reminders_and_authorization_are_safe_without_cron(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, [
            'check_in_date' => '2026-06-21',
            'check_out_date' => '2026-06-24',
        ]);
        $checkIn = app(BookingCheckInService::class)->createForBooking($booking);

        $sentForGuest = app(BookingCheckInReminderService::class)->sendDueReminders($listing['guest']);
        $sentForHost = app(BookingCheckInReminderService::class)->sendDueReminders($listing['host']);

        $this->assertSame(1, $sentForGuest);
        $this->assertSame(0, $sentForHost);
        $this->assertSame('reminder_sent', $checkIn->fresh()->status);
        $this->assertNotNull($checkIn->fresh()->last_reminder_sent_at);

        $this->expectException(ValidationException::class);
        app(BookingCheckInService::class)->getForGuest(User::factory()->create(), $booking);
    }

    public function test_check_in_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        app(BookingCheckInService::class)->createForBooking($booking);

        foreach ($this->componentClasses() as $component) {
            Livewire::actingAs($listing['guest'])
                ->test($component, ['booking' => $booking])
                ->assertSee(__('check_in.title'));
        }

        app()->setLocale('ru');

        Livewire::actingAs($listing['guest'])
            ->test(GuestCheckInPage::class, ['booking' => $booking])
            ->assertSee(__('check_in.title', [], 'ru'))
            ->assertSee(__('check_in.actions.i_arrived', [], 'ru'));

        $this->actingAs($listing['guest'])
            ->get(route('guest.bookings.check-in', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSee(__('check_in.title', [], 'en'));
    }

    /**
     * @return array{guest:User, host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(array $propertyOverrides = []): array
    {
        $guest = User::factory()->create(['name' => 'Arriving Guest']);
        $host = User::factory()->create([
            'is_host' => true,
            'name' => 'Check In Host',
            'phone' => '+37060000000',
        ]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create(array_merge([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Check In Home',
                'city' => 'Vilnius',
                'district' => 'Center',
                'address_line_1' => 'Peace Street',
                'house_number' => '12',
                'apartment_number' => '7',
                'show_exact_address_after_confirmation' => true,
                'show_exact_address_after_payment' => true,
            ], $propertyOverrides));
        $room = Room::factory()->for($property)->create([
            'title' => 'Room 2',
            'room_number' => '2',
        ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'display_name' => 'Place 4',
                'place_number' => '4',
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
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
                'check_in_date' => '2026-06-20',
                'check_out_date' => '2026-06-24',
                'check_in' => '2026-06-20',
                'check_out' => '2026-06-24',
                'check_in_time' => '15:00',
                'check_out_time' => '10:00',
                'arrival_time' => '16:00',
                'nights_count' => 4,
                'nights' => 4,
                'total_amount' => 100,
                'total' => 100,
                'currency' => 'EUR',
                'payment_paid_at' => now(),
                'check_in_instructions' => "Peace Street 12, apartment 7\n\nUse the quiet entrance.",
            ], $overrides));
    }

    /**
     * @return list<class-string<Component>>
     */
    private function componentClasses(): array
    {
        return [
            GuestCheckInPage::class,
            GuestCheckInInstructions::class,
            GuestArrivalButton::class,
            GuestCheckInConfirmButton::class,
            CheckInProblemButton::class,
            CheckInProblemReportSheet::class,
            HostCheckInPanel::class,
            HostCheckInChecklist::class,
            HostCheckInConfirmButton::class,
            CheckInStatusBadge::class,
            CheckInProblemPanel::class,
        ];
    }
}
