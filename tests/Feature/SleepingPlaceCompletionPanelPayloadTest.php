<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Host\SleepingPlaces\SleepingPlaceCompletionPanel;
use App\Models\City;
use App\Models\Country;
use App\Models\Property;
use App\Models\Region;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SleepingPlaceCompletionPanelPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_sleeping_place_completion_panel_keeps_items_out_of_public_state(): void
    {
        $place = $this->createPlace('Completion payload place');
        $place->load('property.host');

        $component = Livewire::actingAs($place->property->host)
            ->test(SleepingPlaceCompletionPanel::class, [
                'sleepingPlace' => $place,
            ])
            ->assertSee(__('sleeping_place.completion.title'))
            ->assertSee(__('sleeping_place.completion.items.title'))
            ->assertSee('%');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('sleepingPlaceId', $encodedSnapshot);
        $this->assertStringNotContainsString('items', $encodedSnapshot);
        $this->assertStringNotContainsString(__('sleeping_place.completion.items.title'), $encodedSnapshot);
        $this->assertStringNotContainsString('percentage', $encodedSnapshot);
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
