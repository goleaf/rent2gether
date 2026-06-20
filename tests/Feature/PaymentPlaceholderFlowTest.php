<?php

namespace Tests\Feature;

use App\Actions\Bookings\BookingSubmit;
use App\Actions\Payments\ConfirmDemoPayment;
use App\Actions\Payments\RecordPaymentFailure;
use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\PaymentPage;
use App\Models\Booking;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\Availability\AvailabilityService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentPlaceholderFlowTest extends TestCase
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
        app()->detectEnvironment(fn (): string => 'testing');
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_payment_page_renders_booking_summary_line_items_and_demo_placeholder(): void
    {
        [$guest, , , $booking] = $this->createPayableBooking();

        $this->actingAs($guest)
            ->get(route('guest.bookings.payment', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(PaymentPage::class)
            ->assertSee(__('booking.payment_page.title', [], 'en'))
            ->assertSee(__('booking.payment_page.method.placeholder', [], 'en'))
            ->assertSee(__('booking.payment_page.actions.mark_paid', [], 'en'))
            ->assertSee('Quiet lower bed');
    }

    public function test_local_demo_payment_creates_record_confirms_booking_and_notifies_host(): void
    {
        [$guest, $host, $place, $booking] = $this->createPayableBooking();

        Livewire::actingAs($guest)
            ->test(PaymentPage::class, ['booking' => $booking])
            ->call('markAsPaid')
            ->assertHasNoErrors();

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::Confirmed);
        $this->assertTrue($booking->payment_status === PaymentStatus::Paid);
        $this->assertNotNull($booking->payment_paid_at);
        $this->assertStringContainsString('Central Street', (string) $booking->check_in_instructions);
        $this->assertStringContainsString('Use the small entrance.', (string) $booking->check_in_instructions);

        $paymentRecord = $booking->paymentRecords()->firstOrFail();

        $this->assertSame(56.0, (float) $paymentRecord->amount);
        $this->assertDatabaseHas('payment_records', [
            'booking_id' => $booking->id,
            'payer_user_id' => $guest->id,
            'provider' => 'demo_manual',
            'currency' => 'EUR',
            'status' => PaymentRecordStatus::Paid->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'booking_payment_received',
            'title_key' => 'notifications.booking_payment_received.title',
            'status' => 'unread',
        ]);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::Booked->value,
        ]);
    }

    public function test_production_cannot_use_demo_payment(): void
    {
        [$guest, , , $booking] = $this->createPayableBooking();

        app()->detectEnvironment(fn (): string => 'production');

        $this->expectException(ValidationException::class);

        try {
            app(ConfirmDemoPayment::class)->handle($guest, $booking);
        } finally {
            $booking->refresh();
            $this->assertTrue($booking->status === BookingStatus::AwaitingPayment);
            $this->assertTrue($booking->payment_status === PaymentStatus::AwaitingPayment);
            $this->assertSame(0, $booking->paymentRecords()->count());
        }
    }

    public function test_payment_failure_keeps_booking_awaiting_payment(): void
    {
        [$guest, , , $booking] = $this->createPayableBooking();

        app(RecordPaymentFailure::class)->handle($guest, $booking, 'demo_declined');

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::AwaitingPayment);
        $this->assertTrue($booking->payment_status === PaymentStatus::Failed);

        $this->assertDatabaseHas('payment_records', [
            'booking_id' => $booking->id,
            'payer_user_id' => $guest->id,
            'provider' => 'demo_manual',
            'status' => PaymentRecordStatus::Failed->value,
        ]);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $booking->sleeping_place_id,
            'booking_id' => $booking->id,
            'status' => AvailabilityStatus::PendingPayment->value,
        ]);
    }

    public function test_paid_booking_blocks_dates(): void
    {
        [$guest, , $place, $booking] = $this->createPayableBooking();

        app(ConfirmDemoPayment::class)->handle($guest, $booking);

        $this->assertFalse(app(AvailabilityService::class)->isAvailable($place, '2026-07-10', '2026-07-12'));
        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::Booked->value,
        ]);
    }

    /**
     * @return array{0: User, 1: User, 2: SleepingPlace, 3: Booking}
     */
    private function createPayableBooking(): array
    {
        $guest = User::factory()->create();
        UserProfile::factory()->for($guest, 'user')->create();
        GuestPreference::factory()->for($guest, 'user')->create();

        $host = User::factory()->create(['is_host' => true]);
        HostProfile::factory()->for($host, 'user')->create([
            'default_check_out_time' => '10:00',
            'default_cancellation_policy' => 'flexible',
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
                'house_number' => '12',
                'apartment_number' => '4',
                'show_exact_address_after_payment' => true,
            ]);
        $property->translations()->create([
            'locale' => 'en',
            'title' => 'Shared apartment',
            'summary' => 'A calm apartment.',
            'description' => 'A calm apartment.',
            'check_in_instructions' => 'Use the small entrance.',
        ]);
        $property->translations()->create([
            'locale' => 'ru',
            'title' => 'Общая квартира',
            'summary' => 'Спокойная квартира.',
            'description' => 'Спокойная квартира.',
            'check_in_instructions' => 'Используйте маленький вход.',
        ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'beds_count' => 2,
            'max_guests' => 2,
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Quiet lower bed',
                'base_price_per_night' => 10,
                'cleaning_fee' => 5,
                'deposit_amount' => 30,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_guests' => 1,
                'instant_booking_enabled' => true,
                'requires_host_approval' => false,
            ]);
        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
            'summary' => 'A calm sleeping place.',
            'description' => 'A calm sleeping place.',
        ]);

        $booking = app(BookingSubmit::class)->handle($guest, $place, [
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'check_in_time' => '15:00',
            'check_out_time' => '10:00',
            'arrival_time' => '15:30',
            'guests_count' => 1,
            'guest_message' => 'I will arrive quietly.',
            'rules_accepted' => true,
            'profile_ready' => true,
        ]);

        return [$guest, $host, $place, $booking];
    }
}
