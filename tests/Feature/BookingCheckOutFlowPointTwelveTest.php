<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\CheckOut\GuestCheckOutPage;
use App\Livewire\Host\CheckOut\HostCheckOutDetailsSheet;
use App\Models\Booking;
use App\Models\BookingCheckOutInventoryCheck;
use App\Models\BookingCheckOutIssue;
use App\Models\BookingCheckOutMedia;
use App\Models\BookingStay;
use App\Models\Property;
use App\Models\PropertyCurrentOccupancySnapshot;
use App\Models\Room;
use App\Models\RoomCurrentOccupancySnapshot;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use App\Services\CheckOut\BookingCheckOutCalendarIntegrationService;
use App\Services\CheckOut\BookingCheckOutExtensionSuggestionService;
use App\Services\CheckOut\BookingCheckOutInventoryService;
use App\Services\CheckOut\BookingCheckOutIssueService;
use App\Services\CheckOut\BookingCheckOutMediaService;
use App\Services\CheckOut\BookingCheckOutPrivacyService;
use App\Services\CheckOut\BookingCheckOutService;
use App\Services\CheckOut\BookingCheckOutStepService;
use App\Services\CheckOut\BookingForgottenItemService;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class BookingCheckOutFlowPointTwelveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-07-20 09:00:00');
    }

    public function test_point_twelve_schema_relationships_and_checkout_creation_exist(): void
    {
        $this->assertTrue(Schema::hasTable('booking_check_outs'));
        $this->assertTrue(Schema::hasTable('booking_check_out_steps'));
        $this->assertTrue(Schema::hasTable('booking_check_out_media'));
        $this->assertTrue(Schema::hasTable('booking_check_out_inventory_checks'));
        $this->assertTrue(Schema::hasTable('booking_check_out_issues'));
        $this->assertTrue(Schema::hasTable('booking_forgotten_items'));
        $this->assertTrue(Schema::hasTable('booking_check_out_status_logs'));
        $this->assertTrue(Schema::hasTable('booking_check_out_events'));
        $this->assertTrue(Schema::hasColumn('booking_check_outs', 'checkout_number'));
        $this->assertTrue(Schema::hasColumn('booking_check_outs', 'booking_stay_id'));
        $this->assertTrue(Schema::hasIndex('booking_check_outs', ['checkout_number'], 'unique'));
        $this->assertTrue(Schema::hasIndex('booking_check_outs', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_check_out_steps', ['booking_check_out_id', 'step_key']));
        $this->assertTrue(Schema::hasIndex('booking_check_out_events', ['booking_check_out_id']));

        $context = $this->stayContext();
        $checkOut = app(BookingCheckOutService::class)->createForStay($context['stay']);

        $this->assertMatchesRegularExpression('/^OUT-\d{4}-\d{6}$/', $checkOut->checkout_number);
        $this->assertSame($context['booking']->id, $checkOut->booking->id);
        $this->assertSame($context['stay']->id, $checkOut->stay->id);
        $this->assertSame($context['place']->id, $checkOut->sleepingPlace->id);
        $this->assertSame($checkOut->id, $context['booking']->fresh()->checkOut->id);
        $this->assertSame($checkOut->id, $context['stay']->fresh()->checkOut->id);
        $this->assertGreaterThanOrEqual(17, app(BookingCheckOutStepService::class)->createDefaultSteps($checkOut)->count());
        $this->assertTrue($checkOut->events()->where('event_key', 'checkout_scheduled')->exists());
    }

    public function test_guest_and_host_checkout_confirmations_sync_booking_stay_logs_events_and_occupancy(): void
    {
        $context = $this->stayContext();
        $checkOut = app(BookingCheckOutService::class)->createForStay($context['stay']);

        app(BookingCheckOutService::class)->markGuestPreparing($context['guest'], $checkOut);
        $guestConfirmed = app(BookingCheckOutService::class)->confirmByGuest($context['guest'], $checkOut->refresh());

        $this->assertSame('guest_checked_out', $guestConfirmed->status);
        $this->assertNotNull($guestConfirmed->guest_confirmed_checkout_at);
        $this->assertTrue($context['booking']->fresh()->status === BookingStatus::GuestCheckedOut);
        $this->assertSame('guest_checked_out', $context['stay']->fresh()->status);
        $this->assertDatabaseHas('booking_check_out_status_logs', [
            'booking_check_out_id' => $checkOut->id,
            'new_status' => 'guest_checked_out',
        ]);
        $this->assertDatabaseHas('booking_check_out_events', [
            'booking_check_out_id' => $checkOut->id,
            'event_key' => 'guest_confirmed_checkout',
        ]);

        $hostConfirmed = app(BookingCheckOutService::class)->confirmByHost($context['host'], $checkOut->refresh());

        $this->assertSame('waiting_inspection', $hostConfirmed->status);
        $this->assertNotNull($hostConfirmed->host_confirmed_checkout_at);
        $this->assertSame(0, RoomCurrentOccupancySnapshot::query()->where('room_id', $context['room']->id)->firstOrFail()->current_occupants_count);
        $this->assertSame(0, PropertyCurrentOccupancySnapshot::query()->where('property_id', $context['property']->id)->firstOrFail()->current_occupants_count);
    }

    public function test_inventory_issues_media_forgotten_items_deposit_and_calendar_rules_work(): void
    {
        $context = $this->stayContext();
        $checkOut = app(BookingCheckOutService::class)->createForStay($context['stay']);
        $checks = app(BookingCheckOutInventoryService::class)->createInventoryChecklist($checkOut);
        $lostKey = $checks->firstWhere('item_name_snapshot', 'key') ?? $checks->first();

        $this->assertContainsOnlyInstancesOf(BookingCheckOutInventoryCheck::class, $checks);

        app(BookingCheckOutInventoryService::class)->markItemReturned($checks->first());
        app(BookingCheckOutInventoryService::class)->markItemLost($lostKey, new Money(15.00, 'EUR'));
        app(BookingCheckOutIssueService::class)->reportIssue($context['host'], $checkOut->refresh(), [
            'issue_type' => 'damage',
            'severity' => 'high',
            'description' => 'Broken locker door.',
            'amount_requested' => 15.00,
            'currency' => 'EUR',
            'deposit_related' => true,
            'repair_needed' => true,
        ]);
        $media = app(BookingCheckOutMediaService::class)->addMedia($context['host'], $checkOut->refresh(), [
            'media_type' => 'photo',
            'media_role' => 'damage_evidence',
            'path' => 'checkout/damage.jpg',
            'visibility' => 'host_only',
        ]);
        $item = app(BookingForgottenItemService::class)->createForgottenItem($context['host'], $checkOut->refresh(), [
            'item_name' => 'Blue scarf',
            'description' => 'Left in the locker.',
            'photo_path' => 'checkout/scarf.jpg',
            'storage_location' => 'Host shelf',
            'return_method' => 'pickup',
        ]);
        app(BookingForgottenItemService::class)->notifyGuest($item);

        $issue = BookingCheckOutIssue::query()->where('booking_check_out_id', $checkOut->id)->where('issue_type', 'damage')->firstOrFail();
        app(BookingCheckOutIssueService::class)->createDepositDeductionIfNeeded($issue);

        $checkOut->refresh()->forceFill([
            'status' => 'completed',
            'cleaning_required' => true,
            'inspection_required' => false,
            'repair_required' => false,
            'has_complaint' => false,
            'has_dispute' => false,
        ])->save();
        app(BookingCheckOutCalendarIntegrationService::class)->openSleepingPlaceIfReady($checkOut->refresh());

        $this->assertTrue($lostKey->fresh()->deduction_requested);
        $this->assertTrue($checkOut->fresh()->deposit_deduction_requested);
        $this->assertSame('guest_notified', $item->fresh()->status);
        $this->assertSame($context['property']->id, $item->fresh()->property_id);
        $this->assertInstanceOf(BookingCheckOutMedia::class, $media);
        $this->assertDatabaseMissing('sleeping_place_calendar_days', [
            'sleeping_place_id' => $context['place']->id,
            'date' => '2026-07-22',
            'status' => 'available',
        ]);

        $checkOut->forceFill(['cleaning_required' => false, 'has_damage' => false, 'has_inventory_issue' => false])->save();
        app(BookingCheckOutCalendarIntegrationService::class)->openSleepingPlaceIfReady($checkOut->refresh());

        $this->assertTrue(SleepingPlaceCalendarDay::query()
            ->where('sleeping_place_id', $context['place']->id)
            ->whereDate('date', '2026-07-22')
            ->where('status', 'available')
            ->exists());
    }

    public function test_privacy_extension_suggestions_and_livewire_components_render_in_english_and_russian(): void
    {
        $context = $this->stayContext();
        $other = $this->listing();
        $checkOut = app(BookingCheckOutService::class)->createForStay($context['stay']);
        app(BookingCheckOutMediaService::class)->addMedia($context['host'], $checkOut, [
            'media_type' => 'photo',
            'media_role' => 'after_checkout_room',
            'path' => 'checkout/room.jpg',
            'visibility' => 'internal',
        ]);

        $privacy = app(BookingCheckOutPrivacyService::class);

        $this->assertTrue($privacy->canGuestView($context['guest'], $checkOut));
        $this->assertFalse($privacy->canGuestView($other['guest'], $checkOut));
        $this->assertTrue($privacy->canHostView($context['host'], $checkOut));
        $this->assertFalse($privacy->canHostView($other['host'], $checkOut));
        $this->assertStringNotContainsString('internal_host_note', json_encode($privacy->filterForGuest($context['guest'], $checkOut), JSON_THROW_ON_ERROR));
        $this->assertFalse($privacy->canViewMedia($context['guest'], $checkOut->media()->firstOrFail()));

        $this->assertTrue(app(BookingCheckOutExtensionSuggestionService::class)->canSuggestExtension($context['booking']));

        Booking::factory()
            ->for($context['guest'], 'guest')
            ->for($context['host'], 'host')
            ->for($context['property'])
            ->for($context['room'])
            ->for($context['place'], 'sleepingPlace')
            ->create([
                'guest_user_id' => $context['guest']->id,
                'host_user_id' => $context['host']->id,
                'property_id' => $context['property']->id,
                'room_id' => $context['room']->id,
                'sleeping_place_id' => $context['place']->id,
                'status' => BookingStatus::Confirmed,
                'check_in_date' => '2026-07-22',
                'check_out_date' => '2026-07-24',
                'check_in' => '2026-07-22',
                'check_out' => '2026-07-24',
            ]);
        SleepingPlaceCalendarDay::factory()->for($context['place'], 'sleepingPlace')->create([
            'date' => '2026-07-22',
            'status' => 'booked',
        ]);

        $this->assertFalse(app(BookingCheckOutExtensionSuggestionService::class)->canSuggestExtension($context['booking']));

        Livewire::actingAs($context['guest'])
            ->test(GuestCheckOutPage::class, ['booking' => $context['booking']])
            ->assertSee(__('check_out.title', [], 'en'));

        Livewire::actingAs($context['host'])
            ->test(HostCheckOutDetailsSheet::class, ['checkOut' => $checkOut])
            ->assertSee(__('check_out.host_title', [], 'en'));

        app()->setLocale('ru');

        Livewire::actingAs($context['guest'])
            ->test(GuestCheckOutPage::class, ['booking' => $context['booking']])
            ->assertSee(__('check_out.title', [], 'ru'));

        Livewire::actingAs($context['host'])
            ->test(HostCheckOutDetailsSheet::class, ['checkOut' => $checkOut])
            ->assertSee(__('check_out.host_title', [], 'ru'));
    }

    /**
     * @return array{guest: User, host: User, property: Property, room: Room, place: SleepingPlace}
     */
    private function listing(): array
    {
        $guest = User::factory()->create(['name' => 'Checkout Guest']);
        $host = User::factory()->host()->create(['name' => 'Checkout Host']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'user_id' => $host->id,
                'host_user_id' => $host->id,
                'title' => 'Checkout Property',
                'city' => 'Vilnius',
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'user_id' => $host->id,
                'title' => 'Checkout Room',
                'sleeping_places_count' => 1,
            ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'display_name' => 'Bed 8',
                'place_number' => '8',
                'base_price_per_night' => 20,
                'currency' => 'EUR',
            ]);

        return compact('guest', 'host', 'property', 'room', 'place');
    }

    /**
     * @return array{guest: User, host: User, property: Property, room: Room, place: SleepingPlace, booking: Booking, stay: BookingStay}
     */
    private function stayContext(): array
    {
        $listing = $this->listing();
        $booking = Booking::factory()
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'guest_user_id' => $listing['guest']->id,
                'host_user_id' => $listing['host']->id,
                'property_id' => $listing['property']->id,
                'room_id' => $listing['room']->id,
                'sleeping_place_id' => $listing['place']->id,
                'status' => BookingStatus::StayInProgress,
                'payment_status' => PaymentStatus::Paid,
                'check_in_date' => '2026-07-18',
                'check_out_date' => '2026-07-22',
                'check_in' => '2026-07-18',
                'check_out' => '2026-07-22',
                'check_in_time' => '15:00',
                'check_out_time' => '11:00',
                'nights_count' => 4,
                'nights' => 4,
                'calendar_presence_days_count' => 5,
                'calendar_days_count' => 5,
                'total_amount' => 126,
                'total_payable' => 126,
                'total' => 126,
                'deposit_amount' => 30,
                'deposit' => 30,
                'currency' => 'EUR',
                'paid_at' => now()->subDays(2),
                'checked_in_at' => now()->subDays(2),
            ]);
        $stay = BookingStay::factory()
            ->for($booking)
            ->for($listing['guest'], 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'booking_id' => $booking->id,
                'guest_user_id' => $listing['guest']->id,
                'host_user_id' => $listing['host']->id,
                'property_id' => $listing['property']->id,
                'room_id' => $listing['room']->id,
                'sleeping_place_id' => $listing['place']->id,
                'status' => 'active',
                'check_in_date' => '2026-07-18',
                'actual_check_in_at' => now()->subDays(2),
                'planned_check_out_date' => '2026-07-22',
                'planned_check_out_time' => '11:00',
                'nights_count' => 4,
                'nights_passed' => 2,
                'nights_remaining' => 2,
                'started_at' => now()->subDays(2),
            ]);

        return [
            ...$listing,
            'booking' => $booking,
            'stay' => $stay,
        ];
    }
}
