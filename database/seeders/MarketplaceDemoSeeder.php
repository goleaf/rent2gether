<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\City;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use Illuminate\Database\Seeder;

class MarketplaceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $city = City::query()->where('name_normalized', 'vilnius')->firstOrFail();
        $country = $city->country;
        $region = $city->region;

        $host = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(HostProfile::factory(), 'hostProfile')
            ->has(UserSetting::factory()->state(['locale' => 'en']), 'setting')
            ->create([
                'name' => 'Demo Host',
                'email' => 'host@example.com',
                'is_host' => true,
            ]);

        User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(GuestPreference::factory(), 'guestPreference')
            ->has(UserSetting::factory()->state(['locale' => 'ru']), 'setting')
            ->create([
                'name' => 'Demo Guest',
                'email' => 'guest@example.com',
            ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->for($country)
            ->for($region)
            ->for($city)
            ->create();

        PropertyTranslation::factory()->for($property)->create([
            'locale' => 'en',
            'title' => 'Calm shared home near transit',
        ]);
        PropertyTranslation::factory()->for($property)->create([
            'locale' => 'ru',
            'title' => 'Спокойное место рядом с транспортом',
        ]);

        $room = Room::factory()->for($property)->create();
        RoomTranslation::factory()->for($room)->create(['locale' => 'en']);
        RoomTranslation::factory()->for($room)->create(['locale' => 'ru', 'title' => 'Общая комната']);

        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create();

        SleepingPlaceTranslation::factory()->for($sleepingPlace)->create(['locale' => 'en']);
        SleepingPlaceTranslation::factory()->for($sleepingPlace)->create([
            'locale' => 'ru',
            'title' => 'Удобное спальное место',
        ]);

        $property->amenities()->syncWithoutDetaching(Amenity::query()->pluck('id'));
        $room->amenities()->syncWithoutDetaching(Amenity::query()->pluck('id'));
        $sleepingPlace->amenities()->syncWithoutDetaching(Amenity::query()->pluck('id'));
        $property->rules()->syncWithoutDetaching(Rule::query()->pluck('id'));
        $room->rules()->syncWithoutDetaching(Rule::query()->pluck('id'));
        $sleepingPlace->rules()->syncWithoutDetaching(Rule::query()->pluck('id'));
    }
}
