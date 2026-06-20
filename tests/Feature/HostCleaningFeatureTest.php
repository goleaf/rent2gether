<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Livewire\Host\Cleaning\HostCleaningCalendarBlockPanel;
use App\Livewire\Host\Cleaning\HostCleaningChecklist;
use App\Livewire\Host\Cleaning\HostCleaningFilters;
use App\Livewire\Host\Cleaning\HostCleaningFindingSheet;
use App\Livewire\Host\Cleaning\HostCleaningPage;
use App\Livewire\Host\Cleaning\HostCleaningPhotoUploader;
use App\Livewire\Host\Cleaning\HostCleaningResponsibleSheet;
use App\Livewire\Host\Cleaning\HostCleaningSummary;
use App\Livewire\Host\Cleaning\HostCleaningTaskCard;
use App\Livewire\Host\Cleaning\HostCleaningTaskDetailsSheet;
use App\Livewire\Host\Cleaning\HostCleaningTemplateEditor;
use App\Models\Booking;
use App\Models\BookingForgottenItem;
use App\Models\HostCleaningFinding;
use App\Models\HostCleaningTask;
use App\Models\HostCleaningTaskItem;
use App\Models\HostCleaningTaskPhoto;
use App\Models\HostCleaningTemplate;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\CheckOut\BookingCheckOutService;
use App\Services\HostCleaning\HostCleaningCalendarService;
use App\Services\HostCleaning\HostCleaningChecklistService;
use App\Services\HostCleaning\HostCleaningFindingService;
use App\Services\HostCleaning\HostCleaningPhotoService;
use App\Services\HostCleaning\HostCleaningReadinessService;
use App\Services\HostCleaning\HostCleaningTaskService;
use App\Services\HostCleaning\HostCleaningTemplateService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class HostCleaningFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-06-20 10:00:00');
    }

    public function test_cleaning_tables_models_relationships_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('host_cleaning_tasks'));
        $this->assertTrue(Schema::hasTable('host_cleaning_task_items'));
        $this->assertTrue(Schema::hasTable('host_cleaning_task_photos'));
        $this->assertTrue(Schema::hasTable('host_cleaning_findings'));
        $this->assertTrue(Schema::hasTable('host_cleaning_templates'));
        $this->assertTrue(Schema::hasColumn('host_cleaning_tasks', 'cleaning_type'));
        $this->assertTrue(Schema::hasColumn('host_cleaning_tasks', 'booking_check_out_id'));
        $this->assertTrue(Schema::hasColumn('host_cleaning_tasks', 'assigned_person_name'));
        $this->assertTrue(Schema::hasIndex('host_cleaning_tasks', ['user_id', 'scheduled_date']));
        $this->assertTrue(Schema::hasIndex('host_cleaning_tasks', ['booking_check_out_id']));
        $this->assertTrue(Schema::hasIndex('host_cleaning_task_items', ['host_cleaning_task_id', 'item_key']));
        $this->assertTrue(Schema::hasIndex('host_cleaning_task_photos', ['host_cleaning_task_id', 'photo_type']));
        $this->assertTrue(Schema::hasIndex('host_cleaning_findings', ['host_cleaning_task_id', 'status']));
        $this->assertTrue(Schema::hasIndex('host_cleaning_templates', ['user_id', 'cleaning_type']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        $task = HostCleaningTask::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->for($booking)
            ->for($checkOut, 'bookingCheckOut')
            ->create(['cleaning_type' => 'after_check_out']);
        $item = HostCleaningTaskItem::factory()->for($task, 'task')->create();
        $photo = HostCleaningTaskPhoto::factory()->for($task, 'task')->for($listing['host'], 'uploadedBy')->create();
        $finding = HostCleaningFinding::factory()->for($task, 'task')->for($booking)->create();
        $template = HostCleaningTemplate::factory()->for($listing['host'], 'user')->create();

        $this->assertSame($listing['host']->id, $task->user->id);
        $this->assertSame($checkOut->id, $task->bookingCheckOut->id);
        $this->assertSame($item->id, $task->items->first()->id);
        $this->assertSame($photo->id, $task->photos->first()->id);
        $this->assertSame($finding->id, $task->findings->first()->id);
        $this->assertSame($listing['host']->id, $template->user->id);
    }

    public function test_cleaning_task_can_be_created_after_checkout_with_default_checklist_and_calendar_block(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);

        $task = app(HostCleaningTaskService::class)->createAfterCheckout($checkOut);

        $this->assertSame('after_check_out', $task->cleaning_type);
        $this->assertSame('needed', $task->status);
        $this->assertSame($booking->id, $task->booking_id);
        $this->assertGreaterThanOrEqual(20, $task->items()->count());
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-06-24',
            'status' => 'cleaning',
            'reason' => 'after_checkout',
        ]);
        $this->assertDatabaseHas('host_calendar_events', [
            'cleaning_task_id' => $task->id,
            'event_type' => 'cleaning',
            'event_status' => 'needed',
        ]);

        $sameTask = app(HostCleaningTaskService::class)->createAfterCheckout($checkOut);

        $this->assertSame($task->id, $sameTask->id);
    }

    public function test_start_complete_readiness_photos_and_responsible_person_work_without_cleaner_role(): void
    {
        Storage::fake('public');

        $listing = $this->listing();
        $task = app(HostCleaningTaskService::class)->createTask($listing['host'], [
            'property_id' => $listing['property']->id,
            'room_id' => $listing['room']->id,
            'sleeping_place_id' => $listing['place']->id,
            'cleaning_type' => 'before_check_in',
            'scheduled_date' => '2026-06-20',
            'scheduled_time' => '13:00',
            'before_photos_required' => true,
            'after_photos_required' => true,
        ]);
        app(HostCleaningTaskService::class)->assignResponsible($listing['host'], $task, [
            'assigned_to_type' => 'external_person',
            'assigned_person_name' => 'Marta',
            'assigned_person_contact' => '+37060000000',
        ]);
        app(HostCleaningTaskService::class)->start($listing['host'], $task);

        $this->expectException(ValidationException::class);
        app(HostCleaningTaskService::class)->complete($listing['host'], $task->fresh());
    }

    public function test_cleaning_can_complete_after_required_items_and_photos_are_done(): void
    {
        Storage::fake('public');

        $listing = $this->listing();
        $task = app(HostCleaningTaskService::class)->createTask($listing['host'], [
            'property_id' => $listing['property']->id,
            'room_id' => $listing['room']->id,
            'sleeping_place_id' => $listing['place']->id,
            'cleaning_type' => 'before_check_in',
            'scheduled_date' => '2026-06-20',
            'before_photos_required' => true,
            'after_photos_required' => true,
        ]);
        app(HostCleaningTaskService::class)->start($listing['host'], $task);
        app(HostCleaningPhotoService::class)->addBeforePhoto($listing['host'], $task, UploadedFile::fake()->image('before.jpg'));
        app(HostCleaningPhotoService::class)->addAfterPhoto($listing['host'], $task, UploadedFile::fake()->image('after.jpg'));

        foreach ($task->items()->pluck('item_key') as $itemKey) {
            app(HostCleaningChecklistService::class)->markItemCompleted($listing['host'], $task, $itemKey);
        }

        $completed = app(HostCleaningTaskService::class)->complete($listing['host'], $task->fresh());

        $this->assertSame('done', $completed->status);
        $this->assertTrue($completed->place_ready_after_cleaning);
        $this->assertTrue(app(HostCleaningReadinessService::class)->canMarkPlaceReady($completed));
        $this->assertDatabaseHas('host_calendar_events', [
            'cleaning_task_id' => $task->id,
            'event_status' => 'done',
            'needs_cleaning' => false,
        ]);
    }

    public function test_findings_keep_place_unready_create_forgotten_item_flow_and_block_calendar(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $checkOut = app(BookingCheckOutService::class)->createForBooking($booking);
        $task = app(HostCleaningTaskService::class)->createAfterCheckout($checkOut);

        $finding = app(HostCleaningFindingService::class)->reportFinding($listing['host'], $task, [
            'finding_type' => 'forgotten_items',
            'severity' => 'medium',
            'description' => 'Blue scarf in locker.',
            'needs_host_action' => true,
            'needs_guest_notification' => true,
        ]);
        $repairFinding = app(HostCleaningFindingService::class)->reportFinding($listing['host'], $task, [
            'finding_type' => 'damage',
            'severity' => 'high',
            'description' => 'Broken lamp near bed.',
            'needs_repair' => true,
            'needs_deposit_review' => true,
        ]);

        app(HostCleaningFindingService::class)->createForgottenItemRecordIfNeeded($finding);
        app(HostCleaningFindingService::class)->createRepairTaskIfNeeded($repairFinding);
        app(HostCleaningCalendarService::class)->releaseCalendarAfterCleaning($task->fresh());

        $this->assertTrue($task->fresh()->has_forgotten_items);
        $this->assertTrue($task->fresh()->needs_repair);
        $this->assertFalse(app(HostCleaningReadinessService::class)->canMarkPlaceReady($task->fresh()));
        $this->assertSame(1, BookingForgottenItem::query()->where('booking_id', $booking->id)->count());
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-06-24',
            'status' => 'repair',
        ]);
    }

    public function test_templates_filters_summary_permissions_and_livewire_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        $other = $this->listing();
        $template = app(HostCleaningTemplateService::class)->createTemplate($listing['host'], [
            'name' => 'After checkout default',
            'cleaning_type' => 'after_check_out',
            'target_type' => 'sleeping_place',
            'items' => [
                ['item_key' => 'replace_bedding', 'required' => true],
                ['item_key' => 'upload_after_photos', 'required' => true],
            ],
            'is_default' => true,
        ]);
        $task = app(HostCleaningTaskService::class)->createTask($listing['host'], [
            'property_id' => $listing['property']->id,
            'room_id' => $listing['room']->id,
            'sleeping_place_id' => $listing['place']->id,
            'cleaning_type' => 'after_check_out',
            'scheduled_date' => '2026-06-20',
            'status' => 'needed',
        ]);

        app(HostCleaningChecklistService::class)->applyTemplate($task, $template);
        app(HostCleaningTaskService::class)->markOverdueTasks($listing['host']);

        $this->assertSame(2, $task->items()->count());
        $this->assertSame($template->id, app(HostCleaningTemplateService::class)->getDefaultTemplate($listing['host'], 'after_check_out', 'sleeping_place')?->id);

        $this->expectException(AuthorizationException::class);
        app(HostCleaningTaskService::class)->start($other['host'], $task);
    }

    public function test_cleaning_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();
        $task = app(HostCleaningTaskService::class)->createTask($listing['host'], [
            'property_id' => $listing['property']->id,
            'room_id' => $listing['room']->id,
            'sleeping_place_id' => $listing['place']->id,
            'cleaning_type' => 'after_check_out',
            'scheduled_date' => '2026-06-20',
        ]);

        foreach ($this->componentClasses() as $component) {
            Livewire::actingAs($listing['host'])
                ->test($component, ['taskId' => $task->id])
                ->assertSee(__('cleaning.title'));
        }

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(HostCleaningPage::class)
            ->assertSee(__('cleaning.title', [], 'ru'))
            ->assertSee(__('cleaning.actions.start', [], 'ru'));
    }

    /**
     * @return array{guest:User, host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(): array
    {
        $guest = User::factory()->create(['name' => 'Cleaning Guest']);
        $host = User::factory()->create(['is_host' => true, 'name' => 'Cleaning Host']);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'title' => 'Cleaning Home',
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
            HostCleaningPage::class,
            HostCleaningFilters::class,
            HostCleaningSummary::class,
            HostCleaningTaskCard::class,
            HostCleaningTaskDetailsSheet::class,
            HostCleaningChecklist::class,
            HostCleaningPhotoUploader::class,
            HostCleaningFindingSheet::class,
            HostCleaningResponsibleSheet::class,
            HostCleaningTemplateEditor::class,
            HostCleaningCalendarBlockPanel::class,
        ];
    }
}
