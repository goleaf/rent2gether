<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Favorites\FavoriteCard;
use App\Models\City;
use App\Models\Country;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Favorites\FavoriteCardQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FavoriteCardPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorite_card_keeps_display_payload_out_of_public_state(): void
    {
        $component = Livewire::test(FavoriteCard::class, [
            'card' => $this->cardPayload(
                favoriteId: 123,
                placeId: 456,
                note: 'Private shortlist note stays render-only.',
            ),
        ])
            ->assertSee('Private shortlist note stays render-only.')
            ->assertSee('Compact favorite place');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('favoriteId', $encodedSnapshot);
        $this->assertStringContainsString('placeId', $encodedSnapshot);
        $this->assertStringNotContainsString('"card"', $encodedSnapshot);
        $this->assertStringNotContainsString('listing_card', $encodedSnapshot);
        $this->assertStringNotContainsString('Private shortlist note stays render-only.', $encodedSnapshot);
        $this->assertStringNotContainsString('Compact favorite place', $encodedSnapshot);
    }

    public function test_favorite_card_compare_action_still_uses_compact_place_id_after_hydration(): void
    {
        $guest = User::factory()->create();
        $place = $this->createPlace('Hydrated favorite place');
        $favorite = Favorite::factory()
            ->for($guest)
            ->for($place, 'sleepingPlace')
            ->create([
                'bed_id' => null,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'personal_note' => 'Hydrated note',
                'note' => 'Hydrated note',
            ]);

        Livewire::actingAs($guest)
            ->test(FavoriteCard::class, [
                'card' => $this->cardPayload(
                    favoriteId: $favorite->id,
                    placeId: $place->id,
                    note: 'Hydrated note',
                ),
            ])
            ->call('toggleCompare')
            ->assertSet('selectedForCompare', true)
            ->assertDispatched('favorite-compare-toggled', sleepingPlaceId: $place->id);
    }

    public function test_favorite_card_query_scopes_to_owner_and_eager_loads_card_graph(): void
    {
        $guest = User::factory()->create();
        $otherGuest = User::factory()->create();
        $place = $this->createPlace('Scoped favorite place');
        $favorite = Favorite::factory()
            ->for($guest)
            ->for($place, 'sleepingPlace')
            ->create([
                'bed_id' => null,
                'property_id' => $place->property_id,
                'room_id' => $place->room_id,
                'personal_note' => 'Scoped note',
                'note' => 'Scoped note',
            ]);

        $query = app(FavoriteCardQuery::class);
        $loaded = $query->forUser($guest)->whereKey($favorite->id)->first();

        $this->assertInstanceOf(Favorite::class, $loaded);
        $this->assertTrue($loaded->relationLoaded('sleepingPlace'));
        $this->assertTrue($loaded->sleepingPlace->relationLoaded('translations'));
        $this->assertTrue($loaded->sleepingPlace->relationLoaded('room'));
        $this->assertTrue($loaded->sleepingPlace->relationLoaded('property'));
        $this->assertTrue($loaded->sleepingPlace->property->relationLoaded('cityModel'));
        $this->assertTrue($loaded->sleepingPlace->relationLoaded('cardMedia'));
        $this->assertArrayHasKey('sleeping_place_id', $loaded->getAttributes());
        $this->assertArrayNotHasKey('nearest_available_dates_json', $loaded->getAttributes());
        $this->assertNull($query->forUser($otherGuest)->whereKey($favorite->id)->first());
    }

    /**
     * @return array<string, mixed>
     */
    private function cardPayload(int $favoriteId, int $placeId, string $note): array
    {
        return [
            'id' => $favoriteId,
            'favorite_id' => $favoriteId,
            'collection_id' => null,
            'listing_card' => null,
            'place_id' => $placeId,
            'title' => 'Compact favorite place',
            'location' => 'Vilnius',
            'image' => null,
            'image_alt' => 'Compact favorite place',
            'url' => route('places.show', ['locale' => 'en', 'sleepingPlace' => $placeId]),
            'book_url' => route('places.book', ['locale' => 'en', 'sleepingPlace' => $placeId]),
            'price_per_night' => 'EUR 30',
            'total_price' => 'EUR 90',
            'deposit' => 'EUR 0',
            'rating' => __('listing.detail.summary.no_reviews'),
            'availability_state' => 'available',
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-13',
            'guests_count' => 1,
            'price_state' => 'same',
            'price_change' => null,
            'note' => $note,
            'priority_label' => __('favorites.priorities.normal'),
            'decision_status_label' => __('favorites.decision_statuses.saved'),
            'dates' => __('favorites.no_dates'),
            'room_type' => __('listing.detail.values.not_set'),
            'sleeping_place_type' => __('listing.detail.values.not_set'),
        ];
    }

    private function createPlace(string $title): SleepingPlace
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
            ]);

        $place->translations()->create([
            'locale' => 'en',
            'title' => $title,
            'summary' => $title,
            'description' => $title,
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
