<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\Hints\DismissHostHintButton;
use App\Livewire\Host\Hints\HostBeforePublishChecklist;
use App\Livewire\Host\Hints\HostHintsPanel;
use App\Livewire\Host\Hints\HostListingQualityScore;
use App\Livewire\Host\Hints\HostWizardHints;
use App\Models\AvailabilityDay;
use App\Models\City;
use App\Models\Country;
use App\Models\HostHintAction;
use App\Models\HostHintDismissal;
use App\Models\HostHintSnapshot;
use App\Models\MediaItem;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\HostHints\HostHintDismissalService;
use App\Services\HostHints\HostHintService;
use App\Services\HostHints\HostListingQualityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AutomaticHostHintsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_hint_tables_models_relationships_and_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('host_hint_snapshots'));
        $this->assertTrue(Schema::hasTable('host_hint_dismissals'));
        $this->assertTrue(Schema::hasTable('host_hint_actions'));
        $this->assertTrue(Schema::hasColumn('host_hint_snapshots', 'message_params_json'));
        $this->assertTrue(Schema::hasColumn('host_hint_snapshots', 'show_before_publish'));
        $this->assertTrue(Schema::hasColumn('host_hint_dismissals', 'dismissed_until'));
        $this->assertTrue(Schema::hasColumn('host_hint_actions', 'action_status'));
        $this->assertTrue(Schema::hasIndex('host_hint_snapshots', ['user_id', 'status']));
        $this->assertTrue(Schema::hasIndex('host_hint_snapshots', ['sleeping_place_id', 'status']));
        $this->assertTrue(Schema::hasIndex('host_hint_dismissals', ['user_id', 'hint_key']));
        $this->assertTrue(Schema::hasIndex('host_hint_actions', ['host_hint_snapshot_id']));

        $listing = $this->listing();
        $host = $listing['host'];
        $hint = HostHintSnapshot::factory()->forTarget($host, $listing['property'], $listing['room'], $listing['place'])->create();
        $dismissal = HostHintDismissal::factory()->forTarget($host, $listing['property'], $listing['room'], $listing['place'])->create();
        $action = HostHintAction::factory()->for($host, 'user')->for($hint, 'snapshot')->create();

        $this->assertSame($host->id, $hint->user->id);
        $this->assertSame($listing['property']->id, $hint->property->id);
        $this->assertSame($listing['room']->id, $hint->room->id);
        $this->assertSame($listing['place']->id, $dismissal->sleepingPlace->id);
        $this->assertSame($hint->id, $action->snapshot->id);

        $listing['place']->delete();

        $this->assertDatabaseMissing('host_hint_snapshots', ['id' => $hint->id]);
        $this->assertDatabaseMissing('host_hint_dismissals', ['id' => $dismissal->id]);
        $this->assertDatabaseMissing('host_hint_actions', ['id' => $action->id]);
    }

    public function test_refresh_hints_for_sleeping_place_detects_missing_host_listing_details(): void
    {
        $listing = $this->listing(
            propertyOverrides: [
                'emergency_contact_name' => null,
                'emergency_contact_phone' => null,
                'access_instructions' => null,
            ],
            roomOverrides: [
                'room_rules_text' => null,
                'current_guests_count' => 0,
                'occupied_places_count' => 0,
            ],
            placeOverrides: [
                'base_price_per_night' => 95,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'weekly_price' => null,
                'monthly_price' => null,
                'cancellation_policy' => null,
                'has_locker' => false,
            ],
        );
        SleepingPlace::factory()
            ->for($listing['property'])
            ->for($listing['room'])
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'base_price_per_night' => 30,
                'cleaning_fee' => 5,
                'deposit_amount' => 20,
            ]);

        app(HostHintService::class)->refreshHintsForSleepingPlace($listing['place']);

        $keys = HostHintSnapshot::query()
            ->where('sleeping_place_id', $listing['place']->id)
            ->pluck('hint_key')
            ->all();

        $this->assertContains('add_main_sleeping_place_photo', $keys);
        $this->assertContains('add_room_photos', $keys);
        $this->assertContains('add_locker_info', $keys);
        $this->assertContains('add_current_occupants_count', $keys);
        $this->assertContains('price_above_area_average', $keys);
        $this->assertContains('missing_cleaning_fee', $keys);
        $this->assertContains('missing_deposit', $keys);
        $this->assertContains('missing_cancellation_policy', $keys);
        $this->assertContains('missing_check_in_time', $keys);
        $this->assertContains('missing_check_out_time', $keys);
        $this->assertContains('missing_key_pickup_method', $keys);
        $this->assertContains('missing_kitchen_rules', $keys);
        $this->assertContains('missing_bathroom_rules', $keys);
        $this->assertContains('missing_emergency_contact', $keys);
        $this->assertContains('calendar_not_open', $keys);
    }

    public function test_quality_score_publish_readiness_and_completion_actions_work(): void
    {
        $listing = $this->listing(placeOverrides: [
            'deposit_amount' => 0,
            'cleaning_fee' => 0,
            'cancellation_policy' => null,
        ]);

        $quality = app(HostListingQualityService::class);

        $this->assertLessThan(100, $quality->calculateCompletionScore($listing['place']));
        $this->assertContains('photos', $quality->getMissingRequiredFields($listing['place']));
        $this->assertContains('deposit', $quality->getMissingRecommendedFields($listing['place']));
        $this->assertContains('photos', $quality->getCriticalIssues($listing['place']));
        $this->assertFalse($quality->getPublishReadiness($listing['place'])['ready']);

        $hint = HostHintSnapshot::factory()->forTarget($listing['host'], $listing['property'], $listing['room'], $listing['place'])->create([
            'hint_key' => 'add_main_sleeping_place_photo',
            'status' => 'active',
        ]);

        app(HostHintService::class)->markAsCompleted($hint);

        $this->assertDatabaseHas('host_hint_snapshots', [
            'id' => $hint->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('host_hint_actions', [
            'host_hint_snapshot_id' => $hint->id,
            'action' => 'completed',
            'action_status' => 'done',
        ]);
    }

    public function test_optional_hint_can_be_dismissed_but_critical_publish_hint_cannot_be_hidden_permanently(): void
    {
        $listing = $this->listing();
        $optional = HostHintSnapshot::factory()->forTarget($listing['host'], $listing['property'], $listing['room'], $listing['place'])->create([
            'hint_key' => 'add_kitchen_photos',
            'importance' => 'low',
            'show_before_publish' => false,
        ]);
        $critical = HostHintSnapshot::factory()->forTarget($listing['host'], $listing['property'], $listing['room'], $listing['place'])->create([
            'hint_key' => 'add_main_sleeping_place_photo',
            'importance' => 'critical',
            'show_before_publish' => true,
        ]);

        app(HostHintDismissalService::class)->dismiss($listing['host'], $optional, null);
        app(HostHintDismissalService::class)->remindLater($listing['host'], $critical, now()->addDay());

        $this->assertTrue(app(HostHintDismissalService::class)->isDismissed($listing['host'], $optional, 'dashboard'));
        $this->assertTrue(app(HostHintDismissalService::class)->isDismissed($listing['host'], $critical, 'wizard'));
        $this->assertFalse(app(HostHintDismissalService::class)->isDismissed($listing['host'], $critical, 'before_publish'));
    }

    public function test_host_hint_livewire_components_render_in_english_and_russian(): void
    {
        $listing = $this->listing(placeOverrides: ['deposit_amount' => 0]);
        app(HostHintService::class)->refreshHintsForSleepingPlace($listing['place']);

        Livewire::actingAs($listing['host'])
            ->test(HostHintsPanel::class)
            ->assertSee(__('host_hints.dashboard_title'))
            ->assertSee(__('host_hints.messages.add_main_sleeping_place_photo'));

        Livewire::actingAs($listing['host'])
            ->test(HostWizardHints::class, [
                'targetType' => 'sleeping_place',
                'targetId' => $listing['place']->id,
                'step' => 'photos',
            ])
            ->assertSee(__('host_hints.messages.add_main_sleeping_place_photo'));

        Livewire::actingAs($listing['host'])
            ->test(HostBeforePublishChecklist::class, [
                'sleepingPlaceId' => $listing['place']->id,
            ])
            ->assertSee(__('host_hints.before_publish'));

        Livewire::actingAs($listing['host'])
            ->test(HostListingQualityScore::class, [
                'targetType' => 'sleeping_place',
                'targetId' => $listing['place']->id,
            ])
            ->assertSee(__('host_hints.quality_title'));

        app()->setLocale('ru');

        Livewire::actingAs($listing['host'])
            ->test(HostHintsPanel::class)
            ->assertSee(__('host_hints.dashboard_title', [], 'ru'));
    }

    public function test_dismiss_host_hint_button_records_dismissal_and_refuses_critical_before_publish(): void
    {
        $listing = $this->listing();
        $optional = HostHintSnapshot::factory()->forTarget($listing['host'], $listing['property'], $listing['room'], $listing['place'])->create([
            'importance' => 'low',
            'show_before_publish' => false,
        ]);
        $critical = HostHintSnapshot::factory()->forTarget($listing['host'], $listing['property'], $listing['room'], $listing['place'])->create([
            'importance' => 'critical',
            'show_before_publish' => true,
        ]);

        Livewire::actingAs($listing['host'])
            ->test(DismissHostHintButton::class, [
                'hintId' => $optional->id,
                'context' => 'dashboard',
            ])
            ->call('dismiss')
            ->assertDispatched('host-hint-dismissed');

        $this->assertDatabaseHas('host_hint_dismissals', [
            'user_id' => $listing['host']->id,
            'hint_key' => $optional->hint_key,
        ]);

        Livewire::actingAs($listing['host'])
            ->test(DismissHostHintButton::class, [
                'hintId' => $critical->id,
                'context' => 'before_publish',
            ])
            ->call('dismiss')
            ->assertHasErrors(['hint']);
    }

    /**
     * @param  array<string, mixed>  $propertyOverrides
     * @param  array<string, mixed>  $roomOverrides
     * @param  array<string, mixed>  $placeOverrides
     * @return array{host: User, property: Property, room: Room, place: SleepingPlace}
     */
    private function listing(array $propertyOverrides = [], array $roomOverrides = [], array $placeOverrides = [], bool $withMedia = false, bool $withCalendar = false): array
    {
        [$country, $region, $city] = $this->geo();
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create(array_merge([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $country->id,
                'region_id' => $region->id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Old Town',
                'status' => PropertyStatus::Active,
                'access_instructions' => null,
                'emergency_contact_name' => null,
                'emergency_contact_phone' => null,
            ], $propertyOverrides));
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Host hint property',
            'summary' => 'Shared stay.',
            'description' => 'Shared stay.',
            'house_rules_text' => null,
            'check_in_instructions' => null,
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Объект с подсказками',
            'summary' => 'Совместное проживание.',
            'description' => 'Совместное проживание.',
            'house_rules_text' => null,
            'check_in_instructions' => null,
        ]);

        $room = Room::factory()
            ->for($property)
            ->create(array_merge([
                'status' => RoomStatus::Active,
                'room_rules_text' => null,
                'current_guests_count' => 0,
                'occupied_places_count' => 0,
            ], $roomOverrides));

        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Draft,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'weekly_price' => null,
                'monthly_price' => null,
                'cancellation_policy' => null,
            ], $placeOverrides));
        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Lower bed',
            'summary' => 'A lower bed.',
            'description' => 'A lower bed.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Нижняя кровать',
            'summary' => 'Нижнее место.',
            'description' => 'Нижнее место.',
        ]);

        if ($withMedia) {
            $this->media($place, $host, 'gallery');
            $this->media($room, $host, 'gallery');
            $this->media($property, $host, 'bathroom');
            $this->media($property, $host, 'kitchen');
        }

        if ($withCalendar) {
            foreach (range(1, 20) as $offset) {
                AvailabilityDay::factory()->for($place)->create([
                    'date' => now()->addDays($offset)->toDateString(),
                    'status' => AvailabilityStatus::Available,
                ]);
            }
        }

        return compact('host', 'property', 'room', 'place');
    }

    /**
     * @return array{0: Country, 1: Region, 2: City}
     */
    private function geo(): array
    {
        $country = Country::query()->firstOrCreate(
            ['code' => 'LT'],
            [
                'iso2' => 'LT',
                'iso3' => 'LTU',
                'name' => 'Lithuania',
                'name_en' => 'Lithuania',
                'name_ru' => 'Литва',
                'status' => Country::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );
        $region = Region::query()->firstOrCreate(
            ['country_id' => $country->id, 'code' => 'VL'],
            ['name' => 'Vilnius County', 'source' => 'geonames', 'source_id' => '864389'],
        );
        $city = City::query()->firstOrCreate(
            ['country_id' => $country->id, 'name' => 'Vilnius'],
            [
                'region_id' => $region->id,
                'geoname_id' => 593116,
                'ascii_name' => 'Vilnius',
                'latitude' => 54.68916,
                'longitude' => 25.2798,
                'population' => 542366,
                'timezone' => 'Europe/Vilnius',
                'feature_class' => 'P',
                'feature_code' => 'PPL',
                'status' => City::STATUS_ACTIVE,
                'is_active' => true,
            ],
        );

        return [$country, $region, $city];
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
