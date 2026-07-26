<?php

namespace Tests\Feature;

use App\Livewire\Host\CheckOut\HostTodayCheckOutCard;
use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HostTodayCheckOutCardPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_today_checkout_card_keeps_checkout_model_out_of_public_state(): void
    {
        $host = User::factory()->create(['is_host' => true]);
        $guest = User::factory()->create(['name' => 'Checkout Guest']);
        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
        ]);
        $room = Room::factory()->for($property)->create(['title' => 'Checkout room']);
        $sleepingPlace = SleepingPlace::factory()
            ->for($property)
            ->for($room)
            ->create(['display_name' => 'Bottom bunk']);
        $booking = Booking::factory()->create([
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $sleepingPlace->id,
            'check_out' => '2026-06-20',
            'check_out_date' => '2026-06-20',
        ]);
        $checkOut = BookingCheckOut::factory()
            ->for($booking)
            ->for($guest, 'guest')
            ->for($host, 'host')
            ->for($property)
            ->for($room)
            ->for($sleepingPlace, 'sleepingPlace')
            ->create([
                'check_out_date' => '2026-06-20',
                'planned_check_out_time' => '11:00',
                'internal_host_note' => 'Private host note',
            ]);

        $component = Livewire::actingAs($host)
            ->test(HostTodayCheckOutCard::class, ['checkOut' => $checkOut])
            ->assertSet('checkOutId', $checkOut->id)
            ->assertViewHas('checkOut', fn (?BookingCheckOut $viewCheckOut): bool => $viewCheckOut?->is($checkOut) === true)
            ->assertSee('Checkout Guest')
            ->assertSee('Checkout room')
            ->assertSee('Bottom bunk');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('checkOutId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\BookingCheckOut', $encodedSnapshot);
        $this->assertStringNotContainsString('Private host note', $encodedSnapshot);
        $this->assertLessThan(10_000, strlen($encodedSnapshot), 'Host today checkout card snapshot should keep full checkout models out of public state.');
    }
}
