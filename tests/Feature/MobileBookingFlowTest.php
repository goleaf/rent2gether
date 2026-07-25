<?php

namespace Tests\Feature;

use App\Actions\Bookings\BookingSubmit;
use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Booking\BookingReview;
use App\Livewire\Bookings\Create\BookingCreatePage;
use App\Models\AvailabilityDay;
use App\Models\Booking;
use App\Models\GuestPreference;
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
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class MobileBookingFlowTest extends TestCase
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

    public function test_instant_booking_creates_awaiting_payment_booking_with_price_lines_history_and_hold(): void
    {
        [$guest, $place] = $this->createPlace([
            'instant_booking_enabled' => true,
            'requires_host_approval' => false,
        ]);

        $booking = app(BookingSubmit::class)->handle($guest, $place, $this->bookingData());

        $this->assertTrue($booking->status === BookingStatus::AwaitingPayment);
        $this->assertTrue($booking->payment_status === PaymentStatus::AwaitingPayment);
        $this->assertTrue($booking->booking_type === BookingType::Instant);
        $this->assertNotNull($booking->rules_accepted_at);
        $this->assertNotNull($booking->availability_hold_expires_at);
        $this->assertSame(2, $booking->nights_count);
        $this->assertSame(3, $booking->calendar_days_count);
        $this->assertGreaterThan(0, $booking->priceLines()->count());

        $this->assertDatabaseHas('booking_status_histories', [
            'booking_id' => $booking->id,
            'from_status' => null,
            'to_status' => BookingStatus::AwaitingPayment->value,
            'changed_by_user_id' => $guest->id,
        ]);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'date' => '2026-07-10',
            'status' => AvailabilityStatus::PendingPayment->value,
        ]);
    }

    public function test_instant_booking_can_be_confirmed_when_payment_mode_allows_later_payment(): void
    {
        [$guest, $place] = $this->createPlace([
            'instant_booking_enabled' => true,
            'requires_host_approval' => false,
        ]);

        $booking = app(BookingSubmit::class)->handle($guest, $place, array_merge($this->bookingData(), [
            'payment_mode' => 'pay_later',
        ]));

        $this->assertTrue($booking->status === BookingStatus::Confirmed);
        $this->assertTrue($booking->payment_status === PaymentStatus::Unpaid);
        $this->assertNull($booking->availability_hold_expires_at);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'status' => AvailabilityStatus::Booked->value,
        ]);
    }

    public function test_request_booking_creates_awaiting_host_approval_booking(): void
    {
        [$guest, $place] = $this->createPlace([
            'instant_booking_enabled' => false,
            'requires_host_approval' => true,
        ]);

        $booking = app(BookingSubmit::class)->handle($guest, $place, $this->bookingData());

        $this->assertTrue($booking->status === BookingStatus::AwaitingHostApproval);
        $this->assertTrue($booking->payment_status === PaymentStatus::Unpaid);
        $this->assertTrue($booking->booking_type === BookingType::HostApproval);
        $this->assertNotNull($booking->availability_hold_expires_at);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'status' => AvailabilityStatus::PendingApproval->value,
        ]);
    }

    public function test_unavailable_place_cannot_be_booked(): void
    {
        [$guest, $place] = $this->createPlace();
        AvailabilityDay::factory()->for($place)->create([
            'date' => '2026-07-11',
            'status' => AvailabilityStatus::Repair,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(BookingSubmit::class)->handle($guest, $place, $this->bookingData());
        } finally {
            $this->assertSame(0, Booking::query()->count());
        }
    }

    public function test_rules_must_be_accepted_before_submit(): void
    {
        [$guest, $place] = $this->createPlace([
            'instant_booking_enabled' => true,
            'requires_host_approval' => false,
        ]);

        Livewire::actingAs($guest)
            ->test(BookingReview::class, ['sleepingPlace' => $place])
            ->set('checkIn', '2026-07-10')
            ->set('checkOut', '2026-07-12')
            ->set('profileReady', true)
            ->call('submit')
            ->assertHasErrors(['rulesAccepted']);

        $this->assertSame(0, Booking::query()->count());
    }

    public function test_double_booking_is_prevented_by_calendar_hold(): void
    {
        [$firstGuest, $place] = $this->createPlace([
            'instant_booking_enabled' => true,
            'requires_host_approval' => false,
        ]);
        $secondGuest = $this->guest();

        app(BookingSubmit::class)->handle($firstGuest, $place, $this->bookingData());

        $this->expectException(ValidationException::class);

        try {
            app(BookingSubmit::class)->handle($secondGuest, $place, $this->bookingData());
        } finally {
            $this->assertSame(1, Booking::query()->count());
        }
    }

    public function test_booking_create_page_renders_date_flow_in_english_and_russian(): void
    {
        [$guest, $place] = $this->createPlace();

        $this->actingAs($guest)
            ->get(route('places.book', [
                'locale' => 'en',
                'sleepingPlace' => $place,
                'in' => '2026-07-10',
                'out' => '2026-07-12',
            ]))
            ->assertOk()
            ->assertSeeLivewire(BookingCreatePage::class)
            ->assertSee(__('bookings.create.title', [], 'en'))
            ->assertSee(__('booking_dates.title', [], 'en'))
            ->assertSee('Quiet lower bed');

        $this->actingAs($guest)
            ->get(route('places.book', [
                'locale' => 'ru',
                'sleepingPlace' => $place,
                'in' => '2026-07-10',
                'out' => '2026-07-12',
            ]))
            ->assertOk()
            ->assertSeeLivewire(BookingCreatePage::class)
            ->assertSee(__('bookings.create.title', [], 'ru'))
            ->assertSee(__('booking_dates.title', [], 'ru'))
            ->assertSee('Тихое нижнее место');
    }

    /**
     * @param  array<string, mixed>  $placeAttributes
     * @return array{0: User, 1: SleepingPlace}
     */
    private function createPlace(array $placeAttributes = []): array
    {
        $guest = $this->guest();
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
            ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'beds_count' => 2,
            'max_guests' => 2,
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create(array_merge([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Quiet lower bed',
                'base_price_per_night' => 20,
                'weekly_price' => null,
                'monthly_price' => null,
                'weekend_price' => null,
                'cleaning_fee' => 5,
                'deposit_amount' => 30,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_nights' => null,
                'max_guests' => 1,
            ], $placeAttributes));

        $place->translations()->create([
            'locale' => 'en',
            'title' => 'Quiet lower bed',
            'summary' => 'A calm sleeping place.',
            'description' => 'A calm sleeping place.',
        ]);
        $place->translations()->create([
            'locale' => 'ru',
            'title' => 'Тихое нижнее место',
            'summary' => 'Спокойное спальное место.',
            'description' => 'Спокойное спальное место.',
        ]);

        $rule = Rule::factory()->create([
            'slug' => 'quiet-hours-after-22',
            'category' => 'quiet_hours',
            'status' => 'active',
        ]);
        $rule->translations()->create([
            'locale' => 'en',
            'name' => 'Quiet hours after 22:00',
            'name_normalized' => 'quiet hours after 22:00',
        ]);
        $rule->translations()->create([
            'locale' => 'ru',
            'name' => 'Тихие часы после 22:00',
            'name_normalized' => 'тихие часы после 22:00',
        ]);
        $place->rules()->attach($rule);

        return [$guest, $place];
    }

    private function guest(): User
    {
        $guest = User::factory()->create();
        UserProfile::factory()->for($guest, 'user')->create([
            'avatar_path' => 'avatars/guest.jpg',
            'about' => 'Calm guest.',
            'phone_verified_at' => now(),
        ]);
        GuestPreference::factory()->for($guest, 'user')->create();

        return $guest;
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingData(): array
    {
        return [
            'check_in' => '2026-07-10',
            'check_out' => '2026-07-12',
            'check_in_time' => '15:00',
            'check_out_time' => '10:00',
            'arrival_time' => '15:30',
            'guests_count' => 1,
            'guest_message' => 'I will arrive quietly.',
            'rules_accepted' => true,
            'profile_ready' => true,
        ];
    }
}
