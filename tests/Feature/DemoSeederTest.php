<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\City;
use App\Models\Complaint;
use App\Models\Country;
use App\Models\DiscountRule;
use App\Models\Favorite;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PriceRule;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\WaitlistItem;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_a_lightweight_marketplace_demo_dataset(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(2, Country::query()->count());
        $this->assertSame(5, City::query()->count());
        $this->assertSame(6, User::query()->count());
        $this->assertSame(3, GuestPreference::query()->count());
        $this->assertSame(3, HostProfile::query()->count());
        $this->assertSame(3, Property::query()->count());
        $this->assertSame(6, Room::query()->count());
        $this->assertSame(18, SleepingPlace::query()->count());
        $this->assertSame(18 * 90, AvailabilityDay::query()->count());
        $this->assertSame(18, PriceRule::query()->count());
        $this->assertSame(18, DiscountRule::query()->count());
        $this->assertSame(10, Booking::query()->count());
        $this->assertSame(10, MessageThread::query()->count());
        $this->assertSame(20, Message::query()->count());
        $this->assertSame(4, Review::query()->count());
        $this->assertSame(6, Favorite::query()->count());
        $this->assertSame(3, SavedSearch::query()->count());
        $this->assertSame(3, WaitlistItem::query()->count());
        $this->assertSame(12, Notification::query()->count());
        $this->assertSame(3, Complaint::query()->count());
        $this->assertGreaterThanOrEqual(33, MediaItem::query()->count());

        $this->assertDatabaseHas('property_translations', [
            'locale' => 'en',
            'title' => 'Quiet shared flat near the station',
        ]);
        $this->assertDatabaseHas('property_translations', [
            'locale' => 'ru',
            'title' => 'Тихая общая квартира рядом с вокзалом',
        ]);
        $this->assertDatabaseHas('media_items', [
            'collection' => 'sleeping_place',
            'mobile_path' => 'demo-media/sleeping_place/place-1-mobile.webp',
        ]);

        $this->assertSame(0, Amenity::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'en'))->count());
        $this->assertSame(0, Amenity::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'ru'))->count());
        $this->assertSame(0, Rule::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'en'))->count());
        $this->assertSame(0, Rule::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'ru'))->count());
    }

    public function test_demo_reset_command_rebuilds_the_lightweight_demo_dataset(): void
    {
        $exitCode = Artisan::call('app:demo-reset', ['--seed-only' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(18, SleepingPlace::query()->count());
        $this->assertSame(18 * 90, AvailabilityDay::query()->count());
    }
}
