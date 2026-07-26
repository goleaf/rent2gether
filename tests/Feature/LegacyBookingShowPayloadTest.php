<?php

namespace Tests\Feature;

use App\Enums\BedStatus;
use App\Enums\BedType;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\BookingShow;
use App\Models\Bed;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LegacyBookingShowPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_booking_show_keeps_the_booking_model_out_of_livewire_public_state(): void
    {
        $booking = $this->legacyBookingFixture();

        $component = Livewire::test(BookingShow::class, ['booking' => $booking])
            ->assertSet('bookingId', $booking->id)
            ->assertViewHas('booking', fn (Booking $viewBooking): bool => $viewBooking->is($booking))
            ->assertSee('Legacy detail place')
            ->assertSee('Legacy detail room')
            ->assertSee('Legacy detail property');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('bookingId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\Booking', $encodedSnapshot);
        $this->assertLessThan(12_000, strlen($encodedSnapshot), 'Legacy booking detail snapshot should keep the full booking model out of public state.');
    }

    private function legacyBookingFixture(): Booking
    {
        $guest = User::factory()->create();
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active,
            'title' => 'Legacy detail property',
            'city' => 'Vilnius',
        ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Legacy detail room',
        ]);
        $bed = Bed::factory()->for($room)->create([
            'title' => 'Legacy detail bed',
            'type' => BedType::Single,
            'status' => BedStatus::Active,
        ]);
        $place = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Legacy detail place',
                'place_number' => 'L1',
            ]);

        return Booking::factory()
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($bed)
            ->for($place, 'sleepingPlace')
            ->create([
                'guest_id' => $guest->id,
                'host_id' => $host->id,
                'guest_user_id' => $guest->id,
                'host_user_id' => $host->id,
                'property_id' => $property->id,
                'room_id' => $room->id,
                'bed_id' => $bed->id,
                'sleeping_place_id' => $place->id,
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
                'check_in' => '2026-08-10',
                'check_out' => '2026-08-13',
                'check_in_date' => '2026-08-10',
                'check_out_date' => '2026-08-13',
                'nights' => 3,
                'nights_count' => 3,
                'guests_count' => 1,
                'subtotal' => 60,
                'subtotal_amount' => 60,
                'discount_amount' => 0,
                'cleaning_fee' => 5,
                'cleaning_fee_amount' => 5,
                'service_fee' => 3,
                'service_fee_amount' => 3,
                'total' => 98,
                'total_amount' => 98,
                'currency' => 'EUR',
            ]);
    }
}
