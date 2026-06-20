<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\BookingExtensionStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Extensions\ExtendStay;
use App\Livewire\Extensions\ManageExtension;
use App\Livewire\Trips\CurrentStay;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Bookings\ExtensionService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingExtensionFlowTest extends TestCase
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

    public function test_extension_component_renders_on_current_stay_page_and_creates_request(): void
    {
        [$guest, $host, $booking] = $this->createStay();

        $this->actingAs($guest)
            ->get(route('trips.current', ['locale' => 'en']))
            ->assertOk()
            ->assertSeeLivewire(CurrentStay::class)
            ->assertSeeLivewire(ExtendStay::class)
            ->assertSee(__('booking.extension.title', [], 'en'));

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking])
            ->set('requestedNewCheckout', '2026-06-26')
            ->set('guestMessage', 'I would like to stay two more nights.')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('booking_extensions', [
            'booking_id' => $booking->id,
            'current_checkout_date' => '2026-06-24 00:00:00',
            'requested_new_checkout_date' => '2026-06-26 00:00:00',
            'additional_nights' => 2,
            'additional_amount' => 40,
            'total_extra' => 42,
            'new_total' => 168,
            'payment_required' => true,
            'status' => BookingExtensionStatus::AwaitingHostApproval->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'booking_extension_requested',
        ]);
    }

    public function test_extension_is_unavailable_when_next_booking_blocks_dates(): void
    {
        [$guest, $host, $booking, $place] = $this->createStay();
        $this->createBlockingBooking($host, $place);

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking])
            ->set('requestedNewCheckout', '2026-06-26')
            ->call('submit')
            ->assertHasErrors(['requestedNewCheckout']);

        $this->assertDatabaseMissing('booking_extensions', [
            'booking_id' => $booking->id,
            'requested_new_checkout_date' => '2026-06-26 00:00:00',
        ]);
    }

    public function test_extra_price_is_calculated_without_repeating_deposit_or_cleaning_fee(): void
    {
        [$guest, , $booking] = $this->createStay();

        $preview = app(ExtensionService::class)->preview($guest, $booking, '2026-06-26');

        $this->assertSame(2, $preview['additional_nights']);
        $this->assertSame(40.0, $preview['additional_amount']);
        $this->assertSame(0.0, $preview['discount_amount']);
        $this->assertSame(2.0, $preview['service_fee_amount']);
        $this->assertSame(42.0, $preview['total_extra']);
        $this->assertSame(168.0, $preview['new_total']);
    }

    public function test_host_approval_flow_moves_extension_to_payment_then_updates_booking_after_payment(): void
    {
        [$guest, $host, $booking, $place] = $this->createStay();

        $extension = app(ExtensionService::class)->request($guest, $booking, '2026-06-26');

        Livewire::actingAs($host)
            ->test(ManageExtension::class, ['booking' => $booking, 'extension' => $extension])
            ->set('hostResponse', 'That works.')
            ->call('approve')
            ->assertHasNoErrors();

        $extension->refresh();
        $booking->refresh();

        $this->assertTrue($extension->status === BookingExtensionStatus::AwaitingPayment);
        $this->assertSame('2026-06-24', $booking->check_out_date->toDateString());

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking])
            ->call('payExtension')
            ->assertHasNoErrors();

        $extension->refresh();
        $booking->refresh();

        $this->assertTrue($extension->status === BookingExtensionStatus::Approved);
        $this->assertSame('2026-06-26', $booking->check_out_date->toDateString());
        $this->assertSame(8, $booking->nights_count);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'date' => '2026-06-25',
            'status' => AvailabilityStatus::Booked->value,
        ]);
    }

    public function test_instant_extension_flow_goes_directly_to_payment_and_updates_after_paid(): void
    {
        [$guest, , $booking] = $this->createStay([
            'instant_booking_enabled' => true,
            'requires_host_approval' => false,
        ]);

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking])
            ->set('requestedNewCheckout', '2026-06-26')
            ->call('submit')
            ->assertHasNoErrors();

        $extension = $booking->refresh()->extensions()->latest('id')->firstOrFail();

        $this->assertTrue($extension->status === BookingExtensionStatus::AwaitingPayment);

        app(ExtensionService::class)->markPaid($guest, $extension);

        $booking->refresh();

        $this->assertSame('2026-06-26', $booking->check_out_date->toDateString());
        $this->assertDatabaseHas('payment_records', [
            'booking_id' => $booking->id,
            'provider' => 'extension_demo_manual',
            'status' => 'paid',
        ]);
    }

    public function test_max_nights_are_enforced_for_extension(): void
    {
        [$guest, , $booking] = $this->createStay([
            'max_nights' => 7,
        ]);

        Livewire::actingAs($guest)
            ->test(ExtendStay::class, ['booking' => $booking])
            ->set('requestedNewCheckout', '2026-06-26')
            ->call('submit')
            ->assertHasErrors(['requestedNewCheckout']);

        $this->assertSame(0, $booking->extensions()->count());
    }

    /**
     * @param  array<string, mixed>  $placeOverrides
     * @return array{0: User, 1: User, 2: Booking, 3: SleepingPlace}
     */
    private function createStay(array $placeOverrides = []): array
    {
        $guest = User::factory()->create();
        UserProfile::factory()->for($guest, 'user')->create();

        $host = User::factory()->create(['is_host' => true]);
        UserProfile::factory()->for($host, 'user')->create();
        HostProfile::factory()->for($host, 'user')->create([
            'display_name' => 'Mila Host',
        ]);

        $property = Property::factory()
            ->for($host, 'host')
            ->create([
                'host_user_id' => $host->id,
                'user_id' => $host->id,
                'status' => PropertyStatus::Active,
                'city' => 'Vilnius',
                'district' => 'Old Town',
                'address_line_1' => 'Central Street',
                'show_exact_address_after_payment' => true,
            ]);

        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Shared apartment',
            'summary' => 'A calm apartment.',
            'description' => 'A calm apartment.',
            'check_in_instructions' => 'Use the small entrance.',
            'house_rules_text' => 'Keep shared spaces calm.',
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Shared room A',
            'room_number' => 'A2',
        ]);

        $room->translations()->create([
            'locale' => 'en',
            'title' => 'Shared room A',
            'summary' => 'Shared room.',
            'description' => 'Shared room.',
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Quiet lower bed',
                'place_number' => 'L1',
                'base_price_per_night' => 20,
                'weekly_price' => null,
                'monthly_price' => null,
                'weekend_price' => null,
                'cleaning_fee' => 0,
                'deposit_amount' => 0,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => 30,
                'max_guests' => 1,
                'extensions_allowed' => true,
                'instant_booking_enabled' => false,
                'requires_host_approval' => true,
            ], $placeOverrides));

        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
            'summary' => 'A quiet bed.',
            'description' => 'A quiet bed.',
        ]);

        $booking = Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::InProgress,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-06-18',
            'check_out_date' => '2026-06-24',
            'check_in' => '2026-06-18',
            'check_out' => '2026-06-24',
            'check_in_time' => '15:00',
            'check_out_time' => '10:00',
            'nights_count' => 6,
            'nights' => 6,
            'calendar_days_count' => 7,
            'subtotal_amount' => 120,
            'subtotal' => 120,
            'discount_amount' => 0,
            'cleaning_fee_amount' => 0,
            'cleaning_fee' => 0,
            'deposit_amount' => 0,
            'deposit' => 0,
            'service_fee_amount' => 6,
            'service_fee' => 6,
            'total_amount' => 126,
            'total' => 126,
            'refundable_amount' => 0,
            'non_refundable_amount' => 126,
            'currency' => 'EUR',
            'payment_paid_at' => now(),
            'guest_checked_in_at' => now(),
            'checked_in_at' => now(),
        ]);

        return [$guest, $host, $booking, $place];
    }

    private function createBlockingBooking(User $host, SleepingPlace $place): Booking
    {
        $guest = User::factory()->create();

        return Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $guest->id,
            'guest_user_id' => $guest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-06-25',
            'check_out_date' => '2026-06-27',
            'check_in' => '2026-06-25',
            'check_out' => '2026-06-27',
            'nights_count' => 2,
            'nights' => 2,
            'calendar_days_count' => 3,
            'currency' => 'EUR',
        ]);
    }
}
