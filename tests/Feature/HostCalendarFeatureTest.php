<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PayoutStatus;
use App\Livewire\Host\Calendar\HostCalendarCleaningSheet;
use App\Livewire\Host\Calendar\HostCalendarDayDetails;
use App\Livewire\Host\Calendar\HostCalendarDayList;
use App\Livewire\Host\Calendar\HostCalendarEventCard;
use App\Livewire\Host\Calendar\HostCalendarFilters as HostCalendarFiltersComponent;
use App\Livewire\Host\Calendar\HostCalendarNoteSheet;
use App\Livewire\Host\Calendar\HostCalendarObjectSelector;
use App\Livewire\Host\Calendar\HostCalendarOccupancyPanel;
use App\Livewire\Host\Calendar\HostCalendarPage;
use App\Livewire\Host\Calendar\HostCalendarPayoutsPanel;
use App\Livewire\Host\Calendar\HostCalendarPriceEditor;
use App\Livewire\Host\Calendar\HostCalendarQuickActions;
use App\Livewire\Host\Calendar\HostCalendarRepairSheet;
use App\Livewire\Host\Calendar\HostCalendarSummary;
use App\Models\Booking;
use App\Models\HostCalendarEvent;
use App\Models\HostCalendarNote;
use App\Models\HostCalendarViewSetting;
use App\Models\HostCleaningTask;
use App\Models\Payout;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use App\Services\HostCalendar\Data\HostCalendarContext;
use App\Services\HostCalendar\Data\HostCalendarFilters;
use App\Services\HostCalendar\HostCalendarCleaningService;
use App\Services\HostCalendar\HostCalendarNoteService;
use App\Services\HostCalendar\HostCalendarOccupancyService;
use App\Services\HostCalendar\HostCalendarPriceService;
use App\Services\HostCalendar\HostCalendarRepairService;
use App\Services\HostCalendar\HostCalendarService;
use App\Services\HostCalendar\HostCalendarSnapshotService;
use App\Services\HostCalendar\HostCalendarViewSettingsService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use Tests\TestCase;

class HostCalendarFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_calendar_tables_models_relationships_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('host_calendar_events'));
        $this->assertTrue(Schema::hasTable('host_calendar_notes'));
        $this->assertTrue(Schema::hasTable('host_calendar_view_settings'));
        $this->assertTrue(Schema::hasIndex('host_calendar_events', ['user_id', 'event_date']));
        $this->assertTrue(Schema::hasIndex('host_calendar_events', ['user_id', 'event_type', 'event_date']));
        $this->assertTrue(Schema::hasIndex('host_calendar_notes', ['user_id', 'note_date']));
        $this->assertTrue(Schema::hasIndex('host_calendar_view_settings', ['user_id']));

        $listing = $this->listing();
        $booking = $this->booking($listing);
        $task = HostCleaningTask::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->for($booking)
            ->create();

        $event = HostCalendarEvent::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->for($booking)
            ->for($task, 'cleaningTask')
            ->create();
        $note = HostCalendarNote::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->for($booking)
            ->create();
        $settings = HostCalendarViewSetting::factory()
            ->for($listing['host'], 'user')
            ->for($listing['property'], 'defaultProperty')
            ->for($listing['room'], 'defaultRoom')
            ->create();

        $this->assertSame($listing['host']->id, $event->user->id);
        $this->assertSame($listing['place']->id, $event->sleepingPlace->id);
        $this->assertSame($task->id, $event->cleaningTask->id);
        $this->assertSame($booking->id, $note->booking->id);
        $this->assertSame($listing['property']->id, $settings->defaultProperty->id);
    }

    public function test_booking_snapshots_create_living_picture_events_and_cancelled_booking_clears_them(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        Payout::factory()
            ->for($listing['host'], 'host')
            ->for($booking)
            ->create([
                'scheduled_date' => '2026-07-13',
                'net_amount' => 120,
                'status' => PayoutStatus::Pending,
            ]);

        $created = app(HostCalendarSnapshotService::class)->refreshForBooking($booking);

        $this->assertGreaterThanOrEqual(4, $created);
        $this->assertDatabaseHas('host_calendar_events', [
            'user_id' => $listing['host']->id,
            'booking_id' => $booking->id,
            'event_type' => 'check_in',
            'event_date' => '2026-07-10',
            'guest_display_name' => 'Calendar Guest',
            'place_status' => 'booked',
        ]);
        $this->assertDatabaseHas('host_calendar_events', [
            'user_id' => $listing['host']->id,
            'booking_id' => $booking->id,
            'event_type' => 'check_out',
            'event_date' => '2026-07-12',
            'needs_cleaning' => true,
        ]);
        $this->assertDatabaseHas('host_calendar_events', [
            'booking_id' => $booking->id,
            'event_type' => 'payout',
            'event_date' => '2026-07-13',
            'payout_status' => PayoutStatus::Pending->value,
            'payout_amount' => 120,
        ]);

        $booking->forceFill(['status' => BookingStatus::CancelledByGuest])->save();
        $deleted = app(HostCalendarSnapshotService::class)->refreshForBooking($booking);

        $this->assertSame(0, $deleted);
        $this->assertDatabaseMissing('host_calendar_events', ['booking_id' => $booking->id]);
    }

    public function test_cleaning_repair_price_and_payout_events_appear_in_the_calendar(): void
    {
        $listing = $this->listing();
        $booking = $this->booking($listing);
        $task = app(HostCalendarCleaningService::class)->createCleaningAfterCheckout($booking);
        SleepingPlaceCalendarDay::factory()
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'date' => '2026-07-11',
                'price' => 27,
                'currency' => 'EUR',
                'source' => 'host_price',
            ]);

        app(HostCalendarSnapshotService::class)->refreshForCleaningTask($task);
        app(HostCalendarSnapshotService::class)->refreshForSleepingPlace($listing['place']);
        $repair = app(HostCalendarRepairService::class)->createRepairEvent($listing['host'], $listing['place'], [
            'start' => '2026-07-14',
            'end' => '2026-07-16',
        ], 'Mattress replacement');

        $doneTask = app(HostCalendarCleaningService::class)->markCleaningDone($task);

        $this->assertSame('done', $doneTask->status);
        $this->assertDatabaseHas('host_calendar_events', [
            'cleaning_task_id' => $task->id,
            'event_type' => 'cleaning',
            'event_status' => 'done',
        ]);
        $this->assertDatabaseHas('host_calendar_events', [
            'sleeping_place_id' => $listing['place']->id,
            'event_type' => 'price',
            'event_date' => '2026-07-11',
            'price_amount' => 27,
        ]);
        $this->assertSame('repair', $repair->event_type);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-07-14',
            'status' => 'repair',
            'reason' => 'repair',
        ]);
    }

    public function test_filters_summary_day_details_and_occupancy_are_host_scoped(): void
    {
        $listing = $this->listing(places: 2);
        $other = $this->listing();
        $booking = $this->booking($listing);
        $otherBooking = $this->booking($other);
        app(HostCalendarSnapshotService::class)->refreshForBooking($booking);
        app(HostCalendarSnapshotService::class)->refreshForBooking($otherBooking);

        $filters = new HostCalendarFilters(
            propertyId: $listing['property']->id,
            eventTypes: ['check_in'],
        );
        $range = ['start' => '2026-07-10', 'end' => '2026-07-13'];
        $events = app(HostCalendarService::class)->getEvents($listing['host'], $range, $filters);
        $summary = app(HostCalendarService::class)->getSummary($listing['host'], $range, new HostCalendarFilters);
        $day = app(HostCalendarService::class)->getDayDetails($listing['host'], '2026-07-10', new HostCalendarContext(
            range: $range,
            filters: $filters,
        ));
        $propertyOccupancy = app(HostCalendarOccupancyService::class)->getPropertyOccupancy($listing['property'], $range);
        $roomOccupancy = app(HostCalendarOccupancyService::class)->getRoomOccupancy($listing['room'], $range);

        $this->assertCount(1, $events);
        $this->assertSame('check_in', $events->first()->event_type);
        $this->assertSame(1, $summary->checkInsCount);
        $this->assertSame(1, $summary->checkOutsCount);
        $this->assertSame(1, $day->events->count());
        $this->assertSame(2, $propertyOccupancy->totalPlaces);
        $this->assertSame(1, $propertyOccupancy->occupiedPlaces);
        $this->assertSame(50, $propertyOccupancy->occupancyPercent);
        $this->assertSame(50, $roomOccupancy->occupancyPercent);
    }

    public function test_notes_view_settings_prices_and_permissions_are_host_safe(): void
    {
        $listing = $this->listing();
        $other = $this->listing();
        $noteService = app(HostCalendarNoteService::class);
        $note = $noteService->createNote($listing['host'], [
            'property_id' => $listing['property']->id,
            'room_id' => $listing['room']->id,
            'sleeping_place_id' => $listing['place']->id,
            'note_date' => '2026-07-10',
            'note_type' => 'inspection',
            'note' => 'Check locker key.',
        ]);
        $updated = $noteService->updateNote($listing['host'], $note, ['note' => 'Check locker and lamp.']);
        $settings = app(HostCalendarViewSettingsService::class)->updateForUser($listing['host'], [
            'default_view' => 'cleaning',
            'default_property_id' => $listing['property']->id,
            'default_room_id' => $listing['room']->id,
            'show_prices' => false,
        ]);
        app(HostCalendarPriceService::class)->changePrice($listing['host'], $listing['place'], '2026-07-10', 31, 'EUR');

        $visible = app(HostCalendarService::class)->getEvents($listing['host'], ['start' => '2026-07-10', 'end' => '2026-07-11'], new HostCalendarFilters);
        $otherVisible = app(HostCalendarService::class)->getEvents($other['host'], ['start' => '2026-07-10', 'end' => '2026-07-11'], new HostCalendarFilters);

        $this->assertSame('Check locker and lamp.', $updated->note);
        $this->assertSame('cleaning', $settings->default_view);
        $this->assertFalse($settings->show_prices);
        $this->assertTrue($visible->contains('event_type', 'price'));
        $this->assertFalse($otherVisible->contains('sleeping_place_id', $listing['place']->id));

        $this->expectException(AuthorizationException::class);
        $noteService->updateNote($other['host'], $note, ['note' => 'Nope.']);
    }

    public function test_quick_actions_mark_cleaning_done_change_price_and_require_confirmation_for_dangerous_actions(): void
    {
        $listing = $this->listing();
        $task = app(HostCalendarCleaningService::class)->markNeedsCleaning($listing['place'], '2026-07-20');

        Livewire::actingAs($listing['host'])
            ->test(HostCalendarQuickActions::class)
            ->call('prepareAction', 'close_date')
            ->assertSet('needsConfirmation', true)
            ->call('changePrice', $listing['place']->id, '2026-07-20', 34, 'EUR')
            ->assertHasNoErrors()
            ->call('markCleaningDone', $task->id)
            ->assertHasNoErrors();

        $this->assertSame('done', $task->fresh()->status);
        $this->assertDatabaseHas('sleeping_place_calendar_days', [
            'sleeping_place_id' => $listing['place']->id,
            'date' => '2026-07-20',
            'price' => 34,
        ]);
    }

    public function test_host_calendar_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing();

        foreach ($this->componentClasses() as $component) {
            Livewire::actingAs($listing['host'])
                ->test($component)
                ->assertSee(__('host_calendar.title'));
        }

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(HostCalendarPage::class)
            ->assertSee(__('host_calendar.title', [], 'ru'))
            ->assertSee(__('host_calendar.summary.check_ins_today', ['count' => 0], 'ru'));
    }

    /**
     * @return array{host:User, property:Property, room:Room, place:SleepingPlace}
     */
    private function listing(int $places = 1): array
    {
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'publication_status' => 'published',
            ]);
        $room = Room::factory()->for($property)->create(['publication_status' => 'published']);
        $place = SleepingPlace::factory()
            ->count($places)
            ->for($property)
            ->for($room)
            ->sequence(fn ($sequence): array => ['display_name' => 'Place '.($sequence->index + 1)])
            ->create([
                'base_price_per_night' => 20,
                'currency' => 'EUR',
                'publication_status' => 'published',
            ])
            ->first();

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
    private function booking(array $listing): Booking
    {
        return Booking::factory()
            ->for(User::factory()->create(['name' => 'Calendar Guest']), 'guest')
            ->for($listing['host'], 'host')
            ->for($listing['property'])
            ->for($listing['room'])
            ->for($listing['place'], 'sleepingPlace')
            ->create([
                'check_in_date' => '2026-07-10',
                'check_out_date' => '2026-07-12',
                'nights_count' => 2,
                'total_amount' => 140,
                'currency' => 'EUR',
                'status' => BookingStatus::Confirmed,
            ]);
    }

    /**
     * @return list<class-string<Component>>
     */
    private function componentClasses(): array
    {
        return [
            HostCalendarPage::class,
            HostCalendarFiltersComponent::class,
            HostCalendarSummary::class,
            HostCalendarDayList::class,
            HostCalendarDayDetails::class,
            HostCalendarEventCard::class,
            HostCalendarObjectSelector::class,
            HostCalendarQuickActions::class,
            HostCalendarPriceEditor::class,
            HostCalendarNoteSheet::class,
            HostCalendarCleaningSheet::class,
            HostCalendarRepairSheet::class,
            HostCalendarPayoutsPanel::class,
            HostCalendarOccupancyPanel::class,
        ];
    }
}
