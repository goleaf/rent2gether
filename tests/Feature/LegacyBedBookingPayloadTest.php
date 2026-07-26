<?php

namespace Tests\Feature;

use App\Enums\BedStatus;
use App\Enums\BedType;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Livewire\Booking\CreateBooking;
use App\Livewire\Search\BedCard;
use App\Models\Bed;
use App\Models\Property;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Livewire\Livewire;
use Tests\TestCase;

class LegacyBedBookingPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_bed_booking_keeps_the_bed_model_out_of_livewire_public_state(): void
    {
        ['guest' => $guest, 'bed' => $bed] = $this->legacyBookingFixture();

        $component = Livewire::actingAs($guest)
            ->test(CreateBooking::class, ['bed' => $bed])
            ->assertSet('bedId', $bed->id)
            ->assertSet('guestsCount', 1)
            ->set('checkIn', '2026-08-10')
            ->set('checkOut', '2026-08-13')
            ->assertSee('Legacy lower bed')
            ->assertSee(__('booking.price_breakdown'));

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('bedId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\Bed', $encodedSnapshot);
        $this->assertLessThan(12_000, strlen($encodedSnapshot), 'Legacy bed booking snapshot should stay compact on mobile.');
    }

    public function test_legacy_bed_booking_passes_render_ready_price_summary_to_blade(): void
    {
        app()->setLocale('en');

        ['guest' => $guest, 'bed' => $bed] = $this->legacyBookingFixture();

        Livewire::actingAs($guest)
            ->test(CreateBooking::class, ['bed' => $bed])
            ->set('checkIn', '2026-08-10')
            ->set('checkOut', '2026-08-13')
            ->assertViewHas('priceSummary', function (?array $summary): bool {
                $rows = collect($summary['rows'] ?? []);

                return is_array($summary)
                    && $summary['total_amount'] === Number::currency(98, 'EUR', 'en')
                    && $rows->contains(fn (array $row): bool => $row['label'] === trans_choice('booking.nights_count', 3, ['count' => 3])
                        && $row['amount'] === Number::currency(60, 'EUR', 'en'))
                    && $rows->contains(fn (array $row): bool => $row['label'] === __('booking.cleaning_fee')
                        && $row['amount'] === Number::currency(5, 'EUR', 'en'))
                    && $rows->contains(fn (array $row): bool => $row['label'] === __('booking.deposit')
                        && $row['amount'] === Number::currency(30, 'EUR', 'en'))
                    && $rows->contains(fn (array $row): bool => $row['label'] === __('booking.service_fee')
                        && $row['amount'] === Number::currency(3, 'EUR', 'en'));
            });
    }

    public function test_legacy_bed_card_keeps_the_bed_model_out_of_livewire_public_state(): void
    {
        app()->setLocale('en');

        ['bed' => $bed] = $this->legacyBookingFixture();

        $component = Livewire::test(BedCard::class, ['bed' => $bed, 'nights' => 3])
            ->assertSet('bedId', $bed->id)
            ->assertViewHas('bed', fn (Bed $viewBed): bool => $viewBed->is($bed))
            ->assertViewHas('nightlyPrice', Number::currency(20, 'EUR', 'en'))
            ->assertViewHas('totalPrice', Number::currency(98, 'EUR', 'en'))
            ->assertSee('Legacy lower bed');

        $encodedSnapshot = json_encode($component->snapshot, JSON_THROW_ON_ERROR);

        $this->assertStringContainsString('bedId', $encodedSnapshot);
        $this->assertStringNotContainsString('App\\\\Models\\\\Bed', $encodedSnapshot);
        $this->assertStringNotContainsString('priceSummary', $encodedSnapshot);
        $this->assertLessThan(10_000, strlen($encodedSnapshot), 'Legacy bed card snapshot should keep the full bed model out of public state.');
    }

    /**
     * @return array{guest:User,host:User,property:Property,room:Room,bed:Bed}
     */
    private function legacyBookingFixture(): array
    {
        $guest = User::factory()->create();
        $host = User::factory()->create(['is_host' => true]);
        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active,
            'title' => 'Compact booking house',
            'city' => 'Vilnius',
        ]);
        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Quiet shared room',
        ]);
        $bed = Bed::factory()->for($room)->create([
            'title' => 'Legacy lower bed',
            'type' => BedType::Single,
            'status' => BedStatus::Active,
            'price_per_night' => 20,
            'price_weekend' => null,
            'price_weekly' => null,
            'price_monthly' => null,
            'cleaning_fee' => 5,
            'deposit' => 30,
            'max_guests' => 1,
            'min_nights' => 1,
            'instant_book' => true,
        ]);

        return [
            'guest' => $guest,
            'host' => $host,
            'property' => $property,
            'room' => $room,
            'bed' => $bed,
        ];
    }
}
