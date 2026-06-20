<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Trips\BookingDetail;
use App\Livewire\Trips\CurrentStay;
use App\Livewire\Trips\TripList;
use App\Models\Amenity;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\Rule;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTripManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-20 10:00:00');
        CarbonImmutable::setTestNow('2026-06-20 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_upcoming_trip_visible(): void
    {
        [$guest] = $this->createTrip(BookingStatus::Confirmed, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-14',
        ]);

        $this->actingAs($guest)
            ->get(route('trips.index', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(TripList::class)
            ->assertSee(__('booking.trips.scopes.upcoming.title', [], 'en'))
            ->assertSee('Quiet lower bed')
            ->assertSee('Vilnius')
            ->assertSee(__('booking.trips.actions.open_detail', [], 'en'));
    }

    public function test_current_stay_visible_after_check_in(): void
    {
        [$guest] = $this->createTrip(BookingStatus::CheckedIn, [
            'check_in_date' => '2026-06-18',
            'check_out_date' => '2026-06-24',
            'guest_checked_in_at' => now(),
            'checked_in_at' => now(),
        ]);

        $this->actingAs($guest)
            ->get(route('trips.current', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(CurrentStay::class)
            ->assertSee(__('booking.trips.current.title', [], 'en'))
            ->assertSee('Central Street')
            ->assertSee('A2')
            ->assertSee('L1')
            ->assertSee(__('booking.trips.current.nights_remaining', [], 'en'));
    }

    public function test_past_trip_visible_after_checkout(): void
    {
        [$guest] = $this->createTrip(BookingStatus::CheckedOut, [
            'check_in_date' => '2026-06-10',
            'check_out_date' => '2026-06-15',
            'guest_checked_in_at' => now()->subDays(8),
            'guest_checked_out_at' => now()->subDays(5),
            'checked_in_at' => now()->subDays(8),
            'checked_out_at' => now()->subDays(5),
        ]);

        $this->actingAs($guest)
            ->get(route('trips.scope', ['locale' => 'en', 'scope' => 'past']))
            ->assertOk()
            ->assertSeeLivewire(TripList::class)
            ->assertSee(__('booking.trips.scopes.past.title', [], 'en'))
            ->assertSee('Quiet lower bed');
    }

    public function test_address_hidden_before_confirmation(): void
    {
        [$guest, $booking] = $this->createTrip(BookingStatus::AwaitingHostApproval, [
            'payment_status' => PaymentStatus::Unpaid,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-12',
            'check_in_instructions' => null,
        ]);

        $this->actingAs($guest)
            ->get(route('guest.bookings.show', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(BookingDetail::class)
            ->assertSee(__('booking.trips.address_hidden', [], 'en'))
            ->assertDontSee('Central Street');
    }

    public function test_localized_content_renders_for_russian_trip_pages(): void
    {
        [$guest] = $this->createTrip(BookingStatus::Confirmed, [
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-14',
        ]);

        $this->actingAs($guest)
            ->get(route('trips.index', ['locale' => 'ru']))
            ->assertOk()
            ->assertSeeLivewire(TripList::class)
            ->assertSee(__('booking.trips.scopes.upcoming.title', [], 'ru'))
            ->assertSee('Тихое нижнее место')
            ->assertDontSee('Quiet lower bed');
    }

    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @return array{0: User, 1: Booking}
     */
    private function createTrip(BookingStatus $status, array $bookingOverrides = []): array
    {
        $guest = User::factory()->create();
        UserProfile::factory()->for($guest, 'user')->create();

        $host = User::factory()->create([
            'is_host' => true,
            'phone' => '+37060000000',
        ]);
        UserProfile::factory()->for($host, 'user')->create([
            'phone' => '+37060000001',
        ]);
        HostProfile::factory()->for($host, 'user')->create([
            'display_name' => 'Mila Host',
        ]);

        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
            'status' => PropertyStatus::Active,
            'city' => 'Vilnius',
            'district' => 'Old Town',
            'address_line_1' => 'Central Street',
            'house_number' => '12',
            'apartment_number' => '4',
            'show_exact_address_before_booking' => false,
            'show_exact_address_after_payment' => true,
            'amenities' => [],
        ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Shared apartment',
            'summary' => 'A calm apartment.',
            'description' => 'A calm apartment.',
            'check_in_instructions' => 'Use the small entrance.',
            'house_rules_text' => 'Keep shared spaces calm.',
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Общая квартира',
            'summary' => 'Спокойная квартира.',
            'description' => 'Спокойная квартира.',
            'check_in_instructions' => 'Используйте маленький вход.',
            'house_rules_text' => 'Сохраняйте спокойствие в общих зонах.',
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Shared room A',
            'room_number' => 'A2',
            'room_rules_text' => 'No loud calls after 22:00.',
        ]);
        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Shared room A',
            'summary' => 'Shared room.',
            'description' => 'Shared room.',
        ]);
        $room->translations()->create([
            'locale' => 'ru',
            'title' => 'Общая комната A',
            'summary' => 'Общая комната.',
            'description' => 'Общая комната.',
        ]);

        $place = SleepingPlace::factory()->for($room)->for($property)->create([
            'status' => SleepingPlaceStatus::Active,
            'display_name' => 'Quiet lower bed',
            'place_number' => 'L1',
            'base_price_per_night' => 10,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
            'currency' => 'EUR',
        ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
            'summary' => 'A quiet bed.',
            'description' => 'A quiet bed.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихое нижнее место',
            'summary' => 'Тихое место.',
            'description' => 'Тихое место.',
        ]);

        $wifi = Amenity::factory()->create(['slug' => 'wifi', 'name_normalized' => 'wifi']);
        $wifi->translations()->createMany([
            ['locale' => 'en', 'name' => 'Wi-Fi', 'name_normalized' => 'wifi'],
            ['locale' => 'ru', 'name' => 'Wi-Fi', 'name_normalized' => 'wifi'],
        ]);
        $property->amenities()->attach($wifi);

        $rule = Rule::factory()->create(['slug' => 'quiet_hours', 'name_normalized' => 'quiet hours']);
        $rule->translations()->createMany([
            ['locale' => 'en', 'name' => 'Quiet hours after 22:00', 'name_normalized' => 'quiet hours after 22:00'],
            ['locale' => 'ru', 'name' => 'Тихие часы после 22:00', 'name_normalized' => 'тихие часы после 22:00'],
        ]);
        $place->rules()->attach($rule);

        $booking = Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => $status,
            'payment_status' => $bookingOverrides['payment_status'] ?? PaymentStatus::Paid,
            'check_in_date' => $bookingOverrides['check_in_date'] ?? '2026-07-10',
            'check_out_date' => $bookingOverrides['check_out_date'] ?? '2026-07-14',
            'check_in' => $bookingOverrides['check_in_date'] ?? '2026-07-10',
            'check_out' => $bookingOverrides['check_out_date'] ?? '2026-07-14',
            'check_in_time' => '15:00',
            'check_out_time' => '10:00',
            'nights_count' => 4,
            'nights' => 4,
            'calendar_days_count' => 5,
            'subtotal_amount' => 40,
            'subtotal' => 40,
            'cleaning_fee_amount' => 5,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
            'deposit' => 30,
            'service_fee_amount' => 2,
            'service_fee' => 2,
            'total_amount' => 77,
            'total' => 77,
            'refundable_amount' => 30,
            'non_refundable_amount' => 47,
            'currency' => 'EUR',
            'payment_paid_at' => now(),
            'check_in_instructions' => array_key_exists('check_in_instructions', $bookingOverrides)
                ? $bookingOverrides['check_in_instructions']
                : "Central Street, 12, 4\n\nUse the small entrance.",
            'guest_checked_in_at' => $bookingOverrides['guest_checked_in_at'] ?? null,
            'guest_checked_out_at' => $bookingOverrides['guest_checked_out_at'] ?? null,
            'checked_in_at' => $bookingOverrides['checked_in_at'] ?? null,
            'checked_out_at' => $bookingOverrides['checked_out_at'] ?? null,
        ]);

        $booking->priceLines()->create([
            'type' => 'nightly_base',
            'label_key' => 'booking.price_lines.nightly_base',
            'amount' => 40,
            'currency' => 'EUR',
            'is_refundable' => false,
        ]);
        $booking->priceLines()->create([
            'type' => 'deposit',
            'label_key' => 'booking.price_lines.deposit',
            'amount' => 30,
            'currency' => 'EUR',
            'is_refundable' => true,
        ]);
        $booking->priceLines()->create([
            'type' => 'total',
            'label_key' => 'booking.price_lines.total',
            'amount' => 77,
            'currency' => 'EUR',
            'is_refundable' => false,
        ]);
        $booking->depositRecords()->create([
            'amount' => 30,
            'currency' => 'EUR',
            'status' => 'held',
            'held_at' => now(),
            'withheld_amount' => 0,
        ]);

        return [$guest, $booking];
    }
}
