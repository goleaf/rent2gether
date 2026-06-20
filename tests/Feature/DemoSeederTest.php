<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\AmenityTranslation;
use App\Models\AvailabilityDay;
use App\Models\Bed;
use App\Models\BedAvailability;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\BookingGuest;
use App\Models\BookingPriceLine;
use App\Models\BookingStatusHistory;
use App\Models\CheckinRecord;
use App\Models\CheckoutRecord;
use App\Models\City;
use App\Models\Complaint;
use App\Models\ComplaintStatusHistory;
use App\Models\Conversation;
use App\Models\Country;
use App\Models\DepositRecord;
use App\Models\DiscountRule;
use App\Models\Favorite;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\MediaItem;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentRecord;
use App\Models\Payout;
use App\Models\PriceRule;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\RefundRequest;
use App\Models\Region;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\RuleTranslation;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Models\WaitlistEntry;
use App\Models\WaitlistItem;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_a_marketplace_demo_dataset_with_bulk_coverage(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(1000, Country::query()->count());
        $this->assertGreaterThanOrEqual(1000, City::query()->count());
        $this->assertGreaterThanOrEqual(1000, User::query()->count());
        $this->assertGreaterThanOrEqual(1000, GuestPreference::query()->count());
        $this->assertGreaterThanOrEqual(1000, HostProfile::query()->count());
        $this->assertGreaterThanOrEqual(1000, Property::query()->count());
        $this->assertGreaterThanOrEqual(1000, Room::query()->count());
        $this->assertGreaterThanOrEqual(1000, SleepingPlace::query()->count());
        $this->assertGreaterThanOrEqual(18 * 90, AvailabilityDay::query()->count());
        $this->assertGreaterThanOrEqual(1000, PriceRule::query()->count());
        $this->assertGreaterThanOrEqual(1000, DiscountRule::query()->count());
        $this->assertGreaterThanOrEqual(1000, Booking::query()->count());
        $this->assertGreaterThanOrEqual(1000, MessageThread::query()->count());
        $this->assertGreaterThanOrEqual(1000, Message::query()->count());
        $this->assertGreaterThanOrEqual(1000, Review::query()->count());
        $this->assertGreaterThanOrEqual(1000, Favorite::query()->count());
        $this->assertGreaterThanOrEqual(1000, SavedSearch::query()->count());
        $this->assertGreaterThanOrEqual(1000, WaitlistItem::query()->count());
        $this->assertGreaterThanOrEqual(1000, Notification::query()->count());
        $this->assertGreaterThanOrEqual(1000, Complaint::query()->count());
        $this->assertGreaterThanOrEqual(1000, MediaItem::query()->count());

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
        $this->assertSeededMediaFilesExist();

        $this->assertSame(0, Amenity::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'en'))->count());
        $this->assertSame(0, Amenity::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'ru'))->count());
        $this->assertSame(0, Rule::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'en'))->count());
        $this->assertSame(0, Rule::query()->whereDoesntHave('translations', fn ($query) => $query->where('locale', 'ru'))->count());
    }

    public function test_demo_reset_command_rebuilds_the_bulk_demo_dataset(): void
    {
        $exitCode = Artisan::call('app:demo-reset', ['--seed-only' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertGreaterThanOrEqual(1000, SleepingPlace::query()->count());
        $this->assertGreaterThanOrEqual(18 * 90, AvailabilityDay::query()->count());
    }

    public function test_database_seeder_creates_at_least_one_thousand_records_for_each_application_model(): void
    {
        $this->seed(DatabaseSeeder::class);

        foreach ($this->bulkSeededModels() as $modelClass) {
            $this->assertGreaterThanOrEqual(
                1000,
                $modelClass::query()->count(),
                $modelClass.' should have at least 1000 seeded rows.',
            );
        }
    }

    /**
     * @return list<class-string<Model>>
     */
    private function bulkSeededModels(): array
    {
        return [
            Amenity::class,
            AmenityTranslation::class,
            AvailabilityDay::class,
            Bed::class,
            BedAvailability::class,
            Booking::class,
            BookingExtension::class,
            BookingGuest::class,
            BookingPriceLine::class,
            BookingStatusHistory::class,
            CheckinRecord::class,
            CheckoutRecord::class,
            City::class,
            Complaint::class,
            ComplaintStatusHistory::class,
            Conversation::class,
            Country::class,
            DepositRecord::class,
            DiscountRule::class,
            Favorite::class,
            GuestPreference::class,
            HostProfile::class,
            MediaItem::class,
            Message::class,
            MessageThread::class,
            Notification::class,
            PaymentRecord::class,
            Payout::class,
            PriceRule::class,
            Property::class,
            PropertyTranslation::class,
            RefundRequest::class,
            Region::class,
            Review::class,
            Room::class,
            RoomTranslation::class,
            Rule::class,
            RuleTranslation::class,
            SavedSearch::class,
            SleepingPlace::class,
            SleepingPlaceTranslation::class,
            User::class,
            UserProfile::class,
            UserSetting::class,
            WaitlistEntry::class,
            WaitlistItem::class,
        ];
    }

    private function assertSeededMediaFilesExist(): void
    {
        $missing = [];

        MediaItem::query()
            ->select([
                'id',
                'disk',
                'path',
                'thumbnail_path',
                'thumb_path',
                'mobile_path',
                'full_path',
            ])
            ->where(function ($query): void {
                $query->where('path', 'like', 'demo-media/%')
                    ->orWhere('path', 'like', 'bulk-demo/%');
            })
            ->chunkById(200, function ($mediaItems) use (&$missing): void {
                foreach ($mediaItems as $mediaItem) {
                    foreach ($this->mediaPaths($mediaItem) as $path) {
                        if (! Storage::disk($mediaItem->disk ?: 'public')->exists($path)) {
                            $missing[] = $path;
                        }
                    }
                }
            });

        $this->assertCount(
            0,
            $missing,
            'Missing seeded media files: '.implode(', ', array_slice($missing, 0, 20)),
        );
    }

    /**
     * @return list<string>
     */
    private function mediaPaths(MediaItem $mediaItem): array
    {
        return collect([
            $mediaItem->path,
            $mediaItem->thumbnail_path,
            $mediaItem->thumb_path,
            $mediaItem->mobile_path,
            $mediaItem->full_path,
        ])
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->unique()
            ->values()
            ->all();
    }
}
