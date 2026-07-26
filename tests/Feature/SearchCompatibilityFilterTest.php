<?php

namespace Tests\Feature;

use App\Enums\GenderType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RoomStatus;
use App\Enums\RoomType;
use App\Enums\SleepingPlaceStatus;
use App\Enums\SleepingPlaceType;
use App\Livewire\Search\SleepingPlaceSearch;
use App\Models\Amenity;
use App\Models\City;
use App\Models\CompatibilityResult;
use App\Models\Country;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SearchCompatibilityFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_by_cached_compatibility_fit_and_combines_with_amenity_filter(): void
    {
        $guest = User::factory()->create();
        $city = $this->city('Berlin');
        $wifi = $this->amenity('wifi', 'Wi-Fi');
        $checkIn = now()->addDays(14)->toDateString();
        $checkOut = now()->addDays(17)->toDateString();

        $matched = $this->createSearchPlace('Matched Compatibility Wi-Fi Place', $city);
        $matched->amenities()->attach($wifi->id);
        $goodWithoutWifi = $this->createSearchPlace('Matched Compatibility No Wi-Fi Place', $city);
        $blocked = $this->createSearchPlace('Blocked Compatibility Place', $city);

        $this->cacheCompatibility($guest, $matched, $checkIn, $checkOut, 'good', 78);
        $this->cacheCompatibility($guest, $goodWithoutWifi, $checkIn, $checkOut, 'great', 92);
        $this->cacheCompatibility($guest, $blocked, $checkIn, $checkOut, 'not_suitable', 18);

        Livewire::actingAs($guest)
            ->test(SleepingPlaceSearch::class)
            ->set('city', (string) $city->id)
            ->set('checkIn', $checkIn)
            ->set('checkOut', $checkOut)
            ->set('flexibleDates', true)
            ->set('minimumCompatibilityFit', 'good')
            ->assertSee('Matched Compatibility Wi-Fi Place')
            ->assertSee('Matched Compatibility No Wi-Fi Place')
            ->assertDontSee('Blocked Compatibility Place')
            ->set('wifi', true)
            ->assertSee('Matched Compatibility Wi-Fi Place')
            ->assertDontSee('Matched Compatibility No Wi-Fi Place')
            ->assertDontSee('Blocked Compatibility Place');
    }

    public function test_search_hides_not_suitable_results_and_ignores_invalid_fit_query_value(): void
    {
        $guest = User::factory()->create();
        $city = $this->city('Vilnius');
        $checkIn = now()->addDays(20)->toDateString();
        $checkOut = now()->addDays(24)->toDateString();

        $uncomfortable = $this->createSearchPlace('Uncomfortable But Allowed Place', $city);
        $blocked = $this->createSearchPlace('Important Conflict Place', $city);

        $this->cacheCompatibility($guest, $uncomfortable, $checkIn, $checkOut, 'uncomfortable', 42);
        $this->cacheCompatibility($guest, $blocked, $checkIn, $checkOut, 'not_suitable', 12);

        Livewire::actingAs($guest)
            ->test(SleepingPlaceSearch::class)
            ->set('city', (string) $city->id)
            ->set('checkIn', $checkIn)
            ->set('checkOut', $checkOut)
            ->set('flexibleDates', true)
            ->set('hideNotSuitableCompatibility', true)
            ->assertSee('Uncomfortable But Allowed Place')
            ->assertDontSee('Important Conflict Place');

        Livewire::actingAs($guest)
            ->test(SleepingPlaceSearch::class)
            ->set('city', (string) $city->id)
            ->set('checkIn', $checkIn)
            ->set('checkOut', $checkOut)
            ->set('flexibleDates', true)
            ->set('minimumCompatibilityFit', 'unexpected')
            ->assertSee('Uncomfortable But Allowed Place')
            ->assertSee('Important Conflict Place');
    }

    private function city(string $name): City
    {
        $country = Country::factory()->create([
            'code' => 'DE',
            'iso3' => 'DEU',
            'name' => 'Germany',
            'name_en' => 'Germany',
            'status' => Country::STATUS_ACTIVE,
            'is_active' => true,
        ]);

        return City::factory()->for($country)->create([
            'name' => $name,
            'ascii_name' => $name,
            'status' => City::STATUS_ACTIVE,
            'is_active' => true,
        ]);
    }

    private function createSearchPlace(string $title, City $city): SleepingPlace
    {
        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'rating_average' => 4.8,
            'reviews_count' => 12,
            'verified_at' => now(),
        ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->for($city, 'cityModel')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'country_id' => $city->country_id,
                'city_id' => $city->id,
                'city' => $city->name,
                'district' => 'Central',
                'status' => PropertyStatus::Active,
                'type' => PropertyType::Apartment,
            ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'type' => RoomType::Shared,
            'gender_policy' => GenderType::NoRestriction,
            'max_guests' => 4,
            'available_places_count' => 1,
        ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'type' => SleepingPlaceType::Single,
                'display_name' => $title,
                'base_price_per_night' => 30,
                'currency' => 'EUR',
                'max_guests' => 1,
                'min_nights' => 1,
                'max_nights' => null,
            ]);

        $place->translations()->create(['locale' => 'en', 'title' => $title]);
        $place->translations()->create(['locale' => 'ru', 'title' => $title]);

        return $place;
    }

    private function cacheCompatibility(
        User $guest,
        SleepingPlace $place,
        string $checkIn,
        string $checkOut,
        string $fitStatus,
        int $score,
    ): void {
        CompatibilityResult::factory()
            ->for($guest, 'user')
            ->for($place->property)
            ->for($place->room)
            ->for($place)
            ->create([
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'nights_count' => 3,
                'compatibility_score' => $score,
                'fit_status' => $fitStatus,
                'expires_at' => now()->addHour(),
            ]);
    }

    private function amenity(string $slug, string $label): Amenity
    {
        $amenity = Amenity::factory()->create([
            'slug' => $slug,
            'name_normalized' => $slug,
            'category' => 'property',
            'status' => 'active',
        ]);
        $amenity->translations()->create(['locale' => 'en', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);
        $amenity->translations()->create(['locale' => 'ru', 'name' => $label, 'name_normalized' => str($label)->lower()->toString()]);

        return $amenity;
    }
}
