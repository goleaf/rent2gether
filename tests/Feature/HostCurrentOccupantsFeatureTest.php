<?php

namespace Tests\Feature;

use App\Enums\BookingExtensionStatus;
use App\Enums\BookingStatus;
use App\Enums\ComplaintStatus;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Host\Occupants\CurrentOccupantCard;
use App\Livewire\Host\Occupants\CurrentOccupantDetailsSheet;
use App\Livewire\Host\Occupants\CurrentOccupantsFilters;
use App\Livewire\Host\Occupants\CurrentOccupantsPage;
use App\Livewire\Host\Occupants\CurrentOccupantsSummary;
use App\Livewire\Host\Occupants\OccupantCheckoutPanel;
use App\Livewire\Host\Occupants\OccupantExtensionPanel;
use App\Livewire\Host\Occupants\OccupantFlagsPanel;
use App\Livewire\Host\Occupants\OccupantNoteSheet;
use App\Livewire\Host\Occupants\OccupantPaymentStatusBadge;
use App\Livewire\Host\Occupants\OccupantQuickActions;
use App\Livewire\Host\Occupants\OccupantStayStatusBadge;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\Complaint;
use App\Models\HostCleaningTask;
use App\Models\HostCurrentStaySnapshot;
use App\Models\HostGuestStayFlag;
use App\Models\HostGuestStayNote;
use App\Models\PaymentRecord;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostOccupants\Data\HostOccupantFilters;
use App\Services\HostOccupants\HostCurrentOccupantsService;
use App\Services\HostOccupants\HostCurrentStaySnapshotService;
use App\Services\HostOccupants\HostGuestStayFlagService;
use App\Services\HostOccupants\HostGuestStayNoteService;
use App\Services\HostOccupants\HostOccupantActionService;
use App\Services\HostOccupants\HostOccupantPrivacyService;
use App\Services\HostOccupants\HostOccupantSummaryService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class HostCurrentOccupantsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-06-20 10:00:00');
    }

    public function test_current_occupant_tables_models_relationships_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('host_current_stay_snapshots'));
        $this->assertTrue(Schema::hasTable('host_guest_stay_notes'));
        $this->assertTrue(Schema::hasTable('host_guest_stay_flags'));
        $this->assertTrue(Schema::hasIndex('host_current_stay_snapshots', ['user_id', 'stay_status']));
        $this->assertTrue(Schema::hasIndex('host_current_stay_snapshots', ['user_id', 'check_out_date']));
        $this->assertTrue(Schema::hasIndex('host_current_stay_snapshots', ['user_id', 'check_out_date', 'room_label', 'sleeping_place_label', 'id']));
        $this->assertTrue(Schema::hasIndex('host_current_stay_snapshots', ['booking_id']));
        $this->assertTrue(Schema::hasIndex('host_guest_stay_notes', ['user_id', 'guest_user_id']));
        $this->assertTrue(Schema::hasIndex('host_guest_stay_flags', ['booking_id', 'status']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $snapshot = HostCurrentStaySnapshot::factory()
            ->for($listing['host'], 'host')
            ->for($booking->guest, 'guest')
            ->for($booking)
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create();
        $note = HostGuestStayNote::factory()
            ->for($listing['host'], 'host')
            ->for($booking->guest, 'guest')
            ->for($booking)
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create();
        $flag = HostGuestStayFlag::factory()
            ->for($listing['host'], 'host')
            ->for($booking->guest, 'guest')
            ->for($booking)
            ->create();

        $this->assertSame($listing['host']->id, $snapshot->host->id);
        $this->assertSame($booking->guest_user_id, $snapshot->guest->id);
        $this->assertSame($listing['place']->id, $snapshot->sleepingPlace->id);
        $this->assertSame($booking->id, $note->booking->id);
        $this->assertSame($booking->id, $flag->booking->id);
    }

    public function test_booking_refresh_creates_snapshot_and_tracks_payment_check_in_and_checkout(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-25',
            'nights_count' => 15,
            'payment_status' => PaymentStatus::PartiallyPaid,
            'status' => BookingStatus::Confirmed,
            'total_amount' => 250,
            'deposit_amount' => 40,
            'cleaning_fee_amount' => 12,
            'guest_message' => 'Needs a quiet corner.',
        ]);
        PaymentRecord::factory()
            ->for($booking)
            ->for($booking->guest, 'payer')
            ->create(['amount' => 100, 'status' => PaymentRecordStatus::Paid]);

        $snapshot = app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking);

        $this->assertSame(5, $snapshot->nights_left);
        $this->assertSame('partial', $snapshot->payment_status);
        $this->assertSame('living_now', $snapshot->stay_status);
        $this->assertSame('Needs a quiet corner.', $snapshot->special_requests_summary);
        $this->assertDatabaseHas('host_current_stay_snapshots', [
            'booking_id' => $booking->id,
            'user_id' => $listing['host']->id,
            'guest_display_name' => 'Current Guest',
            'room_label' => 'Room A',
            'sleeping_place_label' => 'Place 1',
            'nights_left' => 5,
            'booking_total_amount' => 250,
            'paid_amount' => 100,
            'remaining_amount' => 150,
        ]);

        $booking->forceFill([
            'status' => BookingStatus::CheckedIn,
            'payment_status' => PaymentStatus::Paid,
            'checked_in_at' => now(),
        ])->save();
        $checkedIn = app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking->refresh());

        $this->assertSame('living_now', $checkedIn->stay_status);
        $this->assertSame('checked_in', $checkedIn->check_in_status);
        $this->assertSame('paid', $checkedIn->payment_status);

        $booking->forceFill([
            'status' => BookingStatus::CheckedOut,
            'checked_out_at' => now(),
        ])->save();
        $checkedOut = app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking->refresh());

        $this->assertSame('checked_out', $checkedOut->stay_status);
        $this->assertSame('checked_out', $checkedOut->check_in_status);
    }

    public function test_flags_refresh_for_extension_complaint_payment_checkout_and_cleaning(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing, [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-20',
            'payment_status' => PaymentStatus::Pending,
            'guest_message' => 'Arriving with large luggage.',
        ]);
        BookingExtension::factory()->for($booking)->create([
            'status' => BookingExtensionStatus::AwaitingHostApproval,
        ]);
        Complaint::factory()
            ->for($booking)
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'reporter_id' => $booking->guest_user_id,
                'reported_user_id' => $listing['host']->id,
                'status' => ComplaintStatus::Open,
            ]);
        HostCleaningTask::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->for($booking)
            ->create(['status' => 'needed', 'scheduled_date' => '2026-06-20']);

        $flags = app(HostGuestStayFlagService::class)->refreshFlagsForBooking($booking->refresh());

        $this->assertEqualsCanonicalizing([
            'payment_pending',
            'checkout_today',
            'extension_requested',
            'complaint_open',
            'cleaning_needed',
            'special_request',
        ], $flags->pluck('flag_key')->all());
        $this->assertDatabaseHas('host_guest_stay_flags', [
            'booking_id' => $booking->id,
            'flag_key' => 'checkout_today',
            'status' => 'open',
            'message_key' => 'current_occupants.flags.checkout_today',
        ]);

        $overdueBooking = $this->booking($listing, [
            'check_in_date' => '2026-06-01',
            'check_out_date' => '2026-06-18',
            'payment_status' => PaymentStatus::Paid,
        ]);

        app(HostGuestStayFlagService::class)->refreshFlagsForBooking($overdueBooking);

        $this->assertDatabaseHas('host_guest_stay_flags', [
            'booking_id' => $overdueBooking->id,
            'flag_key' => 'checkout_overdue',
        ]);
    }

    public function test_host_scoping_privacy_notes_actions_and_messaging_are_safe(): void
    {
        $listing = $this->listing();
        $other = $this->listing();
        $booking = $this->booking($listing, [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-25',
        ]);
        $booking->guest->forceFill([
            'phone' => '+37060000000',
            'phone_verified' => true,
        ])->save();
        app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking);

        $own = app(HostCurrentOccupantsService::class)->getCurrentOccupants($listing['host'], new HostOccupantFilters);
        $notOwn = app(HostCurrentOccupantsService::class)->getCurrentOccupants($other['host'], new HostOccupantFilters);
        $contact = app(HostOccupantPrivacyService::class)->filterGuestContactForHost($listing['host'], $booking);
        $note = app(HostGuestStayNoteService::class)->createNote($listing['host'], $booking, 'Prefers quiet reminders.', 'important');
        $updated = app(HostGuestStayNoteService::class)->updateNote($listing['host'], $note, ['note' => 'Prefers quiet reminders and early checkout.']);
        $pinned = app(HostGuestStayNoteService::class)->pinNote($listing['host'], $updated);
        $messageResult = app(HostOccupantActionService::class)->messageGuest($listing['host'], $booking, 'I will leave the key instructions here.');

        $this->assertCount(1, $own);
        $this->assertCount(0, $notOwn);
        $this->assertTrue($contact['chat']);
        $this->assertNull($contact['phone']);
        $this->assertNull($contact['email']);
        $this->assertTrue($pinned->is_pinned);
        $this->assertSame('sent', $messageResult['status']);
        $this->assertDatabaseHas('messages', [
            'sender_user_id' => $listing['host']->id,
            'recipient_user_id' => $booking->guest_user_id,
            'booking_id' => $booking->id,
        ]);

        $this->expectException(AuthorizationException::class);
        app(HostGuestStayNoteService::class)->createNote($other['host'], $booking, 'Nope.', 'normal');
    }

    public function test_filters_summary_and_quick_actions_work_for_current_occupants(): void
    {
        $listing = $this->listing();
        $current = $this->booking($listing, [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-25',
            'payment_status' => PaymentStatus::Paid,
        ]);
        $checkingIn = $this->booking($listing, [
            'check_in_date' => '2026-06-20',
            'check_out_date' => '2026-06-28',
            'payment_status' => PaymentStatus::Paid,
        ]);
        $checkingOut = $this->booking($listing, [
            'check_in_date' => '2026-06-15',
            'check_out_date' => '2026-06-20',
            'payment_status' => PaymentStatus::Pending,
        ]);

        foreach ([$current, $checkingIn, $checkingOut] as $booking) {
            app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking);
        }
        app(HostGuestStayFlagService::class)->refreshFlagsForBooking($checkingOut);

        $todayCheckIns = app(HostCurrentOccupantsService::class)->getCurrentOccupants(
            $listing['host'],
            new HostOccupantFilters(scope: 'check_ins_today'),
        );
        $paymentPending = app(HostCurrentOccupantsService::class)->getCurrentOccupants(
            $listing['host'],
            new HostOccupantFilters(scope: 'payment_pending'),
        );
        $summary = app(HostOccupantSummaryService::class)->getSummary($listing['host']);

        $this->assertCount(1, $todayCheckIns);
        $this->assertCount(1, $paymentPending);
        $this->assertSame(3, $summary->currentCount);
        $this->assertSame(1, $summary->checkInsTodayCount);
        $this->assertSame(1, $summary->checkOutsTodayCount);
        $this->assertSame(1, $summary->paymentPendingCount);

        Livewire::actingAs($listing['host'])
            ->test(OccupantQuickActions::class)
            ->call('prepareAction', 'start_checkout_process')
            ->assertSet('needsConfirmation', true)
            ->call('markCheckedIn', $current->id)
            ->assertHasNoErrors()
            ->call('createCleaningTask', $checkingOut->id)
            ->assertHasNoErrors();

        $this->assertSame(BookingStatus::CheckedIn, $current->fresh()->status);
        $this->assertDatabaseHas('host_cleaning_tasks', [
            'booking_id' => $checkingOut->id,
            'status' => 'planned',
            'reason' => 'after_checkout',
        ]);
    }

    public function test_current_occupants_page_renders_complete_cards_filters_and_host_scope(): void
    {
        app()->setLocale('en');

        $listing = $this->listing();
        $otherListing = $this->listing();
        $booking = $this->booking($listing, [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-25',
            'nights_count' => 15,
            'payment_status' => PaymentStatus::PartiallyPaid,
            'guest_message' => 'Needs a quiet corner.',
        ]);
        $booking->guest->forceFill([
            'avatar_path' => 'avatars/current-guest.jpg',
            'phone' => '+37060000000',
            'phone_verified' => true,
            'rating_as_guest' => 4.7,
        ])->save();

        BookingExtension::factory()->for($booking)->create([
            'status' => BookingExtensionStatus::AwaitingHostApproval,
        ]);
        Complaint::factory()
            ->for($booking)
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'reporter_id' => $booking->guest_user_id,
                'reported_user_id' => $listing['host']->id,
                'status' => ComplaintStatus::Open,
            ]);
        app(HostGuestStayNoteService::class)->createNote($listing['host'], $booking, 'Prefers quiet reminders.', 'important');
        app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking->refresh());
        app(HostGuestStayFlagService::class)->refreshFlagsForBooking($booking->refresh());

        $otherBooking = $this->booking($otherListing, [
            'guest_name' => 'Other Tenant',
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-25',
        ]);
        app(HostCurrentStaySnapshotService::class)->refreshForBooking($otherBooking);

        Livewire::actingAs($listing['host'])
            ->test(CurrentOccupantsPage::class)
            ->assertSee('Current Guest')
            ->assertSee('/storage/avatars/current-guest.jpg')
            ->assertSee('Photo of Current Guest')
            ->assertSee('Room A')
            ->assertSee('Place 1')
            ->assertSee('10 Jun 2026')
            ->assertSee('25 Jun 2026')
            ->assertSee('Partially paid')
            ->assertSee('Staying now')
            ->assertSee('Chat is available')
            ->assertSee('Needs a quiet corner.')
            ->assertSee('4.7 / 5')
            ->assertSee('1 open complaint')
            ->assertSee('Extension requested')
            ->assertSee('Payment needs attention')
            ->assertSee('Special request')
            ->assertSee('Prefers quiet reminders.')
            ->assertSee('Yes')
            ->assertSee('No')
            ->assertDontSee('Other Tenant')
            ->call('setScope', 'complaints')
            ->assertSet('scope', 'complaints')
            ->assertSee('Current Guest')
            ->call('setScope', 'unknown-scope')
            ->assertSet('scope', 'all')
            ->set('onlyNeedsAttention', true)
            ->assertSet('onlyNeedsAttention', true)
            ->assertSee('Current Guest');
    }

    public function test_current_occupants_page_paginates_current_stays(): void
    {
        $listing = $this->listing();

        for ($number = 1; $number <= 12; $number++) {
            $booking = $this->booking($listing, [
                'guest_name' => sprintf('Current Guest %02d', $number),
                'check_in_date' => '2026-06-10',
                'check_out_date' => '2026-06-25',
            ]);

            app(HostCurrentStaySnapshotService::class)->refreshForBooking($booking);
        }

        Livewire::actingAs($listing['host'])
            ->test(CurrentOccupantsPage::class)
            ->assertSee('Current Guest 01')
            ->assertSee('Current Guest 10')
            ->assertDontSee('Current Guest 11')
            ->call('nextPage', 'currentOccupantsPage')
            ->assertSee('Current Guest 11')
            ->assertSee('Current Guest 12');
    }

    public function test_current_occupants_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();

        foreach ($this->componentClasses() as $component) {
            Livewire::actingAs($listing['host'])
                ->test($component)
                ->assertSee(__('current_occupants.title'));
        }

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(CurrentOccupantsPage::class)
            ->assertSee(__('current_occupants.title', [], 'ru'))
            ->assertSee(__('current_occupants.summary.current', ['count' => 0], 'ru'));
    }

    /**
     * @return array{host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(): array
    {
        $host = User::factory()->create(['is_host' => true, 'name' => 'Host User']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Current House',
                'publication_status' => 'published',
            ]);
        $room = Room::factory()->for($property)->create([
            'title' => 'Room A',
            'publication_status' => 'published',
        ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'display_name' => 'Place 1',
                'base_price_per_night' => 20,
                'currency' => 'EUR',
                'publication_status' => 'published',
            ]);

        return [
            'host' => $host,
            'property' => $property,
            'room' => $room,
            'place' => $place,
        ];
    }

    /**
     * @param  array{host:User, property:Property, room:Room, place:SleepingPlace}  $listing
     */
    private function booking(array $listing, array $overrides = []): Booking
    {
        $guestName = $overrides['guest_name'] ?? 'Current Guest';
        unset($overrides['guest_name']);

        $guest = User::factory()->create([
            'name' => $guestName,
            'rating_as_guest' => 4.7,
            'avatar' => '/avatars/current-guest.jpg',
        ]);

        $defaults = [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-25',
            'nights_count' => 15,
            'total_amount' => 250,
            'currency' => 'EUR',
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ];

        return Booking::factory()
            ->for($guest, 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create(array_merge($defaults, $overrides));
    }

    /**
     * @return list<class-string<Component>>
     */
    private function componentClasses(): array
    {
        return [
            CurrentOccupantsPage::class,
            CurrentOccupantsFilters::class,
            CurrentOccupantsSummary::class,
            CurrentOccupantCard::class,
            CurrentOccupantDetailsSheet::class,
            OccupantQuickActions::class,
            OccupantPaymentStatusBadge::class,
            OccupantStayStatusBadge::class,
            OccupantNoteSheet::class,
            OccupantFlagsPanel::class,
            OccupantExtensionPanel::class,
            OccupantCheckoutPanel::class,
        ];
    }
}
