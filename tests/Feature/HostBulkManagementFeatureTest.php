<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\Bulk\BulkCalendarEditor;
use App\Livewire\Host\Bulk\BulkCleaningEditor;
use App\Livewire\Host\Bulk\BulkMessageGuests;
use App\Livewire\Host\Bulk\BulkPriceEditor;
use App\Livewire\Host\Bulk\BulkPublicationEditor;
use App\Livewire\Host\Bulk\BulkRulesEditor;
use App\Livewire\Host\Bulk\CloneRoomAction;
use App\Livewire\Host\Bulk\CloneSleepingPlaceAction;
use App\Livewire\Host\Bulk\CreateIdenticalPlacesAction;
use App\Livewire\Host\Bulk\HostBulkActionPicker;
use App\Livewire\Host\Bulk\HostBulkActionsPanel;
use App\Livewire\Host\Bulk\HostBulkConfirm;
use App\Livewire\Host\Bulk\HostBulkPreview;
use App\Livewire\Host\Bulk\HostBulkResult;
use App\Livewire\Host\Bulk\HostBulkTargetSelector;
use App\Models\Booking;
use App\Models\HostBulkActionBatch;
use App\Models\HostBulkActionItem;
use App\Models\HostBulkActionLog;
use App\Models\HostCleaningTask;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\SleepingPlace;
use App\Models\SleepingPlacePricingSetting;
use App\Models\SleepingPlaceRule;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Services\HostBulk\HostBulkActionService;
use App\Services\HostBulk\HostBulkCalendarService;
use App\Services\HostBulk\HostBulkCleaningService;
use App\Services\HostBulk\HostBulkCloneService;
use App\Services\HostBulk\HostBulkMessageService;
use App\Services\HostBulk\HostBulkPermissionService;
use App\Services\HostBulk\HostBulkPricingService;
use App\Services\HostBulk\HostBulkPublicationService;
use App\Services\HostBulk\HostBulkRulesService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class HostBulkManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_tables_models_relationships_and_factories_exist(): void
    {
        $this->assertTrue(Schema::hasTable('host_bulk_action_batches'));
        $this->assertTrue(Schema::hasTable('host_bulk_action_items'));
        $this->assertTrue(Schema::hasTable('host_bulk_action_logs'));
        $this->assertTrue(Schema::hasTable('host_cleaning_tasks'));
        $this->assertTrue(Schema::hasIndex('host_bulk_action_batches', ['user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('host_bulk_action_items', ['batch_id', 'status']));
        $this->assertTrue(Schema::hasIndex('host_bulk_action_logs', ['target_type', 'target_id']));
        $this->assertTrue(Schema::hasIndex('host_cleaning_tasks', ['sleeping_place_id', 'status']));

        $listing = $this->listing();
        $batch = HostBulkActionBatch::factory()->for($listing['host'], 'user')->create();
        $item = HostBulkActionItem::factory()->for($batch, 'batch')->create();
        $log = HostBulkActionLog::factory()->for($listing['host'], 'user')->for($batch, 'batch')->create();
        $task = HostCleaningTask::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['places']->first(), 'sleepingPlace')
            ->create();

        $this->assertSame($listing['host']->id, $batch->user->id);
        $this->assertTrue($batch->items->contains($item));
        $this->assertTrue($batch->logs->contains($log));
        $this->assertSame($listing['places']->first()->id, $task->sleepingPlace->id);
    }

    public function test_batch_preview_confirm_process_and_logging_for_price_update(): void
    {
        $listing = $this->listing(places: 2);
        $service = app(HostBulkActionService::class);

        $batch = $service->createBatch($listing['host'], 'change_price', [
            ['type' => 'sleeping_place', 'id' => $listing['places'][0]->id],
            ['type' => 'sleeping_place', 'id' => $listing['places'][1]->id],
        ], [
            'price' => 22,
            'currency' => 'EUR',
            'range' => ['start' => '2026-07-01', 'end' => '2026-07-04'],
        ]);

        $preview = $service->preview($batch);
        $this->assertSame('previewed', $batch->fresh()->status);

        $confirmed = $service->confirm($batch);
        $this->assertSame('confirmed', $confirmed->status);

        $processed = $service->process($confirmed);
        $this->assertSame(2, $preview['selected_count']);
        $this->assertSame(2, $preview['affected_count']);
        $this->assertSame('completed', $processed->status);
        $this->assertSame(2, $processed->affected_count);
        $this->assertSame('22.00', $listing['places'][0]->fresh()->base_price_per_night);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['places'][0]->id,
            'date' => '2026-07-01',
            'price' => 22,
        ]);
        $this->assertDatabaseHas('host_bulk_action_logs', [
            'user_id' => $listing['host']->id,
            'batch_id' => $processed->id,
            'action_type' => 'change_price',
        ]);
    }

    public function test_permissions_and_booking_conflicts_are_respected(): void
    {
        $listing = $this->listing(places: 2);
        $otherListing = $this->listing();
        $guest = User::factory()->create();
        Booking::factory()
            ->for($guest, 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['places'][0], 'sleepingPlace')
            ->create([
                'check_in_date' => '2026-07-02',
                'check_out_date' => '2026-07-03',
                'status' => BookingStatus::Confirmed,
            ]);

        $this->expectException(AuthorizationException::class);
        app(HostBulkPermissionService::class)->ensureHostOwnsTarget($listing['host'], 'sleeping_place', $otherListing['places']->first()->id);
    }

    public function test_preview_skips_calendar_opening_when_dates_have_bookings(): void
    {
        $listing = $this->listing(places: 2);
        $guest = User::factory()->create();
        Booking::factory()
            ->for($guest, 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['places'][0], 'sleepingPlace')
            ->create([
                'check_in_date' => '2026-07-02',
                'check_out_date' => '2026-07-03',
                'status' => BookingStatus::Confirmed,
            ]);

        $service = app(HostBulkActionService::class);
        $batch = $service->createBatch($listing['host'], 'open_dates', [
            ['type' => 'sleeping_place', 'id' => $listing['places'][0]->id],
            ['type' => 'sleeping_place', 'id' => $listing['places'][1]->id],
        ], [
            'range' => ['start' => '2026-07-01', 'end' => '2026-07-04'],
            'price' => 20,
        ]);

        $preview = $service->preview($batch);
        $processed = $service->process($service->confirm($batch));

        $this->assertSame(2, $preview['selected_count']);
        $this->assertSame(1, $preview['affected_count']);
        $this->assertSame(1, $preview['skipped_count']);
        $this->assertSame('completed', $processed->status);
        $this->assertSame(1, $processed->skipped_count);
        $this->assertDatabaseMissing('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['places'][0]->id,
            'date' => '2026-07-01',
        ]);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['places'][1]->id,
            'date' => '2026-07-01',
            'status' => 'available',
        ]);
    }

    public function test_clone_room_sleeping_place_and_identical_place_creation_follow_safe_copy_rules(): void
    {
        $listing = $this->listing();
        $room = $listing['room'];
        $place = $listing['places']->first();
        RoomTranslation::factory()->for($room)->create(['locale' => 'en', 'title' => 'Original room']);
        SleepingPlaceTranslation::factory()->for($place)->create(['locale' => 'en', 'title' => 'Original place']);
        Booking::factory()
            ->for(User::factory()->create(), 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($room)
            ->for($place, 'sleepingPlace')
            ->create(['status' => BookingStatus::Confirmed]);

        $clone = app(HostBulkCloneService::class);
        $roomClone = $clone->cloneRoom($room, ['copy_photos' => false]);
        $placeClone = $clone->cloneSleepingPlace($place, ['copy_price' => true, 'copy_calendar' => false]);
        $created = $clone->createIdenticalPlaces($room, 3, [
            'display_name' => 'Lower bed',
            'base_price_per_night' => 18,
            'currency' => 'EUR',
            'status' => SleepingPlaceStatus::Draft->value,
        ]);

        $this->assertNotSame($room->id, $roomClone->id);
        $this->assertSame($room->type, $roomClone->type);
        $this->assertSame(1, $roomClone->translations()->count());
        $this->assertSame(0, $roomClone->sleepingPlaces()->count());
        $this->assertNotSame($place->id, $placeClone->id);
        $this->assertSame('18.00', $created->first()->base_price_per_night);
        $this->assertSame(3, $created->count());
        $this->assertSame(0, $placeClone->bookings()->count());
        $this->assertNotNull($placeClone->fresh()->calendarSettings);
        $this->assertGreaterThan(0, $placeClone->calendarDays()->count());
        $this->assertGreaterThan(0, $created->first()->calendarDays()->count());
        $this->assertSame(1, $placeClone->translations()->count());
    }

    public function test_bulk_calendar_pricing_rules_cleaning_messages_and_publication_services_work(): void
    {
        $listing = $this->listing(places: 2);
        $places = $listing['places'];
        $guest = User::factory()->create(['name' => 'Bulk Guest']);
        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($places[0], 'sleepingPlace')
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-12',
                'status' => BookingStatus::Confirmed,
            ]);

        $price = app(HostBulkPricingService::class)->setWeeklyDiscount($places, 12);
        $calendar = app(HostBulkCalendarService::class)->closeDates($places, ['start' => '2026-07-15', 'end' => '2026-07-17'], 'repair');
        $rules = app(HostBulkRulesService::class)->updateHouseRules(collect([$listing['property']]), ['quiet_after_22']);
        app(HostBulkRulesService::class)->updateCheckInOutTimes(collect([$listing['property']]), [
            'check_in_time_from' => '14:00',
            'check_out_time_until' => '11:00',
            'self_check_in_available' => true,
        ]);
        $recipients = app(HostBulkMessageService::class)->previewRecipients($listing['host'], [
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
        ]);
        $messages = app(HostBulkMessageService::class)->sendToBookingGuests($listing['host'], collect([$booking, $booking]), 'Kitchen will be closed tonight.');
        $tasks = app(HostBulkCleaningService::class)->createCleaningTasks($places, [
            'user_id' => $listing['host']->id,
            'scheduled_date' => '2026-07-12',
            'scheduled_time' => '12:00',
            'reason' => 'after_checkout',
        ]);
        $publication = app(HostBulkPublicationService::class)->hideListings($places);

        $this->assertSame(2, $price['affected_count']);
        $this->assertSame('12.00', $places[0]->calendarSettings()->first()->weekly_discount_percent);
        $this->assertSame(2, $calendar['affected_count']);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $places[0]->id,
            'date' => '2026-07-15',
            'status' => 'blocked',
            'reason' => 'repair',
        ]);
        $this->assertSame(1, $rules['affected_count']);
        $this->assertSame(['quiet_after_22'], $listing['property']->fresh()->rules);
        $this->assertCount(1, $recipients);
        $this->assertSame(1, $messages['sent_count']);
        $this->assertSame(1, $messages['skipped_count']);
        $this->assertSame(2, $tasks->count());
        $this->assertSame(2, $publication['affected_count']);
        $this->assertSame('hidden', $places[0]->fresh()->publication_status);
    }

    public function test_bulk_actions_change_cleaning_fee_and_check_in_times(): void
    {
        $listing = $this->listing(places: 2);
        $places = $listing['places'];
        SleepingPlacePricingSetting::factory()->for($places[0], 'sleepingPlace')->create(['cleaning_fee' => 4]);

        $service = app(HostBulkActionService::class);
        $feeBatch = $service->createBatch($listing['host'], 'change_cleaning_fee', [
            ['type' => 'sleeping_place', 'id' => $places[0]->id],
            ['type' => 'sleeping_place', 'id' => $places[1]->id],
        ], [
            'target_type' => 'sleeping_place',
            'fee' => 16.50,
        ]);
        $timeBatch = $service->createBatch($listing['host'], 'change_check_in_time', [
            ['type' => 'sleeping_place', 'id' => $places[0]->id],
            ['type' => 'sleeping_place', 'id' => $places[1]->id],
        ], [
            'target_type' => 'sleeping_place',
            'check_in_time_from' => '13:30',
            'check_in_time_until' => '21:00',
            'check_out_time_until' => '10:30',
        ]);

        $feeResult = $service->process($service->confirm($feeBatch));
        $timeResult = $service->process($service->confirm($timeBatch));

        $this->assertSame('completed', $feeResult->status);
        $this->assertSame('16.50', $places[0]->fresh()->cleaning_fee);
        $this->assertDatabaseHas('sleeping_place_pricing_settings', [
            'sleeping_place_id' => $places[0]->id,
            'cleaning_fee' => 16.50,
        ]);
        $this->assertSame('completed', $timeResult->status);
        $this->assertDatabaseHas('sleeping_place_calendar_settings', [
            'sleeping_place_id' => $places[0]->id,
            'default_check_in_time' => '13:30',
            'check_in_time_from' => '13:30',
            'check_in_time_until' => '21:00',
            'default_check_out_time' => '10:30',
            'check_out_time_until' => '10:30',
        ]);
    }

    public function test_bulk_mark_occupied_writes_occupied_calendar_and_availability_rows(): void
    {
        $listing = $this->listing(places: 2);

        $batch = app(HostBulkActionService::class)->createBatch($listing['host'], 'mark_occupied', [
            ['type' => 'sleeping_place', 'id' => $listing['places'][0]->id],
            ['type' => 'sleeping_place', 'id' => $listing['places'][1]->id],
        ], [
            'target_type' => 'sleeping_place',
            'range' => ['start' => '2026-07-20', 'end' => '2026-07-22'],
            'reason' => 'host_bulk_occupied',
        ]);

        $processed = app(HostBulkActionService::class)->process(app(HostBulkActionService::class)->confirm($batch));

        $this->assertSame('completed', $processed->status);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['places'][0]->id,
            'date' => '2026-07-20',
            'status' => 'occupied',
            'reason' => 'host_bulk_occupied',
            'blocked_by_host' => true,
        ]);
        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $listing['places'][0]->id,
            'date' => '2026-07-20',
            'status' => 'occupied',
        ]);
    }

    public function test_bulk_rules_can_be_applied_to_sleeping_places(): void
    {
        $listing = $this->listing(places: 2);

        $batch = app(HostBulkActionService::class)->createBatch($listing['host'], 'change_rules', [
            ['type' => 'sleeping_place', 'id' => $listing['places'][0]->id],
            ['type' => 'sleeping_place', 'id' => $listing['places'][1]->id],
        ], [
            'target_type' => 'sleeping_place',
            'rules' => ['quiet_after_22', 'no_extra_guests'],
        ]);

        $processed = app(HostBulkActionService::class)->process(app(HostBulkActionService::class)->confirm($batch));

        $this->assertSame('completed', $processed->status);
        $this->assertDatabaseHas('sleeping_place_rules', [
            'sleeping_place_id' => $listing['places'][0]->id,
            'rule_key' => 'quiet_after_22',
            'sort_order' => 1,
            'status' => 'active',
        ]);
        $this->assertSame(2, SleepingPlaceRule::query()->where('sleeping_place_id', $listing['places'][0]->id)->count());
    }

    public function test_bulk_publication_activation_requires_readiness(): void
    {
        $listing = $this->listing(places: 3);
        $listing['places'][0]->forceFill(['publication_status' => 'published'])->save();
        $listing['places'][1]->forceFill(['publication_status' => 'draft', 'base_price_per_night' => 0])->save();
        $listing['places'][2]->forceFill([
            'status' => SleepingPlaceStatus::Hidden->value,
            'publication_status' => 'hidden',
            'base_price_per_night' => 20,
        ])->save();

        $result = app(HostBulkPublicationService::class)->activateListings($listing['places']);

        $this->assertSame(2, $result['affected_count']);
        $this->assertSame(1, $result['skipped_count']);
        $this->assertSame('published', $listing['places'][0]->fresh()->publication_status);
        $this->assertSame('draft', $listing['places'][1]->fresh()->publication_status);
        $this->assertSame('published', $listing['places'][2]->fresh()->publication_status);
        $this->assertSame(SleepingPlaceStatus::Active, $listing['places'][2]->fresh()->status);
    }

    public function test_host_bulk_livewire_panel_applies_selected_sleeping_place_action(): void
    {
        $listing = $this->listing(places: 2);

        Livewire::actingAs($listing['host'])
            ->test(HostBulkActionsPanel::class)
            ->set('actionType', 'change_cleaning_fee')
            ->set('targetType', 'sleeping_place')
            ->set('selectedTargetIds', [$listing['places'][0]->id, $listing['places'][1]->id])
            ->set('cleaningFee', '14.75')
            ->call('applyBulkAction')
            ->assertHasNoErrors()
            ->assertSet('noticeKey', 'host_bulk.messages.completed');

        $this->assertSame('14.75', $listing['places'][0]->fresh()->cleaning_fee);
    }

    public function test_host_bulk_livewire_panel_keeps_preview_and_result_summaries_out_of_public_state(): void
    {
        $listing = $this->listing(places: 2);

        $component = Livewire::actingAs($listing['host'])
            ->test(HostBulkActionsPanel::class)
            ->set('actionType', 'change_cleaning_fee')
            ->set('targetType', 'sleeping_place')
            ->set('selectedTargetIds', [$listing['places'][0]->id, $listing['places'][1]->id])
            ->set('cleaningFee', '14.75')
            ->call('previewBulkAction')
            ->assertHasNoErrors()
            ->assertSee(__('host_bulk.preview'))
            ->assertSee(__('host_bulk.messages.affected_count', ['count' => 2]));

        $previewSnapshotData = $component->snapshot['data'] ?? [];

        $this->assertIsArray($previewSnapshotData);
        $this->assertArrayHasKey('lastBatchId', $previewSnapshotData);
        $this->assertArrayNotHasKey('preview', $previewSnapshotData);
        $this->assertArrayNotHasKey('result', $previewSnapshotData);

        $component
            ->call('applyBulkAction')
            ->assertHasNoErrors()
            ->assertSee(__('host_bulk.result'))
            ->assertSee(__('host_bulk.messages.affected_count', ['count' => 2]));

        $resultSnapshotData = $component->snapshot['data'] ?? [];

        $this->assertIsArray($resultSnapshotData);
        $this->assertArrayHasKey('lastBatchId', $resultSnapshotData);
        $this->assertArrayNotHasKey('preview', $resultSnapshotData);
        $this->assertArrayNotHasKey('result', $resultSnapshotData);
    }

    public function test_bulk_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();

        foreach ($this->componentClasses() as $component) {
            Livewire::actingAs($listing['host'])
                ->test($component)
                ->assertSee(__('host_bulk.title'));
        }

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(HostBulkActionsPanel::class)
            ->assertSee(__('host_bulk.title', [], 'ru'))
            ->assertSee(__('host_bulk.choose_action', [], 'ru'));
    }

    public function test_host_bulk_route_renders_for_authenticated_host(): void
    {
        $listing = $this->listing();

        $this->actingAs($listing['host'])
            ->get(route('host.bulk.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSee(__('host_bulk.title', [], 'en'))
            ->assertSee(__('host_bulk.actions.change_price', [], 'en'));
    }

    /**
     * @return array{host: User, property: Property, room: Room, places: Collection<int, SleepingPlace>}
     */
    private function listing(int $places = 1): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'rules' => ['quiet_hours'],
                'publication_status' => 'published',
            ]);
        $room = Room::factory()->for($property)->create([
            'rules' => ['quiet_hours'],
            'room_rules_text' => 'Keep quiet after 22:00.',
        ]);
        $sleepingPlaces = SleepingPlace::factory()
            ->count($places)
            ->for($property)
            ->for($room)
            ->sequence(fn ($sequence): array => [
                'place_number' => (string) ($sequence->index + 1),
                'display_name' => 'Place '.($sequence->index + 1),
            ])
            ->create([
                'base_price_per_night' => 20,
                'currency' => 'EUR',
                'publication_status' => 'published',
            ]);

        return [
            'host' => $host,
            'property' => $property,
            'room' => $room,
            'places' => $sleepingPlaces,
        ];
    }

    /**
     * @return list<class-string<Component>>
     */
    private function componentClasses(): array
    {
        return [
            HostBulkActionsPanel::class,
            HostBulkTargetSelector::class,
            HostBulkActionPicker::class,
            HostBulkPreview::class,
            HostBulkConfirm::class,
            HostBulkResult::class,
            CloneRoomAction::class,
            CloneSleepingPlaceAction::class,
            CreateIdenticalPlacesAction::class,
            BulkPriceEditor::class,
            BulkCalendarEditor::class,
            BulkRulesEditor::class,
            BulkMessageGuests::class,
            BulkCleaningEditor::class,
            BulkPublicationEditor::class,
        ];
    }
}
