<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Livewire\Host\Listings\Steps\CalendarStep;
use App\Livewire\Host\Listings\Steps\PublishStep;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\PropertyAccessDetail;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\SleepingPlaceCalendarRule;
use App\Models\SleepingPlaceCalendarSetting;
use App\Models\User;
use App\Services\Calendar\CalendarAvailabilityService;
use App\Services\Calendar\CalendarCleaningGapService;
use App\Services\Calendar\CalendarPriceService;
use App\Services\Calendar\CalendarRuleService;
use App\Services\HostListings\Wizard\HostCalendarDraftService;
use App\Services\HostListings\Wizard\HostListingPublishService;
use App\Services\HostListings\Wizard\HostListingReadinessService;
use App\Services\HostListings\Wizard\HostSleepingPlaceDraftService;
use App\Services\HostListings\Wizard\ListingPublicationCheckService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class ListingCalendarAndPublishFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_tables_models_relationships_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('sleeping_place_calendar_settings'));
        $this->assertTrue(Schema::hasTable('sleeping_place_calendar_days'));
        $this->assertTrue(Schema::hasTable('sleeping_place_calendar_rules'));
        $this->assertTrue(Schema::hasColumn('sleeping_place_calendar_days', 'blocked_by_host'));
        $this->assertTrue(Schema::hasIndex('sleeping_place_calendar_settings', ['sleeping_place_id']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_calendar_days', ['sleeping_place_id', 'date']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_calendar_days', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('sleeping_place_calendar_rules', ['sleeping_place_id', 'rule_type']));

        $listing = $this->listing();
        $setting = SleepingPlaceCalendarSetting::factory()->for($listing['place'], 'sleepingPlace')->create();
        $day = SleepingPlaceCalendarDay::factory()->for($listing['place'], 'sleepingPlace')->create();
        $rule = SleepingPlaceCalendarRule::factory()->for($listing['place'], 'sleepingPlace')->create();

        $this->assertSame($listing['place']->id, $setting->sleepingPlace->id);
        $this->assertSame($listing['place']->id, $day->sleepingPlace->id);
        $this->assertSame($listing['place']->id, $rule->sleepingPlace->id);
        $this->assertSame($setting->id, $listing['place']->calendarSettings->id);
        $this->assertTrue($listing['place']->calendarDays->contains($day));
        $this->assertTrue($listing['place']->calendarRules->contains($rule));
    }

    public function test_host_can_open_close_price_and_restrict_calendar_dates(): void
    {
        $listing = $this->listing();
        $calendar = app(HostCalendarDraftService::class);

        $settings = $calendar->updateSettings($listing['host'], $listing['place'], [
            'default_price' => 20,
            'currency' => 'EUR',
            'min_nights' => 2,
            'max_nights' => 30,
            'weekly_discount_percent' => 10,
            'cleaning_gap_hours' => 4,
            'cleaning_gap_days' => 1,
            'check_in_time_from' => '14:00',
            'check_in_time_until' => '22:00',
            'check_out_time_until' => '11:00',
        ]);

        $opened = $calendar->openDatesForPlace($listing['host'], $listing['place'], [
            'start' => '2026-07-01',
            'end' => '2026-07-05',
        ], [
            'price' => 20,
            'min_nights' => 2,
            'max_nights' => 30,
        ]);

        $calendar->setPriceForDates($listing['host'], $listing['place'], [
            'start' => '2026-07-03',
            'end' => '2026-07-04',
        ], 30);
        $calendar->setCheckInDays($listing['host'], $listing['place'], [1, 2, 3, 4, 5]);
        $calendar->setCheckOutDays($listing['host'], $listing['place'], [5, 6, 7]);
        $calendar->setMinMaxNights($listing['host'], $listing['place'], 3, 21);
        $calendar->setCleaningGap($listing['host'], $listing['place'], 6, 2);
        $closed = $calendar->closeDatesForPlace($listing['host'], $listing['place'], [
            'start' => '2026-07-04',
            'end' => '2026-07-05',
        ], 'repair');

        $this->assertSame(4, $opened);
        $this->assertSame(1, $closed);
        $this->assertSame('EUR', $settings->currency);
        $this->assertSame(4, $listing['place']->calendarDays()->count());
        $this->assertSame(4, $listing['place']->availabilityDays()->count());
        $this->assertSame('30.00', $listing['place']->calendarDays()->whereDate('date', '2026-07-03')->first()->price);
        $this->assertSame(AvailabilityStatus::BlockedByHost, $listing['place']->availabilityDays()->whereDate('date', '2026-07-04')->first()->status);
        $this->assertSame('blocked', $listing['place']->calendarDays()->whereDate('date', '2026-07-04')->first()->status);
        $this->assertSame('repair', $listing['place']->calendarDays()->whereDate('date', '2026-07-04')->first()->reason);
        $this->assertSame(2, $listing['place']->fresh()->cleaning_gap_days);
        $this->assertSame(3, $listing['place']->fresh()->min_nights);
        $this->assertSame(21, $listing['place']->fresh()->max_nights);
        $this->assertDatabaseHas('sleeping_place_calendar_rules', ['rule_type' => 'check_in_days']);
        $this->assertDatabaseHas('sleeping_place_calendar_rules', ['rule_type' => 'check_out_days']);
    }

    public function test_sleeping_place_creation_automatically_bootstraps_calendar_per_place(): void
    {
        $listing = $this->listing();
        $room = $listing['room'];
        app(HostCalendarDraftService::class)->updateSettings($listing['host'], $listing['place'], [
            'default_price' => 20,
            'currency' => 'EUR',
        ]);
        app(HostCalendarDraftService::class)->openDatesForPlace($listing['host'], $listing['place'], [
            'start' => now()->addDay()->toDateString(),
            'end' => now()->addDays(4)->toDateString(),
        ], ['price' => 20]);

        $place = app(HostSleepingPlaceDraftService::class)->createSleepingPlace($room, [
            'display_name' => 'Auto calendar place',
            'place_number' => '1',
            'base_price_per_night' => 24,
            'currency' => 'EUR',
            'min_nights' => 2,
            'max_nights' => 30,
        ]);
        $readiness = app(HostListingReadinessService::class)->checkPublishReadiness($listing['property']->fresh());
        $blockingKeys = collect($readiness['blocking'])->pluck('check_key')->all();

        $this->assertNotNull($place->fresh()->calendarSettings);
        $this->assertGreaterThan(0, $place->calendarDays()->where('status', 'available')->count());
        $this->assertSame(
            $place->calendarDays()->where('status', 'available')->count(),
            $place->availabilityDays()->where('status', AvailabilityStatus::Available->value)->count(),
        );
        $this->assertNotContains('missing_calendar_settings', $blockingKeys);
        $this->assertNotContains('missing_available_dates', $blockingKeys);
    }

    public function test_calendar_availability_booking_blocks_and_cleaning_gap(): void
    {
        $listing = $this->listing();
        $calendar = app(HostCalendarDraftService::class);
        $calendar->updateSettings($listing['host'], $listing['place'], [
            'default_price' => 20,
            'currency' => 'EUR',
            'cleaning_gap_days' => 1,
        ]);
        $calendar->openDatesForPlace($listing['host'], $listing['place'], [
            'start' => '2026-07-10',
            'end' => '2026-07-15',
        ], ['price' => 20]);

        $booking = Booking::factory()->for($listing['host'], 'host')->for($listing['property'])->for($listing['room'])->for($listing['place'], 'sleepingPlace')->create([
            'host_user_id' => $listing['host']->id,
            'property_id' => $listing['property']->id,
            'room_id' => $listing['room']->id,
            'sleeping_place_id' => $listing['place']->id,
            'check_in_date' => '2026-07-11',
            'check_out_date' => '2026-07-13',
            'status' => BookingStatus::Confirmed,
        ]);

        $availability = app(CalendarAvailabilityService::class);
        $cleaning = app(CalendarCleaningGapService::class);
        $availability->applyBookingBlock($booking);
        $cleaning->blockAfterCheckout($booking);

        $this->assertFalse($availability->isAvailable($listing['place'], ['start' => '2026-07-11', 'end' => '2026-07-13']));
        $this->assertSame(['2026-07-11', '2026-07-12'], $availability->getUnavailableDates($listing['place'], ['start' => '2026-07-11', 'end' => '2026-07-13']));
        $this->assertSame('booked', $listing['place']->calendarDays()->whereDate('date', '2026-07-11')->first()->status);
        $this->assertSame('cleaning', $listing['place']->calendarDays()->whereDate('date', '2026-07-13')->first()->status);

        $availability->releaseBookingBlock($booking);
        $cleaning->releaseCleaningBlocks($booking);

        $this->assertTrue($availability->isAvailable($listing['place'], ['start' => '2026-07-11', 'end' => '2026-07-13']));
        $this->assertSame('available', $listing['place']->calendarDays()->whereDate('date', '2026-07-11')->first()->status);
        $this->assertSame(AvailabilityStatus::Available, $listing['place']->availabilityDays()->whereDate('date', '2026-07-11')->first()->status);
    }

    public function test_calendar_price_rules_and_discounts_are_resolved(): void
    {
        $listing = $this->listing();
        $calendar = app(HostCalendarDraftService::class);
        $calendar->updateSettings($listing['host'], $listing['place'], [
            'default_price' => 20,
            'currency' => 'EUR',
            'weekly_discount_percent' => 10,
            'monthly_discount_percent' => 20,
        ]);
        $calendar->openDatesForPlace($listing['host'], $listing['place'], [
            'start' => '2026-07-01',
            'end' => '2026-07-09',
        ], ['price' => 20]);
        $calendar->setPriceForDates($listing['host'], $listing['place'], [
            'start' => '2026-07-03',
            'end' => '2026-07-04',
        ], 30);
        app(CalendarRuleService::class)->createRule($listing['host'], $listing['place'], [
            'rule_type' => 'weekend_price',
            'weekdays_json' => [5, 6],
            'price' => 25,
            'priority' => 5,
        ]);

        $pricing = app(CalendarPriceService::class);

        $this->assertSame(30.0, $pricing->getPriceForDate($listing['place'], '2026-07-03'));
        $this->assertSame(25.0, $pricing->getPriceForDate($listing['place'], '2026-07-04'));
        $this->assertSame(139.5, $pricing->getTotalPrice($listing['place'], ['start' => '2026-07-01', 'end' => '2026-07-08']));
        $this->assertSame(20.0, $pricing->applyMonthlyDiscount($listing['place'], 25.0, 30));
        $this->assertContains('date_price_override', collect($pricing->explainPrice($listing['place'], ['start' => '2026-07-01', 'end' => '2026-07-08']))->pluck('reason')->all());
    }

    public function test_publication_checks_calendar_requirements_review_fields_and_authorization(): void
    {
        $listing = $this->listing();
        $otherHost = User::factory()->create(['is_host' => true]);

        $this->expectException(AuthorizationException::class);
        app(HostCalendarDraftService::class)->openDatesForPlace($otherHost, $listing['place'], [
            'start' => '2026-07-01',
            'end' => '2026-07-02',
        ]);
    }

    public function test_publication_is_blocked_until_calendar_and_required_fields_are_ready(): void
    {
        $listing = $this->listing();
        $this->makeReadyExceptCalendar($listing);

        $readiness = app(HostListingReadinessService::class)->checkPublishReadiness($listing['property']);
        $this->assertFalse($readiness['ready']);
        $this->assertContains('missing_calendar_settings', collect($readiness['blocking'])->pluck('check_key')->all());
        $this->assertContains('missing_available_dates', collect($readiness['blocking'])->pluck('check_key')->all());

        $this->expectException(ValidationException::class);
        app(HostListingPublishService::class)->publishIfReady($listing['host'], $listing['property']);
    }

    public function test_publication_succeeds_when_calendar_is_ready_and_review_comment_is_future_ready(): void
    {
        $listing = $this->listing();
        $this->makeReadyExceptCalendar($listing);
        app(HostCalendarDraftService::class)->updateSettings($listing['host'], $listing['place'], [
            'default_price' => 20,
            'currency' => 'EUR',
            'check_in_time_from' => '14:00',
            'check_out_time_until' => '11:00',
        ]);
        app(HostCalendarDraftService::class)->openDatesForPlace($listing['host'], $listing['place'], [
            'start' => '2026-07-01',
            'end' => '2026-07-05',
        ], ['price' => 20]);

        $review = app(HostListingPublishService::class)->requestPublication($listing['host'], $listing['property'], 'Please check the calendar.');
        $published = app(HostListingPublishService::class)->publishIfReady($listing['host'], $listing['property']);
        $checks = app(ListingPublicationCheckService::class)->getOpenBlockingChecks($listing['property']);

        $this->assertSame('pending_review', $review->publication_status);
        $this->assertSame('pending', $review->review_status);
        $this->assertSame('Please check the calendar.', $review->review_comment);
        $this->assertSame('published', $published->publication_status);
        $this->assertSame('auto_approved', $published->review_status);
        $this->assertTrue($checks->isEmpty());
    }

    public function test_calendar_and_publish_steps_render_in_english_and_russian(): void
    {
        $listing = $this->listing();

        Livewire::actingAs($listing['host'])
            ->test(CalendarStep::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_calendar.title'))
            ->assertSee(__('listing_calendar.quick_open'));

        Livewire::actingAs($listing['host'])
            ->test(PublishStep::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_publish.title'))
            ->assertSee(__('listing_publish.blocking_issues'));

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(CalendarStep::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_calendar.title', [], 'ru'));

        Livewire::actingAs($listing['host'])
            ->test(PublishStep::class, ['propertyId' => $listing['property']->id])
            ->assertSee(__('listing_publish.title', [], 'ru'));
    }

    /**
     * @return array{host: User, property: Property, room: Room, place: SleepingPlace}
     */
    private function listing(): array
    {
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'default_check_in_time' => '15:00',
            'default_check_out_time' => '11:00',
            'default_cancellation_policy' => 'flexible',
        ]);

        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => 'draft',
            'publication_status' => 'draft',
            'rules' => ['kitchen', 'bathroom', 'quiet'],
            'emergency_contact_name' => 'Host',
            'emergency_contact_phone' => '+37060000000',
        ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Calendar property',
            'description' => 'A calm shared room.',
            'house_rules_text' => 'Kitchen, bathroom and quiet rules are clear.',
        ]);
        PropertyAccessDetail::factory()->for($property)->create([
            'key_pickup_method' => 'meet_host',
            'emergency_contact_available' => true,
        ]);
        $room = Room::factory()->for($property)->create([
            'status' => 'draft',
            'publication_status' => 'draft',
            'room_rules_text' => 'Quiet after 22:00.',
            'sleeping_places_count' => 1,
        ]);
        $place = SleepingPlace::factory()->for($property)->for($room)->create([
            'status' => 'draft',
            'publication_status' => 'draft',
            'base_price_per_night' => 20,
            'deposit_amount' => 0,
            'cancellation_policy' => 'flexible',
        ]);

        return ['host' => $host, 'property' => $property, 'room' => $room, 'place' => $place];
    }

    /**
     * @param  array{host: User, property: Property, room: Room, place: SleepingPlace}  $listing
     */
    private function makeReadyExceptCalendar(array $listing): void
    {
        $this->media($listing['property'], $listing['host'], 'gallery');
        $this->media($listing['room'], $listing['host'], 'gallery');
        $this->media($listing['place'], $listing['host'], 'exact_place');
    }

    private function media(Model $model, User $host, string $collection): void
    {
        MediaItem::factory()->create([
            'owner_type' => $model::class,
            'owner_id' => $model->getKey(),
            'owner_user_id' => $host->id,
            'mediable_type' => $model::class,
            'mediable_id' => $model->getKey(),
            'collection' => $collection,
            'status' => 'active',
        ]);
    }
}
