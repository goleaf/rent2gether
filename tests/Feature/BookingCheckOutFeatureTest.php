<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\CheckOut\CheckOutIssueReportSheet;
use App\Livewire\Bookings\CheckOut\CheckOutStatusBadge;
use App\Livewire\Bookings\CheckOut\DepositDecisionPanel;
use App\Livewire\Bookings\CheckOut\ForgottenItemsPanel;
use App\Livewire\Bookings\CheckOut\GuestCheckOutChecklist;
use App\Livewire\Bookings\CheckOut\GuestCheckOutConfirmButton;
use App\Livewire\Bookings\CheckOut\GuestCheckOutPage;
use App\Livewire\Bookings\CheckOut\HostCheckOutChecklist;
use App\Livewire\Bookings\CheckOut\HostCheckOutConfirmButton;
use App\Livewire\Bookings\CheckOut\HostCheckOutPanel;
use App\Livewire\Bookings\CheckOut\HostInspectionPanel;
use App\Livewire\Bookings\CheckOut\ReviewRequestPanel;
use App\Models\Booking;
use App\Models\BookingCheckOutChecklistItem;
use App\Models\BookingCheckOutIssueReport;
use App\Models\BookingDepositDecision;
use App\Models\BookingForgottenItem;
use App\Models\BookingReviewRequest;
use App\Models\HostInspectionTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use App\Services\CheckOut\BookingCheckOutCalendarService;
use App\Services\CheckOut\BookingCheckOutChecklistService;
use App\Services\CheckOut\BookingCheckOutConfirmationService;
use App\Services\CheckOut\BookingCheckOutInspectionService;
use App\Services\CheckOut\BookingCheckOutIssueService;
use App\Services\CheckOut\BookingCheckOutReminderService;
use App\Services\CheckOut\BookingCheckOutService;
use App\Services\CheckOut\BookingDepositDecisionService;
use App\Services\CheckOut\BookingForgottenItemService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCheckOutFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-06-20 10:00:00');
    }

    public function test_checkout_tables_models_relationships_indexes_and_default_checklist_exist(): void
    {
        $this->assertTrue(Schema::hasTable('booking_check_outs'));
        $this->assertTrue(Schema::hasTable('booking_check_out_checklist_items'));
        $this->assertTrue(Schema::hasTable('booking_check_out_issue_reports'));
        $this->assertTrue(Schema::hasTable('booking_forgotten_items'));
        $this->assertTrue(Schema::hasTable('booking_deposit_decisions'));
        $this->assertTrue(Schema::hasTable('host_inspection_tasks'));
        $this->assertTrue(Schema::hasTable('booking_review_requests'));
        $this->assertTrue(Schema::hasIndex('booking_check_outs', ['booking_id']));
        $this->assertTrue(Schema::hasIndex('booking_check_outs', ['guest_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_check_out_checklist_items', ['booking_check_out_id', 'item_key']));
        $this->assertTrue(Schema::hasIndex('booking_check_out_issue_reports', ['host_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_deposit_decisions', ['booking_id']));
        $this->assertTrue(Schema::hasIndex('host_inspection_tasks', ['booking_check_out_id']));
        $this->assertTrue(Schema::hasIndex('booking_review_requests', ['reviewer_user_id', 'status']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        $items = app(BookingCheckOutChecklistService::class)->createDefaultChecklist($checkOut);
        $issue = BookingCheckOutIssueReport::factory()
            ->for($checkOut)
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->create();
        $forgotten = BookingForgottenItem::factory()
            ->for($checkOut)
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->create();
        $decision = BookingDepositDecision::factory()
            ->for($checkOut)
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->create();
        $inspection = HostInspectionTask::factory()
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->for($booking)
            ->for($checkOut)
            ->create();
        $request = BookingReviewRequest::factory()
            ->for($booking)
            ->for($listing['guest'], 'reviewer')
            ->for($listing['host'], 'reviewee')
            ->create();

        $this->assertSame($booking->id, $checkOut->booking->id);
        $this->assertSame($listing['guest']->id, $checkOut->guest->id);
        $this->assertSame($listing['host']->id, $checkOut->host->id);
        $this->assertGreaterThanOrEqual(13, $items->count());
        $this->assertContainsOnlyInstancesOf(BookingCheckOutChecklistItem::class, $items);
        $this->assertSame($checkOut->id, $issue->checkOut->id);
        $this->assertSame($checkOut->id, $forgotten->checkOut->id);
        $this->assertSame($checkOut->id, $decision->checkOut->id);
        $this->assertSame($checkOut->id, $inspection->checkOut->id);
        $this->assertSame($booking->id, $request->booking->id);
    }

    public function test_checkout_access_reminders_and_extension_offer_are_safe(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, [
            'check_out_date' => '2026-06-21',
            'check_out' => '2026-06-21',
        ]);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);

        $this->assertSame($checkOut->id, app(BookingCheckOutService::class)->getForGuest($listing['guest'], $booking)->id);
        $this->assertSame($checkOut->id, app(BookingCheckOutService::class)->getForHost($listing['host'], $booking)->id);
        $this->assertSame(1, app(BookingCheckOutReminderService::class)->sendDueReminders($listing['guest']));
        $this->assertSame(0, app(BookingCheckOutReminderService::class)->sendDueReminders($listing['host']));
        $this->assertSame('reminder_sent', $checkOut->fresh()->status);
        $this->assertTrue(app(BookingCheckOutService::class)->canOfferExtension($checkOut->fresh()));

        SleepingPlaceCalendarDay::factory()
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'date' => '2026-06-22',
                'status' => 'booked',
                'booking_id' => Booking::factory()->create()->id,
            ]);

        $this->assertFalse(app(BookingCheckOutService::class)->canOfferExtension($checkOut->fresh()));

        $this->expectException(ValidationException::class);
        app(BookingCheckOutService::class)->getForGuest(User::factory()->create(), $booking);
    }

    public function test_guest_and_host_confirmations_create_tasks_deposit_review_requests_and_update_snapshots(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $booking->depositRecords()->create([
            'amount' => 30,
            'currency' => 'EUR',
            'status' => 'held',
            'held_at' => now(),
            'withheld_amount' => 0,
        ]);

        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        $guestCheckedOut = app(BookingCheckOutService::class)->markGuestCheckedOut($listing['guest'], $checkOut);

        $this->assertSame('host_inspection_pending', $guestCheckedOut->status);
        $this->assertNotNull($guestCheckedOut->actual_check_out_at);
        $this->assertDatabaseHas('host_inspection_tasks', [
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $booking->id,
            'status' => 'planned',
        ]);
        $this->assertDatabaseHas('host_cleaning_tasks', [
            'booking_id' => $booking->id,
            'reason' => 'after_checkout',
        ]);

        app(BookingCheckOutInspectionService::class)->completeInspection($listing['host'], $guestCheckedOut->refresh(), [
            'room_checked' => true,
            'sleeping_place_checked' => true,
            'sleeping_place_free' => true,
            'has_damage' => false,
            'has_extra_dirty' => false,
        ]);
        $completed = app(BookingCheckOutConfirmationService::class)->hostConfirm($listing['host'], $guestCheckedOut->refresh());

        $booking->refresh();

        $this->assertSame('completed', $completed->status);
        $this->assertTrue($booking->status === BookingStatus::CheckedOut);
        $this->assertDatabaseHas('booking_deposit_decisions', [
            'booking_id' => $booking->id,
            'decision' => 'return_full',
            'status' => 'return_pending',
        ]);
        $this->assertSame(2, BookingReviewRequest::query()->where('booking_id', $booking->id)->count());
        $this->assertDatabaseHas('host_current_stay_snapshots', [
            'booking_id' => $booking->id,
            'stay_status' => 'checked_out',
            'check_in_status' => 'checked_out',
        ]);
        $this->assertDatabaseHas('host_calendar_events', [
            'booking_id' => $booking->id,
            'event_type' => 'check_out',
            'event_status' => 'checked_out',
        ]);
    }

    public function test_unresolved_issue_blocks_completion_and_deposit_deduction_can_be_disputed(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        app(BookingCheckOutService::class)->markGuestCheckedOut($listing['guest'], $checkOut);

        $issue = app(BookingCheckOutIssueService::class)->reportIssue($listing['host'], $checkOut->refresh(), [
            'issue_type' => 'damage',
            'severity' => 'high',
            'description' => 'The locker lock is broken.',
            'deposit_related' => true,
            'repair_needed' => true,
            'photo_paths' => ['checkout/locker.jpg'],
        ]);
        $decision = app(BookingDepositDecisionService::class)->requestPartialDeduction($listing['host'], $checkOut->refresh(), 12.50, 'Locker lock replacement');
        $disputed = app(BookingDepositDecisionService::class)->guestDispute($listing['guest'], $decision, 'The lock was already loose.');
        $blocked = app(BookingCheckOutConfirmationService::class)->hostConfirm($listing['host'], $checkOut->refresh());

        $this->assertSame('guest_disputed', $disputed->status);
        $this->assertSame('problem_reported', $blocked->status);
        $this->assertTrue($booking->fresh()->status === BookingStatus::InProgress);

        app(BookingCheckOutIssueService::class)->markResolved($listing['host'], $issue);
        app(BookingDepositDecisionService::class)->resolveDecision($disputed->refresh());
        app(BookingCheckOutInspectionService::class)->completeInspection($listing['host'], $checkOut->refresh(), [
            'room_checked' => true,
            'sleeping_place_checked' => true,
            'sleeping_place_free' => true,
            'has_damage' => true,
            'has_extra_dirty' => false,
        ]);

        $completed = app(BookingCheckOutConfirmationService::class)->hostConfirm($listing['host'], $checkOut->refresh());

        $this->assertSame('completed', $completed->status);
        $this->assertDatabaseHas('booking_deposit_decisions', [
            'id' => $decision->id,
            'status' => 'resolved',
            'deduction_amount' => 12.50,
        ]);
    }

    public function test_forgotten_items_and_calendar_release_respect_history_cleaning_and_repair_blocks(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        $item = app(BookingForgottenItemService::class)->createForgottenItem($listing['host'], $checkOut, [
            'item_name' => 'Blue scarf',
            'description' => 'Left on the lower bed.',
            'storage_location' => 'Host shelf',
            'keep_until' => '2026-07-20',
        ]);

        app(BookingForgottenItemService::class)->notifyGuest($item);

        SleepingPlaceCalendarDay::factory()->for($listing['place'], 'sleepingPlace')->create([
            'date' => '2026-06-23',
            'status' => 'booked',
            'booking_id' => $booking->id,
        ]);
        SleepingPlaceCalendarDay::factory()->for($listing['place'], 'sleepingPlace')->create([
            'date' => '2026-06-24',
            'status' => 'cleaning',
            'reason' => 'after_checkout',
        ]);
        SleepingPlaceCalendarDay::factory()->for($listing['place'], 'sleepingPlace')->create([
            'date' => '2026-06-25',
            'status' => 'repair',
            'reason' => 'repair',
        ]);

        app(BookingCheckOutCalendarService::class)->syncCalendarAfterCheckout($checkOut->refresh());

        $this->assertSame('guest_notified', $item->fresh()->status);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-06-23',
            'status' => 'booked',
            'booking_id' => $booking->id,
        ]);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-06-24',
            'status' => 'cleaning',
        ]);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-06-25',
            'status' => 'repair',
        ]);

        $this->expectException(AuthorizationException::class);
        app(BookingForgottenItemService::class)->markPickedUp(User::factory()->create(['is_host' => true]), $item);
    }

    public function test_checkout_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        app(BookingCheckOutService::class)->createForBooking($booking);

        foreach ($this->componentClasses() as $component) {
            Livewire::actingAs($listing['guest'])
                ->test($component, ['booking' => $booking])
                ->assertSee(__('check_out.title'));
        }

        app()->setLocale('ru');

        Livewire::actingAs($listing['guest'])
            ->test(GuestCheckOutPage::class, ['booking' => $booking])
            ->assertSee(__('check_out.title', [], 'ru'))
            ->assertSee(__('check_out.actions.i_checked_out', [], 'ru'));

        $this->actingAs($listing['guest'])
            ->get(route('guest.bookings.check-out', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSee(__('check_out.title', [], 'en'));
    }

    public function test_checkout_issue_report_sheet_keeps_photo_paths_out_of_public_state(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        app(BookingCheckOutService::class)->createForBooking($booking);

        $component = Livewire::actingAs($listing['host'])
            ->test(CheckOutIssueReportSheet::class, ['booking' => $booking])
            ->set('issueType', 'damage')
            ->set('severity', 'high')
            ->set('description', 'The locker door is damaged after checkout.')
            ->assertSee(__('check_out.title'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('bookingId', $encodedSnapshot);
        $this->assertStringContainsString('checkOutId', $encodedSnapshot);
        $this->assertStringNotContainsString('photoPaths', $encodedSnapshot);
    }

    /**
     * @return array{guest:User, host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(): array
    {
        $guest = User::factory()->create(['name' => 'Leaving Guest']);
        $host = User::factory()->create(['is_host' => true, 'name' => 'Checkout Host']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Checkout Home',
                'city' => 'Vilnius',
                'district' => 'Center',
            ]);
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
                'status' => BookingStatus::InProgress,
                'payment_status' => PaymentStatus::Paid,
                'check_in_date' => '2026-06-16',
                'check_out_date' => '2026-06-24',
                'check_in' => '2026-06-16',
                'check_out' => '2026-06-24',
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'nights_count' => 8,
                'nights' => 8,
                'total_amount' => 180,
                'total' => 180,
                'deposit_amount' => 30,
                'deposit' => 30,
                'currency' => 'EUR',
                'guest_checked_in_at' => now()->subDays(4),
                'host_confirmed_checkin_at' => now()->subDays(4),
                'checked_in_at' => now()->subDays(4),
            ], $overrides));
    }

    /**
     * @return list<class-string<Component>>
     */
    private function componentClasses(): array
    {
        return [
            GuestCheckOutPage::class,
            GuestCheckOutChecklist::class,
            GuestCheckOutConfirmButton::class,
            HostCheckOutPanel::class,
            HostCheckOutChecklist::class,
            HostInspectionPanel::class,
            HostCheckOutConfirmButton::class,
            CheckOutStatusBadge::class,
            CheckOutIssueReportSheet::class,
            ForgottenItemsPanel::class,
            DepositDecisionPanel::class,
            ReviewRequestPanel::class,
        ];
    }
}
