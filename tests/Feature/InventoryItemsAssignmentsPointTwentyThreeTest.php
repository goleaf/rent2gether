<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\Inventory\GuestIssuedItemsCard;
use App\Livewire\Host\Bookings\Inventory\HostReturnInventoryChecklist;
use App\Livewire\Host\Inventory\HostInventoryPage;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\BookingDepositDecision;
use App\Models\BookingRelocation;
use App\Models\CleaningTask;
use App\Models\InventoryIssue;
use App\Models\InventoryItem;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Cleaning\CleaningTaskService;
use App\Services\Cleaning\InspectionTaskService;
use App\Services\Cleaning\PlaceReadinessService;
use App\Services\Inventory\InventoryAssignmentService;
use App\Services\Inventory\InventoryCategoryService;
use App\Services\Inventory\InventoryCheckInIntegrationService;
use App\Services\Inventory\InventoryCheckItemService;
use App\Services\Inventory\InventoryCheckOutIntegrationService;
use App\Services\Inventory\InventoryCleaningIntegrationService;
use App\Services\Inventory\InventoryConsumableUsageService;
use App\Services\Inventory\InventoryDepositIntegrationService;
use App\Services\Inventory\InventoryInspectionIntegrationService;
use App\Services\Inventory\InventoryIssueService;
use App\Services\Inventory\InventoryItemService;
use App\Services\Inventory\InventoryItemUnitService;
use App\Services\Inventory\InventoryListingIntegrationService;
use App\Services\Inventory\InventoryMaintenanceIntegrationService;
use App\Services\Inventory\InventoryPrivacyService;
use App\Services\Inventory\InventoryRatingIntegrationService;
use App\Services\Inventory\InventoryReadinessIntegrationService;
use App\Services\Inventory\InventoryRelocationIntegrationService;
use App\Services\Inventory\InventoryReplacementService;
use App\Services\Inventory\InventoryStockAlertService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryItemsAssignmentsPointTwentyThreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_point_twenty_three_schema_exists_with_indexes(): void
    {
        foreach ([
            'inventory_categories',
            'inventory_items',
            'inventory_item_units',
            'booking_inventory_assignments',
            'inventory_movements',
            'inventory_checks',
            'inventory_check_items',
            'inventory_issues',
            'inventory_issue_media',
            'inventory_replacements',
            'inventory_consumable_usages',
            'inventory_stock_alerts',
            'inventory_status_logs',
            'inventory_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('inventory_items', 'inventory_number'));
        $this->assertTrue(Schema::hasColumn('inventory_items', 'is_required_for_readiness'));
        $this->assertTrue(Schema::hasColumn('booking_inventory_assignments', 'assignment_number'));
        $this->assertTrue(Schema::hasColumn('inventory_issues', 'guest_responsibility_status'));
        $this->assertTrue(Schema::hasIndex('inventory_items', ['host_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('inventory_items', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('booking_inventory_assignments', ['booking_id', 'status']));
        $this->assertTrue(Schema::hasIndex('inventory_issues', ['host_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('inventory_events', ['source_type', 'source_id']));
    }

    public function test_host_can_manage_own_inventory_items_and_privacy_filters_hide_guest_private_data(): void
    {
        [, $host, $place] = $this->createBookingContext();
        $otherHost = User::factory()->host()->create();
        $categories = app(InventoryCategoryService::class)->seedDefaultCategories();

        $item = app(InventoryItemService::class)->createItem($host, [
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'inventory_category_id' => $categories->firstWhere('category_key', 'access')->id,
            'item_type' => 'key',
            'inventory_scope' => 'access',
            'name' => 'Room key',
            'description' => 'Main room key',
            'status' => 'available',
            'condition_status' => 'good',
            'quantity' => 1,
            'unit' => 'pcs',
            'is_returnable' => true,
            'is_consumable' => false,
            'is_fixed_asset' => false,
            'is_guest_visible' => true,
            'is_required_for_readiness' => true,
            'is_promised_in_listing' => true,
            'current_location_type' => 'sleeping_place',
            'purchase_price_amount' => 12,
            'estimated_replacement_cost_amount' => 20,
            'deposit_deduction_default_amount' => 20,
            'currency' => 'EUR',
            'host_note' => 'Keep spare key in storage.',
            'internal_note' => 'Supplier invoice #42.',
        ]);

        $this->assertSame('INV-2026-000001', $item->inventory_number);
        $this->assertSame($host->id, $item->host_user_id);
        $this->assertSame($place->property_id, $item->property_id);
        $this->assertSame($place->room_id, $item->room_id);
        $this->assertSame($place->id, $item->sleeping_place_id);
        $this->assertTrue($item->is_returnable);
        $this->assertTrue($item->is_required_for_readiness);
        $this->assertTrue($item->is_promised_in_listing);
        $this->assertTrue(app(InventoryPrivacyService::class)->canHostManage($host, $item));
        $this->assertFalse(app(InventoryPrivacyService::class)->canHostView($otherHost, $item));
        $this->assertTrue(app(InventoryItemService::class)->getForSleepingPlace($place)->contains('id', $item->id));

        $guestPayload = app(InventoryPrivacyService::class)->filterItemForGuest(User::factory()->create(), $item);

        $this->assertArrayNotHasKey('purchase_price_amount', $guestPayload);
        $this->assertArrayNotHasKey('internal_note', $guestPayload);
        $this->assertSame('Room key', $guestPayload['name']);

        $this->expectException(AuthorizationException::class);
        app(InventoryItemService::class)->createItem($otherHost, [
            'property_id' => $place->property_id,
            'item_type' => 'towel',
            'inventory_scope' => 'guest_issued',
            'name' => 'Other host towel',
        ]);
    }

    public function test_units_assignments_returns_and_inventory_issues_work_without_automatic_deposit_deduction(): void
    {
        [$guest, $host, $place, $booking] = $this->createBookingContext();
        $item = $this->createInventoryItem($host, $place, ['item_type' => 'key', 'is_returnable' => true]);
        $units = app(InventoryItemUnitService::class)->createUnits($item, 3);

        $assignment = app(InventoryAssignmentService::class)->issueUnitToGuest($host, $booking, $units->first(), [
            'assignment_type' => 'issued_at_check_in',
            'condition_at_issue' => 'good',
        ]);

        $this->assertSame('INVA-2026-000001', $assignment->assignment_number);
        $this->assertSame('issued', $assignment->status);
        $this->assertTrue($assignment->expected_return);
        $this->assertSame($guest->id, $assignment->guest_user_id);
        $this->assertSame('issued_to_guest', $assignment->inventoryItem->fresh()->status);
        $this->assertDatabaseHas('inventory_movements', [
            'inventory_item_id' => $item->id,
            'inventory_item_unit_id' => $units->first()->id,
            'movement_type' => 'issued_to_guest',
        ]);

        app(InventoryAssignmentService::class)->guestConfirmReceived($guest, $assignment);
        app(InventoryAssignmentService::class)->hostConfirmIssued($host, $assignment->fresh());
        app(InventoryAssignmentService::class)->markReturnExpected($assignment->fresh());
        app(InventoryAssignmentService::class)->markReturned($guest, $assignment->fresh(), ['condition_at_return' => 'good']);
        $returned = app(InventoryAssignmentService::class)->markReturned($host, $assignment->fresh(), ['condition_at_return' => 'good']);

        $this->assertSame('returned', $returned->status);
        $this->assertNotNull($returned->guest_confirmed_returned_at);
        $this->assertNotNull($returned->host_confirmed_returned_at);

        $notReturned = app(InventoryAssignmentService::class)->issueUnitToGuest($host, $booking, $units->get(1), [
            'assignment_type' => 'temporary_use',
        ]);
        $issue = app(InventoryAssignmentService::class)->markNotReturned($host, $notReturned, [
            'description' => 'Guest did not return the spare key.',
        ]);

        $this->assertInstanceOf(InventoryIssue::class, $issue);
        $this->assertSame('not_returned', $issue->issue_type);
        $this->assertNull($issue->booking_deposit_deduction_id);
        $this->assertFalse(BookingDepositDecision::query()->where('booking_id', $booking->id)->exists());

        $damaged = app(InventoryAssignmentService::class)->issueUnitToGuest($host, $booking, $units->get(2), [
            'assignment_type' => 'temporary_use',
        ]);
        $damagedIssue = app(InventoryAssignmentService::class)->markReturnedDamaged($host, $damaged, [
            'description' => 'Key card returned bent.',
            'deduction_suggested_amount' => 10,
        ]);

        $this->assertSame('damaged', $damagedIssue->issue_type);
        $this->assertSame('returned_damaged', $damaged->fresh()->status);
    }

    public function test_checkin_checkout_cleaning_inspection_and_readiness_integrations_track_inventory(): void
    {
        [$guest, $host, $place, $booking, $checkOut, $checkIn] = $this->createBookingContext();
        $key = $this->createInventoryItem($host, $place, [
            'item_type' => 'key',
            'is_returnable' => true,
            'is_required_for_readiness' => true,
        ]);
        $bedding = $this->createInventoryItem($host, $place, ['item_type' => 'bedding_set']);
        $towel = $this->createInventoryItem($host, $place, ['item_type' => 'towel']);

        $issued = app(InventoryCheckInIntegrationService::class)->issueDefaultItemsAtCheckIn($checkIn);

        $this->assertGreaterThanOrEqual(3, $issued->count());
        $this->assertTrue($checkIn->fresh()->keys_handed_over);
        $this->assertTrue($checkIn->fresh()->bedding_issued);
        $this->assertTrue($checkIn->fresh()->towel_issued);

        $check = app(InventoryCheckOutIntegrationService::class)->createReturnChecklistForCheckout($checkOut);

        $this->assertSame('check_out_return', $check->check_type);
        $this->assertSame($booking->id, $check->booking_id);
        $this->assertGreaterThanOrEqual(1, $check->items()->count());

        $checkItem = $check->items()->where('inventory_item_id', $key->id)->first();
        app(InventoryCheckItemService::class)->markMissing($checkItem, 'Key was not returned.');
        $issues = app(InventoryCheckItemService::class)->createIssuesFromFailedCheckItems($check->fresh());

        $this->assertTrue($issues->contains('issue_type', 'missing'));

        $cleaningTask = app(CleaningTaskService::class)->createAfterCheckout($checkOut);
        app(InventoryCleaningIntegrationService::class)->markBeddingNeedsWashing($cleaningTask);
        app(InventoryCleaningIntegrationService::class)->markTowelsNeedsWashing($cleaningTask);

        $this->assertSame('needs_washing', $bedding->fresh()->status);
        $this->assertSame('needs_washing', $towel->fresh()->status);

        $inspection = app(InspectionTaskService::class)->createPostCleaning($cleaningTask);
        $inspectionCheck = app(InventoryInspectionIntegrationService::class)->checkInventoryDuringInspection($inspection);

        $this->assertSame('inspection_check', $inspectionCheck->check_type);

        $readiness = app(PlaceReadinessService::class)->createForNextCheckIn($booking);
        $key->forceFill(['status' => 'missing'])->save();
        app(InventoryReadinessIntegrationService::class)->markReadinessBlockedIfRequiredItemsMissing($readiness);

        $this->assertSame('waiting_inventory', $readiness->fresh()->status);
        $this->assertFalse($readiness->fresh()->inventory_ready);

        app(InventoryItemService::class)->markAvailable($key->fresh());
        app(InventoryReadinessIntegrationService::class)->markInventoryReady($readiness->fresh());

        $this->assertTrue($readiness->fresh()->inventory_ready);
    }

    public function test_inventory_issues_can_start_deposit_maintenance_replacement_relocation_listing_and_rating_flows(): void
    {
        [$guest, $host, $place, $booking] = $this->createBookingContext();
        $item = $this->createInventoryItem($host, $place, [
            'item_type' => 'lamp',
            'is_fixed_asset' => true,
            'is_promised_in_listing' => true,
            'estimated_replacement_cost_amount' => 35,
            'deposit_deduction_default_amount' => 20,
        ]);
        $issue = app(InventoryIssueService::class)->createIssue($guest, $item, [
            'booking_id' => $booking->id,
            'issue_type' => 'damaged',
            'severity' => 'high',
            'description' => 'Lamp was broken during the stay.',
            'deduction_suggested_amount' => 20,
            'guest_responsibility_status' => 'possibly_guest_fault',
        ]);

        $depositCandidate = app(InventoryDepositIntegrationService::class)->createDepositDeductionCandidate($issue);

        $this->assertInstanceOf(BookingDepositDecision::class, $depositCandidate);
        $this->assertSame($depositCandidate->id, $issue->fresh()->booking_deposit_deduction_id);

        $maintenance = app(InventoryMaintenanceIntegrationService::class)->createMaintenanceFromInventoryIssue($issue->fresh());

        $this->assertNotNull($maintenance);
        $this->assertNotNull($issue->fresh()->maintenance_request_created_id);

        $replacement = app(InventoryReplacementService::class)->createReplacement($host, $issue->fresh(), [
            'replacement_reason' => 'damaged',
            'replacement_cost_amount' => 35,
            'new_item' => [
                'name' => 'Replacement lamp',
                'item_type' => 'lamp',
                'inventory_scope' => 'sleeping_place',
            ],
        ]);

        app(InventoryReplacementService::class)->markPurchased($replacement);
        app(InventoryReplacementService::class)->markInstalled($replacement->fresh());
        app(InventoryReplacementService::class)->completeReplacement($replacement->fresh());

        $this->assertSame('retired', $item->fresh()->status);
        $this->assertSame('completed', $replacement->fresh()->status);
        $this->assertNotNull($replacement->fresh()->new_inventory_item_id);

        $cleaning = CleaningTask::factory()->create([
            'booking_id' => $booking->id,
            'host_user_id' => $host->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
        ]);
        $usage = app(InventoryConsumableUsageService::class)->recordCleaningUsage($cleaning, $item->fresh(), 1);

        $this->assertSame('cleaning', $usage->usage_type);

        $alert = app(InventoryStockAlertService::class)->createAlert($item->fresh(), 'replacement_needed');

        $this->assertSame('active', $alert->status);

        $relocation = BookingRelocation::factory()->create([
            'original_booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'current_property_id' => $place->property_id,
            'current_room_id' => $place->room_id,
            'current_sleeping_place_id' => $place->id,
            'new_property_id' => $place->property_id,
            'new_room_id' => $place->room_id,
            'new_sleeping_place_id' => $place->id,
        ]);
        $transfer = app(InventoryRelocationIntegrationService::class)->prepareTransferForRelocation($relocation);

        $this->assertTrue($transfer->isNotEmpty());

        $missingPromised = app(InventoryListingIntegrationService::class)->detectMissingPromisedInventory($place);

        $this->assertTrue($missingPromised->contains('id', $item->id));

        app(InventoryRatingIntegrationService::class)->recordConfirmedInventoryDamage(
            app(InventoryIssueService::class)->markConfirmedGuestFault($issue->fresh())
        );
        app(InventoryRatingIntegrationService::class)->removeRatingImpactIfGuestFaultRejected(
            app(InventoryIssueService::class)->markGuestDisputed($issue->fresh())
        );

        $this->assertDatabaseHas('inventory_events', [
            'inventory_issue_id' => $issue->id,
            'event_key' => 'inventory_damage_rating_recorded',
        ]);
        $this->assertDatabaseHas('inventory_events', [
            'inventory_issue_id' => $issue->id,
            'event_key' => 'inventory_rating_impact_removed',
        ]);
    }

    public function test_guest_and_host_inventory_livewire_components_render_in_english_and_russian(): void
    {
        [$guest, $host, $place, $booking] = $this->createBookingContext();
        $item = $this->createInventoryItem($host, $place, ['item_type' => 'towel', 'is_returnable' => true]);
        app(InventoryAssignmentService::class)->issueToGuest($host, $booking, $item);

        $this->actingAs($host);

        app()->setLocale('en');
        Livewire::test(HostInventoryPage::class)
            ->assertSee(trans('inventory.title'));
        Livewire::test(HostReturnInventoryChecklist::class, ['bookingId' => $booking->id])
            ->assertSee(trans('inventory.actions.mark_returned'));

        app()->setLocale('ru');
        Livewire::test(HostInventoryPage::class)
            ->assertSee(trans('inventory.title'));
        Livewire::test(HostReturnInventoryChecklist::class, ['bookingId' => $booking->id])
            ->assertSee(trans('inventory.actions.mark_returned'));

        $this->actingAs($guest);

        app()->setLocale('en');
        Livewire::test(GuestIssuedItemsCard::class, ['bookingId' => $booking->id])
            ->assertSee(trans('inventory.guest.issued_items'));

        app()->setLocale('ru');
        Livewire::test(GuestIssuedItemsCard::class, ['bookingId' => $booking->id])
            ->assertSee(trans('inventory.guest.issued_items'));
    }

    /**
     * Creates an inventory item in the supplied sleeping-place context.
     *
     * @param  array<string, mixed>  $overrides
     */
    private function createInventoryItem(User $host, SleepingPlace $place, array $overrides = []): InventoryItem
    {
        return app(InventoryItemService::class)->createItem($host, array_merge([
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'item_type' => 'towel',
            'inventory_scope' => 'guest_issued',
            'name' => 'Inventory item',
            'status' => 'available',
            'condition_status' => 'good',
            'quantity' => 1,
            'unit' => 'pcs',
            'is_guest_visible' => true,
            'current_location_type' => 'sleeping_place',
            'currency' => 'EUR',
        ], $overrides));
    }

    /**
     * Creates a booking context with check-in and checkout records for inventory flows.
     *
     * @return array{0: User, 1: User, 2: SleepingPlace, 3: Booking, 4: BookingCheckOut, 5: BookingCheckIn}
     */
    private function createBookingContext(): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->host()->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'city' => 'Vilnius',
            ]);
        $room = Room::factory()->for($property)->create();
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'display_name' => 'Inventory lower bed',
                'place_number' => 'INV1',
                'base_price_per_night' => 50,
                'base_price' => 50,
                'status' => 'active',
                'publication_status' => 'published',
                'has_bedding' => true,
                'has_towel' => true,
                'has_locker' => true,
            ]);

        $booking = Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::InProgress,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-08-01',
            'check_out_date' => '2026-08-05',
            'check_in' => '2026-08-01',
            'check_out' => '2026-08-05',
            'check_in_time' => '17:00',
            'check_out_time' => '11:00',
            'arrival_time' => '17:00',
            'nights_count' => 4,
            'nights' => 4,
            'chargeable_days_count' => 4,
            'calendar_presence_days_count' => 5,
            'subtotal' => 200,
            'subtotal_amount' => 200,
            'accommodation_amount' => 200,
            'discount_amount' => 0,
            'cleaning_fee' => 10,
            'cleaning_fee_amount' => 10,
            'deposit' => 50,
            'deposit_amount' => 50,
            'service_fee' => 20,
            'service_fee_amount' => 20,
            'total' => 280,
            'total_amount' => 280,
            'host_payout_amount' => 210,
            'currency' => 'EUR',
            'cancellation_policy' => CancellationPolicy::Flexible,
            'check_in_instruction_available' => true,
        ]);

        $checkIn = BookingCheckIn::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2026-08-01',
            'planned_check_in_time' => '17:00',
            'status' => 'scheduled',
        ]);

        $checkOut = BookingCheckOut::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'check_out_date' => '2026-08-05',
            'planned_check_out_time' => '11:00',
            'actual_check_out_at' => '2026-08-05 11:00:00',
            'completed_at' => '2026-08-05 11:10:00',
            'cleaning_required' => true,
            'inspection_required' => true,
            'status' => 'completed',
        ]);

        return [$guest, $host, $place->fresh(['room', 'property']), $booking, $checkOut, $checkIn];
    }
}
