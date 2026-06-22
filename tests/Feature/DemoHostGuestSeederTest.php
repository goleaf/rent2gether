<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckOut;
use App\Models\BookingPriceLine;
use App\Models\BookingQuote;
use App\Models\BookingRequest;
use App\Models\BookingStay;
use App\Models\Favorite;
use App\Models\HostProfile;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Notification;
use App\Models\PaymentRecord;
use App\Models\Property;
use App\Models\Review;
use App\Models\Room;
use App\Models\SavedSearch;
use App\Models\SleepingPlace;
use App\Models\SleepingPlaceCalendarDay;
use App\Models\User;
use App\Models\UserActivitySummary;
use App\Models\UserDocument;
use App\Models\UserLanguage;
use App\Models\UserNotificationPreference;
use App\Models\UserPrivacySetting;
use App\Models\UserSavedPreference;
use App\Models\UserSetting;
use App\Models\UserVerification;
use App\Models\WaitlistItem;
use Database\Seeders\DemoHostGuestSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoHostGuestSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_named_demo_host_and_guest_get_complete_twenty_stay_history(): void
    {
        $this->seed(DemoHostGuestSeeder::class);
        $this->seed(DemoHostGuestSeeder::class);

        $host = User::query()->where('email', 'host@example.com')->firstOrFail();
        $guest = User::query()->where('email', 'guest@example.com')->firstOrFail();

        $this->assertTrue(Hash::check('password', $host->password));
        $this->assertTrue(Hash::check('example', $guest->password));
        $this->assertTrue((bool) $host->is_host);
        $this->assertTrue((bool) $guest->is_guest);

        $this->assertSame(1, HostProfile::query()->where('user_id', $host->id)->count());
        $this->assertGreaterThanOrEqual(2, UserSetting::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(2, UserSavedPreference::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(2, UserPrivacySetting::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(4, UserLanguage::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(4, UserVerification::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(2, UserDocument::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(6, UserNotificationPreference::query()->whereIn('user_id', [$host->id, $guest->id])->count());
        $this->assertGreaterThanOrEqual(2, UserActivitySummary::query()->whereIn('user_id', [$host->id, $guest->id])->count());

        $property = Property::query()->where('host_user_id', $host->id)->firstOrFail();
        $room = Room::query()->where('property_id', $property->id)->firstOrFail();
        $place = SleepingPlace::query()->where('room_id', $room->id)->firstOrFail();

        $this->assertDatabaseHas('property_translations', ['property_id' => $property->id, 'locale' => 'en']);
        $this->assertDatabaseHas('property_translations', ['property_id' => $property->id, 'locale' => 'ru']);
        $this->assertDatabaseHas('room_translations', ['room_id' => $room->id, 'locale' => 'en']);
        $this->assertDatabaseHas('room_translations', ['room_id' => $room->id, 'locale' => 'ru']);
        $this->assertDatabaseHas('sleeping_place_translations', ['sleeping_place_id' => $place->id, 'locale' => 'en']);
        $this->assertDatabaseHas('sleeping_place_translations', ['sleeping_place_id' => $place->id, 'locale' => 'ru']);

        $bookings = Booking::query()
            ->where('guest_user_id', $guest->id)
            ->where('host_user_id', $host->id)
            ->where('status', BookingStatus::Completed->value)
            ->orderBy('check_in_date')
            ->get();

        $this->assertCount(20, $bookings);

        $bookingIds = $bookings->pluck('id');

        $this->assertGreaterThanOrEqual(20, BookingQuote::query()->whereIn('id', $bookings->pluck('booking_quote_id')->filter())->count());
        $this->assertGreaterThanOrEqual(4, BookingRequest::query()->where('guest_user_id', $guest->id)->where('host_user_id', $host->id)->count());
        $this->assertGreaterThanOrEqual(80, BookingPriceLine::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertSame(20, PaymentRecord::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertSame(20, BookingStay::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertSame(20, BookingCheckIn::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertSame(20, BookingCheckOut::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertGreaterThanOrEqual(40, Review::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertGreaterThanOrEqual(20, MessageThread::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertGreaterThanOrEqual(60, Message::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertGreaterThanOrEqual(80, AvailabilityDay::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertGreaterThanOrEqual(80, SleepingPlaceCalendarDay::query()->whereIn('booking_id', $bookingIds)->count());
        $this->assertGreaterThanOrEqual(4, Favorite::query()->where('user_id', $guest->id)->count());
        $this->assertGreaterThanOrEqual(2, SavedSearch::query()->where('user_id', $guest->id)->count());
        $this->assertGreaterThanOrEqual(2, WaitlistItem::query()->where('user_id', $guest->id)->count());
        $this->assertGreaterThanOrEqual(10, Notification::query()->whereIn('user_id', [$host->id, $guest->id])->count());
    }
}
