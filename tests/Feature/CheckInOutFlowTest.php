<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Checkin\CheckIn;
use App\Livewire\Checkin\CheckOut;
use App\Livewire\Checkin\ProblemReport;
use App\Livewire\Host\ManageBooking;
use App\Livewire\Trips\BookingDetail;
use App\Models\Booking;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CheckInOutFlowTest extends TestCase
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

    public function test_guest_check_in_records_confirmation_and_moves_booking_to_checked_in(): void
    {
        [$guest, , $booking] = $this->createStay(BookingStatus::Confirmed);

        Livewire::actingAs($guest)
            ->test(CheckIn::class, ['booking' => $booking])
            ->set('propertyFound', true)
            ->set('keysReceived', true)
            ->set('roomSeen', true)
            ->set('sleepingPlaceShown', true)
            ->set('rulesSeen', true)
            ->set('everythingOk', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('guest.bookings.show', ['locale' => 'en', 'booking' => $booking]));

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::CheckedIn);
        $this->assertNotNull($booking->guest_checked_in_at);
        $this->assertNotNull($booking->checked_in_at);

        $this->assertDatabaseHas('checkin_records', [
            'booking_id' => $booking->id,
            'property_found' => true,
            'keys_received' => true,
            'room_shown' => true,
            'sleeping_place_shown' => true,
            'rules_explained' => true,
            'everything_ok' => true,
            'guest_confirmed' => true,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('booking_status_histories', [
            'booking_id' => $booking->id,
            'from_status' => BookingStatus::Confirmed->value,
            'to_status' => BookingStatus::CheckedIn->value,
            'changed_by_user_id' => $guest->id,
        ]);
    }

    public function test_check_in_problem_report_stores_description_and_photos(): void
    {
        Storage::fake('public');

        [$guest, , $booking] = $this->createStay(BookingStatus::Confirmed);

        Livewire::actingAs($guest)
            ->test(ProblemReport::class, ['booking' => $booking])
            ->set('problemDescription', 'The key code did not open the front door.')
            ->set('photos', [
                UploadedFile::fake()->image('door.jpg', 800, 600),
            ])
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('guest.bookings.show', ['locale' => 'en', 'booking' => $booking]));

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::CheckedIn);
        $this->assertTrue((bool) $booking->has_complaint);

        $record = $booking->checkinRecord()->firstOrFail();

        $this->assertTrue((bool) $record->problem_reported);
        $this->assertTrue((bool) $record->has_issue);
        $this->assertSame('The key code did not open the front door.', $record->problem_description);
        $this->assertCount(1, $record->problem_media);
        $this->assertStringEndsWith('.webp', $record->problem_media[0]);

        Storage::disk('public')->assertExists($record->problem_media[0]);
    }

    public function test_host_check_in_confirm_moves_booking_to_in_progress(): void
    {
        [$guest, $host, $booking] = $this->createStay(BookingStatus::Confirmed);

        Livewire::actingAs($guest)
            ->test(CheckIn::class, ['booking' => $booking])
            ->set('propertyFound', true)
            ->set('keysReceived', true)
            ->set('roomSeen', true)
            ->set('sleepingPlaceShown', true)
            ->set('rulesSeen', true)
            ->set('everythingOk', true)
            ->call('submit')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(ManageBooking::class, ['booking' => $booking->refresh()])
            ->call('confirmCheckIn')
            ->assertHasNoErrors();

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::InProgress);
        $this->assertNotNull($booking->host_confirmed_checkin_at);

        $this->assertDatabaseHas('checkin_records', [
            'booking_id' => $booking->id,
            'host_confirmed' => true,
            'status' => 'completed',
        ]);
    }

    public function test_guest_check_out_records_confirmation_and_moves_booking_to_checked_out(): void
    {
        [$guest, , $booking] = $this->createStay(BookingStatus::InProgress, [
            'guest_checked_in_at' => now()->subDay(),
            'checked_in_at' => now()->subDay(),
            'host_confirmed_checkin_at' => now()->subDay(),
        ]);

        Livewire::actingAs($guest)
            ->test(CheckOut::class, ['booking' => $booking])
            ->set('keysReturned', true)
            ->set('belongingsRemoved', true)
            ->set('lockerEmptied', true)
            ->set('placeClean', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('guest.bookings.show', ['locale' => 'en', 'booking' => $booking]));

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::CheckedOut);
        $this->assertNotNull($booking->guest_checked_out_at);
        $this->assertNotNull($booking->checked_out_at);

        $this->assertDatabaseHas('checkout_records', [
            'booking_id' => $booking->id,
            'keys_returned' => true,
            'belongings_removed' => true,
            'locker_emptied' => true,
            'place_clean' => true,
            'guest_confirmed' => true,
            'status' => 'pending_host',
        ]);
    }

    public function test_host_check_out_confirm_completes_booking_and_releases_deposit(): void
    {
        [$guest, $host, $booking] = $this->createStay(BookingStatus::InProgress, [
            'guest_checked_in_at' => now()->subDay(),
            'checked_in_at' => now()->subDay(),
            'host_confirmed_checkin_at' => now()->subDay(),
        ]);

        Livewire::actingAs($guest)
            ->test(CheckOut::class, ['booking' => $booking])
            ->set('keysReturned', true)
            ->set('belongingsRemoved', true)
            ->set('lockerEmptied', true)
            ->set('placeClean', true)
            ->call('submit')
            ->assertHasNoErrors();

        Livewire::actingAs($host)
            ->test(ManageBooking::class, ['booking' => $booking->refresh()])
            ->call('confirmCheckOut')
            ->assertHasNoErrors();

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::Completed);
        $this->assertNotNull($booking->host_confirmed_checkout_at);
        $this->assertNotNull($booking->deposit_released_at);

        $this->assertDatabaseHas('checkout_records', [
            'booking_id' => $booking->id,
            'host_confirmed' => true,
            'damage_found' => false,
            'deposit_action' => 'return',
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('deposit_records', [
            'booking_id' => $booking->id,
            'status' => 'released',
            'withheld_amount' => 0,
        ]);
    }

    public function test_completed_booking_can_be_reviewed_from_trip_detail(): void
    {
        [$guest, , $booking] = $this->createStay(BookingStatus::Completed, [
            'guest_checked_in_at' => now()->subDays(3),
            'checked_in_at' => now()->subDays(3),
            'guest_checked_out_at' => now()->subDay(),
            'checked_out_at' => now()->subDay(),
            'host_confirmed_checkout_at' => now()->subDay(),
        ]);

        $this->actingAs($guest)
            ->get(route('guest.bookings.show', ['locale' => 'en', 'booking' => $booking]))
            ->assertOk()
            ->assertSeeLivewire(BookingDetail::class)
            ->assertSee(__('booking.trips.actions.review', [], 'en'));
    }

    /**
     * @param  array<string, mixed>  $bookingOverrides
     * @return array{0: User, 1: User, 2: Booking}
     */
    private function createStay(BookingStatus $status, array $bookingOverrides = []): array
    {
        $guest = User::factory()->create(['name' => 'Calm Guest']);
        UserProfile::factory()->for($guest, 'user')->create();

        $host = User::factory()->create([
            'is_host' => true,
            'name' => 'Mila Host',
            'phone' => '+37060000000',
        ]);
        UserProfile::factory()->for($host, 'user')->create(['phone' => '+37060000001']);
        HostProfile::factory()->for($host, 'user')->create(['display_name' => 'Mila Host']);

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
        $room->translations()->createMany([
            [
                'locale' => 'en',
                'title' => 'Shared room A',
                'summary' => 'Shared room.',
                'description' => 'Shared room.',
            ],
            [
                'locale' => 'ru',
                'title' => 'Общая комната A',
                'summary' => 'Общая комната.',
                'description' => 'Общая комната.',
            ],
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
        $place->translations()->createMany([
            [
                'locale' => 'en',
                'title' => 'Quiet lower bed',
                'summary' => 'A quiet bed.',
                'description' => 'A quiet bed.',
            ],
            [
                'locale' => 'ru',
                'title' => 'Тихое нижнее место',
                'summary' => 'Тихое место.',
                'description' => 'Тихое место.',
            ],
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
            'status' => $status,
            'payment_status' => PaymentStatus::Paid,
            'check_in_date' => '2026-06-18',
            'check_out_date' => '2026-06-24',
            'check_in' => '2026-06-18',
            'check_out' => '2026-06-24',
            'check_in_time' => '15:00',
            'check_out_time' => '10:00',
            'arrival_time' => '16:00',
            'nights_count' => 6,
            'nights' => 6,
            'calendar_days_count' => 7,
            'subtotal_amount' => 60,
            'subtotal' => 60,
            'cleaning_fee_amount' => 5,
            'cleaning_fee' => 5,
            'deposit_amount' => 30,
            'deposit' => 30,
            'service_fee_amount' => 3,
            'service_fee' => 3,
            'total_amount' => 98,
            'total' => 98,
            'refundable_amount' => 30,
            'non_refundable_amount' => 68,
            'currency' => 'EUR',
            'payment_paid_at' => now(),
            'check_in_instructions' => "Central Street, 12, 4\n\nUse the small entrance.",
            ...$bookingOverrides,
        ]);

        $booking->priceLines()->createMany([
            [
                'type' => 'nightly_base',
                'label_key' => 'booking.price_lines.nightly_base',
                'amount' => 60,
                'currency' => 'EUR',
                'is_refundable' => false,
            ],
            [
                'type' => 'deposit',
                'label_key' => 'booking.price_lines.deposit',
                'amount' => 30,
                'currency' => 'EUR',
                'is_refundable' => true,
            ],
            [
                'type' => 'total',
                'label_key' => 'booking.price_lines.total',
                'amount' => 98,
                'currency' => 'EUR',
                'is_refundable' => false,
            ],
        ]);

        $booking->depositRecords()->create([
            'amount' => 30,
            'currency' => 'EUR',
            'status' => 'held',
            'held_at' => now(),
            'withheld_amount' => 0,
        ]);

        return [$guest, $host, $booking];
    }
}
