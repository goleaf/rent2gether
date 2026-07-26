<?php

namespace Tests\Feature;

use App\Data\Favorites\FavoriteContext;
use App\Enums\AvailabilityStatus;
use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Favorites\CreateCollectionSheet;
use App\Livewire\Favorites\FavoriteCard;
use App\Livewire\Favorites\FavoriteCollectionPage;
use App\Livewire\Favorites\FavoriteCollectionsList;
use App\Livewire\Favorites\FavoritesPage;
use App\Livewire\Favorites\FavoriteToggle;
use App\Livewire\Favorites\MoveFavoriteSheet;
use App\Models\AvailabilityDay;
use App\Models\City;
use App\Models\Country;
use App\Models\Favorite;
use App\Models\FavoriteCollection;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Favorites\FavoriteAvailabilityService;
use App\Services\Favorites\FavoriteCardPresenter;
use App\Services\Favorites\FavoriteCardQuery;
use App\Services\Favorites\FavoriteCollectionService;
use App\Services\Favorites\FavoriteReminderService;
use App\Services\Favorites\FavoriteService;
use App\Services\Favorites\FavoriteSnapshotService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class FavoritesCollectionsFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_collections_and_custom_collection_are_created(): void
    {
        $guest = User::factory()->create();
        $service = app(FavoriteCollectionService::class);

        $service->ensureDefaultCollections($guest);

        $this->assertSame(9, $guest->favoriteCollections()->default()->count());

        Livewire::actingAs($guest)
            ->test(CreateCollectionSheet::class)
            ->set('title', 'Work shortlist')
            ->set('description', 'Close to office')
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('favorite_collections', [
            'user_id' => $guest->id,
            'title' => 'Work shortlist',
            'type' => 'custom',
        ]);
    }

    public function test_default_collection_titles_are_translated_at_render_time(): void
    {
        $guest = User::factory()->create();

        app()->setLocale('en');
        app(FavoriteCollectionService::class)->ensureDefaultCollections($guest);

        app()->setLocale('ru');

        Livewire::actingAs($guest)
            ->test(FavoriteCollectionsList::class)
            ->assertSee('Дешевые варианты')
            ->assertDontSee('Cheap options');
    }

    public function test_favorite_adds_snapshot_and_prevents_duplicates(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Snapshot place');
        $collection = FavoriteCollection::factory()->for($guest)->create();
        $service = app(FavoriteService::class);

        $context = new FavoriteContext(
            source: 'search',
            checkIn: '2026-07-10',
            checkOut: '2026-07-13',
            guestsCount: 1,
            personalNote: 'Quiet option',
        );

        $first = $service->add($guest, $place->id, $collection->id, $context);
        $second = $service->add($guest, $place->id, $collection->id, $context);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Favorite::query()->where('user_id', $guest->id)->where('sleeping_place_id', $place->id)->count());
        $this->assertSame($collection->id, $second->favorite_collection_id);
        $this->assertNotNull($second->total_price_snapshot);
        $this->assertSame('Quiet option', $second->personal_note);
    }

    public function test_collection_delete_does_not_delete_sleeping_place_and_user_delete_cascades(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Cascade place');
        $collection = FavoriteCollection::factory()->for($guest)->create();

        $favorite = app(FavoriteService::class)->add($guest, $place->id, $collection->id, new FavoriteContext);

        $collection->delete();

        $this->assertModelExists($place);
        $this->assertNull($favorite->refresh()->favorite_collection_id);

        $guest->delete();

        $this->assertDatabaseMissing('favorites', ['id' => $favorite->id]);
    }

    public function test_policies_restrict_favorites_to_owner(): void
    {
        $guest = User::factory()->create();
        $other = User::factory()->create();
        $collection = FavoriteCollection::factory()->for($guest)->create();
        $favorite = Favorite::factory()->for($guest)->forCollection($collection)->create(['bed_id' => null]);

        $this->assertTrue(Gate::forUser($guest)->allows('view', $collection));
        $this->assertFalse(Gate::forUser($other)->allows('view', $collection));
        $this->assertTrue(Gate::forUser($guest)->allows('update', $favorite));
        $this->assertFalse(Gate::forUser($other)->allows('update', $favorite));

        $this->actingAs($other)
            ->get(route('favorites.collections.show', ['locale' => 'en', 'favoriteCollection' => $collection]))
            ->assertForbidden();
    }

    public function test_favorite_updates_note_priority_status_and_reminder(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Action place');
        $favorite = app(FavoriteService::class)->add($guest, $place->id, null, new FavoriteContext);

        $service = app(FavoriteService::class);
        $service->updateNote($guest, $favorite->id, 'Ask about locker');
        $service->updatePriority($guest, $favorite->id, 'high');
        $service->updateDecisionStatus($guest, $favorite->id, 'almost_chosen');

        $favorite->refresh();

        $this->assertSame('Ask about locker', $favorite->personal_note);
        $this->assertSame(9, $favorite->priority);
        $this->assertSame('almost_chosen', $favorite->decision_status);

        $reminders = app(FavoriteReminderService::class);
        $reminders->schedule($guest, $favorite, CarbonImmutable::now()->addDay(), 'Check price');

        $this->assertNotNull($favorite->refresh()->remind_at);
        $this->assertCount(0, $reminders->dueReminders($guest));

        $favorite->update(['remind_at' => now()->subMinute(), 'reminder_sent_at' => null]);

        $this->assertCount(1, $reminders->dueReminders($guest));

        $reminders->cancel($guest, $favorite->refresh());

        $this->assertNull($favorite->refresh()->remind_at);
    }

    public function test_favorite_card_actions_update_organization_state(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Card action place');
        $favorite = app(FavoriteService::class)->add($guest, $place->id, null, new FavoriteContext);
        $card = app(FavoriteCardPresenter::class)
            ->presentMany(app(FavoriteCardQuery::class)->forFavorite($guest, $favorite->id)->get())[0];

        Livewire::actingAs($guest)
            ->test(FavoriteCard::class, ['card' => $card])
            ->call('openMoveSheet')
            ->assertSet('moveSheetOpen', true)
            ->call('openNoteSheet')
            ->assertSet('noteSheetOpen', true)
            ->call('openReminderSheet')
            ->assertSet('reminderSheetOpen', true)
            ->call('setPriority', 'high')
            ->call('setDecisionStatus', 'almost_chosen')
            ->assertHasNoErrors();

        $favorite->refresh();

        $this->assertSame(9, $favorite->priority);
        $this->assertSame('almost_chosen', $favorite->decision_status);
    }

    public function test_move_favorite_sheet_moves_only_to_owned_active_collection(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Move sheet place');
        $source = FavoriteCollection::factory()->for($guest)->create(['title' => 'Source']);
        $target = FavoriteCollection::factory()->for($guest)->create(['title' => 'Target']);
        $favorite = app(FavoriteService::class)->add($guest, $place->id, $source->id, new FavoriteContext);

        Livewire::actingAs($guest)
            ->test(MoveFavoriteSheet::class, ['favoriteId' => $favorite->id])
            ->assertSet('collectionId', $source->id)
            ->set('collectionId', $target->id)
            ->call('move')
            ->assertHasNoErrors();

        $this->assertSame($target->id, $favorite->refresh()->favorite_collection_id);
    }

    public function test_price_and_availability_changes_are_detected(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Changing place', ['base_price_per_night' => 40, 'cleaning_fee' => 0, 'deposit_amount' => 0]);
        $favorite = app(FavoriteService::class)->add($guest, $place->id, null, new FavoriteContext(
            checkIn: '2026-07-10',
            checkOut: '2026-07-13',
        ));

        $place->update(['base_price_per_night' => 25]);

        $favorite = app(FavoriteSnapshotService::class)->refresh($favorite);

        $this->assertTrue($favorite->price_changed);
        $this->assertLessThan(0, (float) $favorite->price_change_amount);

        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::BlockedByHost,
        ]);

        $result = app(FavoriteAvailabilityService::class)->check($favorite->refresh());

        $this->assertFalse($result->isAvailable);
        $this->assertTrue($result->becameUnavailable);
    }

    public function test_collection_page_and_toggle_render(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Rendered place');
        $collection = FavoriteCollection::factory()->for($guest)->create(['title' => 'Rendered collection']);

        app(FavoriteService::class)->add($guest, $place->id, $collection->id, new FavoriteContext);

        Livewire::actingAs($guest)
            ->test(FavoriteCollectionPage::class, ['favoriteCollection' => $collection])
            ->assertSee('Rendered collection')
            ->assertSee('Rendered place')
            ->call('setFilter', 'available')
            ->assertHasNoErrors()
            ->call('setSort', 'high_priority')
            ->assertHasNoErrors();

        Livewire::actingAs($guest)
            ->test(FavoriteToggle::class, ['sleepingPlaceId' => $place->id, 'source' => 'search'])
            ->assertSet('selected', true)
            ->call('toggle')
            ->assertSet('selected', false);
    }

    public function test_favorite_collection_query_indexes_exist(): void
    {
        foreach ([
            'favorites_user_added_index',
            'favorites_collection_added_index',
            'favorites_collection_available_added_index',
            'favorites_collection_price_changed_added_index',
            'favorites_collection_current_price_index',
        ] as $index) {
            $this->assertTrue(Schema::hasIndex('favorites', $index), $index.' is missing.');
        }
    }

    public function test_favorites_summary_uses_one_aggregate_lookup_instead_of_repeated_counts(): void
    {
        $guest = User::factory()->create();

        Favorite::factory()->for($guest)->create([
            'bed_id' => null,
            'sleeping_place_id' => null,
            'is_currently_available' => true,
            'price_changed' => true,
            'became_available_again' => false,
            'remind_at' => now()->addDay(),
            'reminder_sent_at' => null,
        ]);
        Favorite::factory()->for($guest)->create([
            'bed_id' => null,
            'sleeping_place_id' => null,
            'is_currently_available' => false,
            'price_changed' => false,
            'became_available_again' => true,
            'remind_at' => null,
            'reminder_sent_at' => null,
        ]);
        Favorite::factory()->for(User::factory())->create([
            'bed_id' => null,
            'sleeping_place_id' => null,
            'is_currently_available' => true,
            'price_changed' => true,
            'became_available_again' => true,
        ]);

        $summaryCountQueries = 0;
        DB::listen(static function ($query) use (&$summaryCountQueries): void {
            $sql = strtolower($query->sql);

            if (str_starts_with($sql, 'select count(*) as "aggregate" from "favorites"')) {
                $summaryCountQueries++;
            }
        });

        $component = Livewire::actingAs($guest)
            ->test(FavoritesPage::class)
            ->assertSee(__('favorites.summary.total'));

        $this->assertSame([
            'total' => 2,
            'available' => 1,
            'price_changed' => 1,
            'available_again' => 1,
            'reminders' => 1,
        ], $component->instance()->summary());
        $this->assertLessThanOrEqual(1, $summaryCountQueries, 'Favorites summary should avoid repeated count queries on every render.');
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     */
    private function createPlace(string $title, array $placeOverrides = []): SleepingPlace
    {
        $city = $this->city('Vilnius');
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'region_id' => $city->region_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Old Town',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
                'kitchens_count' => 1,
            ]);
        $room = Room::factory()
            ->for($property)
            ->create([
                'status' => RoomStatus::Active,
                'type' => RoomType::Shared,
                'gender_policy' => GenderType::NoRestriction,
                'occupied_places_count' => 1,
            ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'base_price_per_night' => 30,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                ...$placeOverrides,
            ]);

        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => $title,
            'description' => $title,
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'RU '.$title,
            'summary' => 'RU '.$title,
            'description' => 'RU '.$title,
        ]);

        return $place;
    }

    private function city(string $name): City
    {
        $country = Country::factory()->create(['name_en' => 'Lithuania']);
        $region = Region::factory()->for($country)->create(['name' => $name.' County']);

        return City::factory()
            ->for($country)
            ->for($region)
            ->create([
                'name' => $name,
                'ascii_name' => $name,
                'population' => 500000,
                'status' => City::STATUS_ACTIVE,
                'is_active' => true,
            ]);
    }
}
