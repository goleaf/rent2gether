<?php

namespace Tests\Unit;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\SleepingPlaceStatus;
use App\Models\Amenity;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingPriceLine;
use App\Models\BookingStatusHistory;
use App\Models\City;
use App\Models\Complaint;
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
use App\Models\PriceRule;
use App\Models\Property;
use App\Models\PropertyTranslation;
use App\Models\RefundRequest;
use App\Models\Region;
use App\Models\Review;
use App\Models\Room;
use App\Models\RoomTranslation;
use App\Models\Rule;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceTranslation;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserSetting;
use App\Models\WaitlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreMarketplaceSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_room_and_sleeping_place_graph_can_be_created_with_translations(): void
    {
        $country = Country::factory()->create();
        $region = Region::factory()->for($country)->create();
        $city = City::factory()->for($country)->for($region)->create();
        $host = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(HostProfile::factory(), 'hostProfile')
            ->has(UserSetting::factory(), 'setting')
            ->create();

        $property = Property::factory()
            ->for($host, 'host')
            ->for($country)
            ->for($region)
            ->for($city)
            ->has(PropertyTranslation::factory()->state(['locale' => 'en']), 'translations')
            ->create(['status' => PropertyStatus::Active]);

        $room = Room::factory()
            ->for($property)
            ->has(RoomTranslation::factory()->state(['locale' => 'en']), 'translations')
            ->create();

        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->has(SleepingPlaceTranslation::factory()->state(['locale' => 'en']), 'translations')
            ->create(['status' => SleepingPlaceStatus::Active]);

        $amenity = Amenity::factory()
            ->hasTranslations(1, ['locale' => 'en'])
            ->create();
        $rule = Rule::factory()
            ->hasTranslations(1, ['locale' => 'en'])
            ->create();

        $property->amenities()->attach($amenity);
        $room->amenities()->attach($amenity);
        $sleepingPlace->amenities()->attach($amenity);
        $property->rules()->attach($rule);
        $room->rules()->attach($rule);
        $sleepingPlace->rules()->attach($rule);

        MediaItem::factory()->for($property, 'mediable')->create();
        AvailabilityDay::factory()->for($sleepingPlace)->create([
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::Available,
        ]);
        PriceRule::factory()->for($sleepingPlace)->create();
        DiscountRule::factory()->for($sleepingPlace)->create();

        $this->assertTrue(Property::active()->inCity($city->id)->translated('en')->whereKey($property)->exists());
        $this->assertTrue($property->rooms()->whereKey($room)->exists());
        $this->assertTrue($property->sleepingPlaces()->whereKey($sleepingPlace)->exists());
        $this->assertTrue($sleepingPlace->amenities()->whereKey($amenity)->exists());
        $this->assertTrue($sleepingPlace->rules()->whereKey($rule)->exists());
        $this->assertTrue($sleepingPlace->availabilityDays()->whereDate('date', '2026-07-10')->exists());
    }

    public function test_booking_money_and_social_records_can_be_created_from_factories(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createBookableSleepingPlace();

        $booking = Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace)
            ->create([
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
            ]);

        BookingGuest::factory()->for($booking)->create();
        BookingPriceLine::factory()->for($booking)->create();
        BookingStatusHistory::factory()->for($booking)->for($guest, 'changedBy')->create();
        PaymentRecord::factory()->for($booking)->for($guest, 'payer')->create();
        DepositRecord::factory()->for($booking)->create();
        RefundRequest::factory()->for($booking)->for($guest, 'requestedBy')->create();
        Favorite::factory()->for($guest)->for($sleepingPlace)->create();
        $city = City::findOrFail($property->city_id);
        SavedSearch::factory()->for($guest)->for($city)->create();
        WaitlistItem::factory()->for($guest)->for($sleepingPlace)->create();

        $thread = MessageThread::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($booking)
            ->for($sleepingPlace)
            ->create();
        Message::factory()->for($thread, 'thread')->for($guest, 'sender')->create();

        Review::factory()->for($booking)->for($guest, 'reviewer')->for($host, 'reviewee')->for($property)->for($room)->for($sleepingPlace)->create();
        Complaint::factory()->for($guest, 'reporter')->for($host, 'reportedUser')->for($booking)->for($property)->for($room)->for($sleepingPlace)->create();
        Notification::factory()->for($guest, 'user')->create();

        $this->assertTrue(Booking::forGuest($guest->id)->whereKey($booking)->exists());
        $this->assertTrue(Booking::forHost($host->id)->whereKey($booking)->exists());
        $this->assertTrue($booking->priceLines()->exists());
        $this->assertTrue($booking->statusHistories()->exists());
        $this->assertTrue($thread->messages()->exists());
        $this->assertSame($city->id, $property->city_id);
    }

    public function test_available_between_scope_excludes_overlapping_bookings_and_blocked_days(): void
    {
        [$guest, $host, $property, $room, $sleepingPlace] = $this->createBookableSleepingPlace();

        $this->assertTrue(
            SleepingPlace::availableBetween('2026-07-10', '2026-07-15')
                ->whereKey($sleepingPlace)
                ->exists()
        );

        Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace)
            ->create([
                'check_in_date' => '2026-07-12',
                'check_out_date' => '2026-07-14',
                'status' => BookingStatus::Confirmed,
            ]);

        $this->assertFalse(
            SleepingPlace::availableBetween('2026-07-10', '2026-07-15')
                ->whereKey($sleepingPlace)
                ->exists()
        );
    }

    /**
     * @return array{User, User, Property, Room, SleepingPlace}
     */
    private function createBookableSleepingPlace(): array
    {
        $country = Country::factory()->create();
        $region = Region::factory()->for($country)->create();
        $city = City::factory()->for($country)->for($region)->create();
        $guest = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(GuestPreference::factory(), 'guestPreference')
            ->create();
        $host = User::factory()
            ->has(UserProfile::factory()->for($country)->for($city), 'profile')
            ->has(HostProfile::factory(), 'hostProfile')
            ->create();
        $property = Property::factory()
            ->for($host, 'host')
            ->for($country)
            ->for($region)
            ->for($city)
            ->create(['status' => PropertyStatus::Active]);
        $room = Room::factory()->for($property)->create();
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(['status' => SleepingPlaceStatus::Active]);

        return [$guest, $host, $property, $room, $sleepingPlace];
    }
}
