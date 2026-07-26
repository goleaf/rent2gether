<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\BookingReview;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingReviewPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2026-07-26 10:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_booking_review_keeps_quote_and_availability_results_out_of_public_snapshot(): void
    {
        [$guest, $place] = $this->bookingPlace();

        $component = Livewire::actingAs($guest)
            ->test(BookingReview::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-08-10')
            ->set('checkOut', '2026-08-12')
            ->set('guestMessage', 'I will arrive quietly after work.')
            ->assertSet('sleepingPlaceId', $place->id)
            ->assertSee(__('booking.flow.price.title'))
            ->assertSee(__('booking.flow.price.refund_title'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('sleepingPlaceId', $encodedSnapshot);
        $this->assertStringNotContainsString('"quote"', $encodedSnapshot);
        $this->assertStringNotContainsString('"availabilityWarning"', $encodedSnapshot);
        $this->assertStringNotContainsString('"unavailableDates"', $encodedSnapshot);
        $this->assertLessThan(14_000, strlen($encodedSnapshot), 'Booking review snapshot should keep calculated quote data out of public Livewire state.');
    }

    /**
     * @return array{0:User,1:SleepingPlace}
     */
    private function bookingPlace(): array
    {
        $guest = User::factory()->create([
            'name' => 'Careful Guest',
            'email_verified_at' => CarbonImmutable::now(),
        ]);
        UserProfile::factory()->for($guest, 'user')->create([
            'about' => 'Quiet short stay guest.',
            'phone_verified_at' => CarbonImmutable::now(),
        ]);

        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create();

        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active,
            'title' => 'Payload house',
            'city' => 'Vilnius',
        ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Payload room',
        ]);
        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Payload lower bed',
                'base_price_per_night' => 20,
                'weekend_price' => null,
                'weekly_price' => null,
                'monthly_price' => null,
                'cleaning_fee' => 5,
                'deposit_amount' => 30,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
                'max_guests' => 1,
                'instant_booking_enabled' => true,
                'requires_host_approval' => false,
            ]);

        return [$guest, $place];
    }
}
