<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Livewire\Bookings\Readiness\GuestPlaceReadyNotice;
use App\Livewire\Host\Cleaning\HostCleaningTasksPage;
use App\Livewire\Host\Inspections\HostInspectionDetailsSheet;
use App\Livewire\Host\Readiness\PlaceReadinessCard;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\CleaningTask;
use App\Models\CleaningTaskIssue;
use App\Models\CleaningTaskMedia;
use App\Models\ComplaintAction;
use App\Models\ComplaintCase;
use App\Models\PlaceReadinessCheck;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use App\Services\Cleaning\CleaningComplaintIntegrationService;
use App\Services\Cleaning\CleaningPolicyService;
use App\Services\Cleaning\CleaningPrivacyService;
use App\Services\Cleaning\CleaningTaskIssueService;
use App\Services\Cleaning\CleaningTaskItemService;
use App\Services\Cleaning\CleaningTaskMediaService;
use App\Services\Cleaning\CleaningTaskService;
use App\Services\Cleaning\InspectionTaskItemService;
use App\Services\Cleaning\InspectionTaskService;
use App\Services\Cleaning\PlaceReadinessService;
use App\Services\Cleaning\TurnoverReadinessService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CleaningInspectionReadinessPointTwentyOneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-01 09:00:00');
        CarbonImmutable::setTestNow('2026-08-01 09:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_point_twenty_one_schema_exists_with_indexes(): void
    {
        foreach ([
            'cleaning_policies',
            'cleaning_tasks',
            'cleaning_task_items',
            'cleaning_task_media',
            'cleaning_task_issues',
            'inspection_tasks',
            'inspection_task_items',
            'inspection_task_media',
            'place_readiness_checks',
            'cleaning_status_logs',
            'cleaning_events',
            'inspection_status_logs',
            'inspection_events',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), $table.' table is missing.');
        }

        $this->assertTrue(Schema::hasColumn('cleaning_tasks', 'cleaning_number'));
        $this->assertTrue(Schema::hasColumn('cleaning_tasks', 'responsible_type'));
        $this->assertTrue(Schema::hasColumn('inspection_tasks', 'inspection_number'));
        $this->assertTrue(Schema::hasColumn('place_readiness_checks', 'readiness_number'));
        $this->assertTrue(Schema::hasIndex('cleaning_tasks', ['host_user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('cleaning_task_items', ['cleaning_task_id', 'item_key']));
        $this->assertTrue(Schema::hasIndex('inspection_tasks', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('place_readiness_checks', ['sleeping_place_id']));
    }

    public function test_cleaning_policy_task_after_checkout_and_host_privacy_work(): void
    {
        [$guest, $host, $place, $booking, $checkOut] = $this->createBookingContext();
        $otherHost = User::factory()->host()->create();

        $policy = app(CleaningPolicyService::class)->createDefaultForSleepingPlace($place);
        $task = app(CleaningTaskService::class)->createAfterCheckout($checkOut);

        $this->assertSame($place->id, $policy->sleeping_place_id);
        $this->assertSame('CLN-2026-000001', $task->cleaning_number);
        $this->assertSame('after_check_out', $task->cleaning_type);
        $this->assertSame('sleeping_place', $task->cleaning_scope);
        $this->assertSame($booking->id, $task->booking_id);
        $this->assertSame($checkOut->id, $task->booking_check_out_id);
        $this->assertSame($place->property_id, $task->property_id);
        $this->assertSame($place->room_id, $task->room_id);
        $this->assertSame($place->id, $task->sleeping_place_id);
        $this->assertGreaterThanOrEqual(20, $task->items()->count());
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-08-05',
            'status' => 'cleaning',
            'source' => 'cleaning_task',
        ]);
        $this->assertTrue(app(CleaningPrivacyService::class)->canHostView($host, $task));
        $this->assertFalse(app(CleaningPrivacyService::class)->canHostView($otherHost, $task));

        $this->expectException(AuthorizationException::class);
        app(CleaningTaskService::class)->markStarted($otherHost, $task);
    }

    public function test_cleaning_assignment_checklist_photos_completion_and_issues_work_without_cleaner_role(): void
    {
        [, $host, , , $checkOut] = $this->createBookingContext();
        $task = app(CleaningTaskService::class)->createAfterCheckout($checkOut);

        $assigned = app(CleaningTaskService::class)->assignResponsible($host, $task, [
            'responsible_type' => 'external_person',
            'responsible_name_snapshot' => 'Marta',
            'responsible_contact_snapshot' => '+37060000000',
        ]);

        $this->assertSame('external_person', $assigned->responsible_type);
        $this->assertSame('assigned', $assigned->status);

        app(CleaningTaskService::class)->markStarted($host, $assigned);

        $this->expectException(ValidationException::class);
        app(CleaningTaskService::class)->markCompleted($host, $assigned->fresh());
    }

    public function test_cleaning_can_complete_after_required_checklist_and_after_photo_are_done(): void
    {
        [, $host, , , $checkOut] = $this->createBookingContext();
        $task = app(CleaningTaskService::class)->createAfterCheckout($checkOut);

        foreach ($task->items()->pluck('item_key') as $itemKey) {
            app(CleaningTaskItemService::class)->markCompleted($task, $itemKey, $host);
        }

        app(CleaningTaskMediaService::class)->uploadAfterPhoto($host, $task, [
            'path' => 'cleaning/after.jpg',
            'media_role' => 'after_cleaning_sleeping_place',
        ]);

        $completed = app(CleaningTaskService::class)->markCompleted($host, $task->fresh());

        $this->assertSame('completed', $completed->status);
        $this->assertTrue($completed->checklist_completed);
        $this->assertTrue($completed->after_photos_uploaded);
        $this->assertDatabaseHas('cleaning_events', [
            'cleaning_task_id' => $task->id,
            'event_key' => 'cleaning_completed',
        ]);
    }

    public function test_cleaning_issues_create_follow_up_records_and_block_calendar(): void
    {
        [, $host, $place, $booking, $checkOut] = $this->createBookingContext();
        $task = app(CleaningTaskService::class)->createAfterCheckout($checkOut);

        $issue = app(CleaningTaskIssueService::class)->reportIssue($host, $task, [
            'issue_type' => 'needs_repair',
            'severity' => 'high',
            'description' => 'Broken lamp near the bed.',
            'creates_maintenance_request' => true,
            'creates_deposit_review' => true,
            'creates_complaint' => true,
            'blocks_calendar' => true,
        ]);

        app(CleaningTaskIssueService::class)->createMaintenanceIfNeeded($issue);
        app(CleaningTaskIssueService::class)->createDepositReviewIfNeeded($issue->fresh());
        app(CleaningTaskIssueService::class)->createComplaintIfNeeded($issue->fresh());
        app(CleaningTaskIssueService::class)->blockCalendarIfNeeded($issue->fresh());

        $freshIssue = $issue->fresh();

        $this->assertInstanceOf(CleaningTaskIssue::class, $freshIssue);
        $this->assertTrue($task->fresh()->repair_required);
        $this->assertNotNull($freshIssue->maintenance_request_id);
        $this->assertNotNull($freshIssue->booking_deposit_case_id);
        $this->assertNotNull($freshIssue->complaint_case_id);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $place->id,
            'date' => '2026-08-05',
            'status' => 'repair',
            'booking_id' => $booking->id,
        ]);
    }

    public function test_cleaning_from_complaint_and_after_repair_are_linked(): void
    {
        [$guest, $host, , $booking] = $this->createBookingContext();
        $case = ComplaintCase::factory()->create([
            'booking_id' => $booking->id,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'reporter_user_id' => $guest->id,
            'against_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'complaint_type' => 'dirty_room',
        ]);
        $action = ComplaintAction::factory()->create([
            'complaint_case_id' => $case->id,
            'action_type' => 'create_cleaning',
            'status' => 'pending',
        ]);

        $cleaning = app(CleaningComplaintIntegrationService::class)->createCleaningFromComplaint($case);
        app(CleaningComplaintIntegrationService::class)->markComplaintActionCompleted($cleaning);
        $afterRepair = app(CleaningTaskService::class)->createAfterRepair((object) [
            'host_user_id' => $host->id,
            'property_id' => $booking->property_id,
            'room_id' => $booking->room_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'booking_id' => $booking->id,
            'id' => 777,
        ]);

        $this->assertSame('after_complaint', $cleaning->cleaning_type);
        $this->assertSame($cleaning->id, $case->fresh()->cleaning_task_id);
        $this->assertSame('completed', $action->fresh()->status);
        $this->assertSame('after_repair', $afterRepair->cleaning_type);
        $this->assertSame(777, $afterRepair->maintenance_request_id);
    }

    public function test_inspection_tasks_after_cleaning_and_checkout_can_pass_or_fail(): void
    {
        [, $host, $place, , $checkOut] = $this->createBookingContext();
        $cleaning = app(CleaningTaskService::class)->createAfterCheckout($checkOut);
        $inspection = app(InspectionTaskService::class)->createPostCleaning($cleaning);
        $postCheckout = app(InspectionTaskService::class)->createPostCheckout($checkOut);

        $this->assertSame('INSP-2026-000001', $inspection->inspection_number);
        $this->assertSame('post_cleaning', $inspection->inspection_type);
        $this->assertSame($place->id, $inspection->sleeping_place_id);
        $this->assertGreaterThanOrEqual(15, $inspection->items()->count());
        $this->assertSame('post_checkout', $postCheckout->inspection_type);

        foreach ($inspection->items()->pluck('item_key') as $itemKey) {
            app(InspectionTaskItemService::class)->markCompleted($inspection, $itemKey, $host);
        }

        $passed = app(InspectionTaskService::class)->markPassed($host, $inspection->fresh(), [
            'result_summary' => 'Everything is ready.',
        ]);

        $failed = app(InspectionTaskService::class)->markFailed($host, $postCheckout, [
            'cleaning_required' => true,
            'repair_required' => true,
            'result_summary' => 'Mattress needs attention.',
        ]);

        $this->assertSame('passed', $passed->status);
        $this->assertTrue($passed->passed);
        $this->assertSame('failed', $failed->status);
        $this->assertTrue($failed->cleaning_required);
        $this->assertTrue($failed->repair_required);
    }

    public function test_place_readiness_requires_cleaning_inspection_access_and_no_blockers(): void
    {
        [$guest, $host, $place, $booking, $checkOut] = $this->createBookingContext();
        $cleaning = app(CleaningTaskService::class)->createAfterCheckout($checkOut);
        $check = app(PlaceReadinessService::class)->createForNextCheckIn($booking);

        $notReady = app(PlaceReadinessService::class)->checkReadiness($check);

        $this->assertSame('waiting_cleaning', $notReady->status);
        $this->assertFalse($notReady->cleaning_completed);

        foreach ($cleaning->items()->pluck('item_key') as $itemKey) {
            app(CleaningTaskItemService::class)->markCompleted($cleaning, $itemKey, $host);
        }
        app(CleaningTaskMediaService::class)->uploadAfterPhoto($host, $cleaning, [
            'path' => 'cleaning/after.jpg',
            'media_role' => 'after_cleaning_sleeping_place',
        ]);
        app(CleaningTaskService::class)->markCompleted($host, $cleaning->fresh());

        $inspection = app(InspectionTaskService::class)->createPostCleaning($cleaning->fresh());
        app(InspectionTaskService::class)->markPassed($host, $inspection);

        $ready = app(PlaceReadinessService::class)->checkReadiness($check->fresh([
            'booking',
            'sleepingPlace',
        ]));

        $this->assertSame('ready', $ready->status);
        $this->assertTrue($ready->cleaning_completed);
        $this->assertTrue($ready->inspection_completed);
        $this->assertTrue($ready->access_ready);
        $this->assertTrue($ready->calendar_available);
        $this->assertTrue(app(CleaningPrivacyService::class)->filterForGuest($guest, $cleaning->fresh())['is_safe_notice']);
        $this->assertArrayNotHasKey('internal_host_note', app(CleaningPrivacyService::class)->filterForGuest($guest, $cleaning->fresh()));
        $this->assertFalse(app(CleaningPrivacyService::class)->canViewMedia($guest, CleaningTaskMedia::factory()->create([
            'cleaning_task_id' => $cleaning->id,
            'booking_id' => $booking->id,
            'visibility' => 'future_review_only',
        ])));

        $blocking = PlaceReadinessCheck::factory()->create([
            'booking_id' => $booking->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'host_user_id' => $host->id,
            'repair_completed' => false,
            'complaint_not_blocking' => false,
        ]);

        $this->assertSame('waiting_repair', app(PlaceReadinessService::class)->checkReadiness($blocking)->status);
    }

    public function test_same_day_turnover_detects_gap_and_creates_urgent_tasks(): void
    {
        [, , $place, $previousBooking] = $this->createBookingContext();
        $nextBooking = Booking::factory()->create([
            'guest_user_id' => User::factory()->create()->id,
            'host_user_id' => $previousBooking->host_user_id,
            'property_id' => $previousBooking->property_id,
            'room_id' => $previousBooking->room_id,
            'sleeping_place_id' => $place->id,
            'check_in_date' => '2026-08-05',
            'check_in' => '2026-08-05',
            'check_in_time' => '17:00',
            'check_out_date' => '2026-08-07',
            'check_out' => '2026-08-07',
        ]);
        app(CleaningPolicyService::class)->createDefaultForSleepingPlace($place)->forceFill([
            'default_cleaning_duration_minutes' => 180,
            'default_inspection_duration_minutes' => 30,
            'same_day_turnover_min_gap_minutes' => 15,
        ])->save();

        $window = app(TurnoverReadinessService::class)->calculateTurnoverWindow($previousBooking, $nextBooking);
        $tasks = app(TurnoverReadinessService::class)->createSameDayTurnoverTasks($previousBooking, $nextBooking);

        $this->assertTrue(app(TurnoverReadinessService::class)->canPrepareInTime($previousBooking, $nextBooking));
        $this->assertSame(225, $window['required_minutes']);
        $this->assertTrue($tasks->contains(fn (CleaningTask $task): bool => $task->priority === 'same_day_turnover'));

        $nextBooking->forceFill(['check_in_time' => '12:30'])->save();

        $this->assertFalse(app(TurnoverReadinessService::class)->canPrepareInTime($previousBooking, $nextBooking->fresh()));
        $this->assertTrue(app(TurnoverReadinessService::class)->buildTurnoverWarnings($previousBooking, $nextBooking->fresh())->contains('same_day_turnover_risky'));
    }

    public function test_canonical_cleaning_livewire_components_render_in_english_and_russian(): void
    {
        [, $host, , $booking, $checkOut] = $this->createBookingContext();
        $task = app(CleaningTaskService::class)->createAfterCheckout($checkOut);
        $inspection = app(InspectionTaskService::class)->createPostCleaning($task);
        $readiness = app(PlaceReadinessService::class)->createForNextCheckIn($booking);

        $this->actingAs($host);

        app()->setLocale('en');
        Livewire::test(HostCleaningTasksPage::class)
            ->assertSee(trans('cleaning.title'));
        Livewire::test(HostInspectionDetailsSheet::class, ['inspectionTaskId' => $inspection->id])
            ->assertSee(trans('inspections.title'));
        Livewire::test(PlaceReadinessCard::class, ['readinessCheckId' => $readiness->id])
            ->assertSee(trans('readiness.title'));
        Livewire::test(GuestPlaceReadyNotice::class, ['bookingId' => $booking->id])
            ->assertSee(trans('readiness.messages.place_not_ready'));

        app()->setLocale('ru');
        Livewire::test(HostCleaningTasksPage::class)
            ->assertSee(trans('cleaning.title'));
        Livewire::test(HostInspectionDetailsSheet::class, ['inspectionTaskId' => $inspection->id])
            ->assertSee(trans('inspections.title'));
        Livewire::test(PlaceReadinessCard::class, ['readinessCheckId' => $readiness->id])
            ->assertSee(trans('readiness.title'));
    }

    /**
     * Creates a booking context with the property-room-sleeping-place hierarchy required by cleaning flows.
     *
     * @return array{0: User, 1: User, 2: SleepingPlace, 3: Booking, 4: BookingCheckOut}
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
                'display_name' => 'Cleaning lower bed',
                'place_number' => 'CLN1',
                'base_price_per_night' => 50,
                'base_price' => 50,
                'status' => 'active',
                'publication_status' => 'published',
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

        SleepingPlaceCalendarDay::factory()->create([
            'sleeping_place_id' => $place->id,
            'date' => '2026-08-05',
            'status' => 'booked',
            'booking_id' => $booking->id,
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

        return [$guest, $host, $place->fresh(['room', 'property']), $booking, $checkOut];
    }
}
