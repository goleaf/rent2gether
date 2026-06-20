<?php

namespace Tests\Feature;

use App\Actions\Bookings\AcceptBookingRequest;
use App\Actions\Bookings\BookingSubmit;
use App\Enums\AvailabilityStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Shell\HostRequestsPage;
use App\Models\Booking;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class HostBookingRequestManagementTest extends TestCase
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

    public function test_host_requests_page_shows_incoming_request_and_guest_summary(): void
    {
        [$host, $guest, $place, $booking] = $this->createBookingRequest();

        $this->actingAs($host)
            ->get('/en/host/requests')
            ->assertOk()
            ->assertSeeLivewire(HostRequestsPage::class)
            ->assertSee(__('shell.pages.host.requests.title', [], 'en'))
            ->assertSee('Quiet lower bed')
            ->assertSee(__('host.requests.card.guest', ['name' => 'Calm Guest'], 'en'));

        Livewire::actingAs($host)
            ->test(HostRequestsPage::class)
            ->call('selectRequest', $booking->id)
            ->assertSet('selectedBookingId', $booking->id)
            ->assertSee(__('host.requests.compatibility.title', [], 'en'))
            ->assertSee(__('host.requests.profile.relevant_preferences', [], 'en'))
            ->assertSee('Calm guest.');

        $this->actingAs($host)
            ->get('/ru/host/requests')
            ->assertOk()
            ->assertSee(__('shell.pages.host.requests.title', [], 'ru'));

        $this->assertSame($place->id, $booking->sleeping_place_id);
        $this->assertSame($guest->id, $booking->guest_user_id);
    }

    public function test_host_can_accept_own_request(): void
    {
        [$host, $guest, $place, $booking] = $this->createBookingRequest();

        Livewire::actingAs($host)
            ->test(HostRequestsPage::class)
            ->call('selectRequest', $booking->id)
            ->set('expiryAt', '2026-06-20T12:00')
            ->set('acceptMessage', 'Welcome, payment details are ready.')
            ->call('acceptSelected')
            ->assertHasNoErrors();

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::AwaitingPayment);
        $this->assertTrue($booking->payment_status === PaymentStatus::AwaitingPayment);
        $this->assertNotNull($booking->payment_deadline_at);
        $this->assertSame('Welcome, payment details are ready.', $booking->host_response);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::PendingPayment->value,
        ]);

        $this->assertDatabaseHas('booking_status_histories', [
            'booking_id' => $booking->id,
            'from_status' => BookingStatus::AwaitingHostApproval->value,
            'to_status' => BookingStatus::AwaitingPayment->value,
            'changed_by_user_id' => $host->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_request_accepted',
            'title_key' => 'notifications.booking_request_accepted.title',
            'status' => 'unread',
        ]);
    }

    public function test_host_cannot_accept_request_for_another_host(): void
    {
        [, , , $booking] = $this->createBookingRequest();
        $otherHost = User::factory()->create(['is_host' => true]);

        $this->expectException(AuthorizationException::class);

        try {
            app(AcceptBookingRequest::class)->handle($otherHost, $booking);
        } finally {
            $this->assertTrue($booking->refresh()->status === BookingStatus::AwaitingHostApproval);
        }
    }

    public function test_accepting_rechecks_availability_before_changing_status(): void
    {
        [$host, , $place, $booking] = $this->createBookingRequest();
        $secondGuest = $this->guest('Other Guest');

        Booking::factory()->create([
            'bed_id' => null,
            'guest_id' => $secondGuest->id,
            'guest_user_id' => $secondGuest->id,
            'host_id' => $host->id,
            'host_user_id' => $host->id,
            'property_id' => $place->property_id,
            'room_id' => $place->room_id,
            'sleeping_place_id' => $place->id,
            'check_in' => '2026-07-11',
            'check_out' => '2026-07-13',
            'check_in_date' => '2026-07-11',
            'check_out_date' => '2026-07-13',
            'nights' => 2,
            'nights_count' => 2,
            'calendar_days_count' => 3,
            'status' => BookingStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->expectException(ValidationException::class);

        try {
            app(AcceptBookingRequest::class)->handle($host, $booking);
        } finally {
            $this->assertTrue($booking->refresh()->status === BookingStatus::AwaitingHostApproval);
            $this->assertDatabaseMissing('notifications', [
                'user_id' => $booking->guest_user_id,
                'type' => 'booking_request_accepted',
            ]);
        }
    }

    public function test_decline_releases_hold_and_notifies_guest(): void
    {
        [$host, $guest, $place, $booking] = $this->createBookingRequest();

        Livewire::actingAs($host)
            ->test(HostRequestsPage::class)
            ->call('selectRequest', $booking->id)
            ->set('declineReason', 'dates_unavailable')
            ->set('declineMessage', 'The dates are no longer open.')
            ->call('declineSelected')
            ->assertHasNoErrors();

        $booking->refresh();

        $this->assertTrue($booking->status === BookingStatus::DeclinedByHost);
        $this->assertSame('dates_unavailable', $booking->cancellation_reason);
        $this->assertSame('The dates are no longer open.', $booking->host_response);
        $this->assertNull($booking->availability_hold_expires_at);

        $this->assertDatabaseMissing('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => $booking->id,
            'status' => AvailabilityStatus::PendingApproval->value,
        ]);

        $this->assertDatabaseHas('availability_days', [
            'sleeping_place_id' => $place->id,
            'booking_id' => null,
            'date' => '2026-07-10 00:00:00',
            'status' => AvailabilityStatus::Available->value,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_request_declined',
            'title_key' => 'notifications.booking_request_declined.title',
            'status' => 'unread',
        ]);
    }

    /**
     * @return array{0: User, 1: User, 2: SleepingPlace, 3: Booking}
     */
    private function createBookingRequest(): array
    {
        $guest = $this->guest('Calm Guest');
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
                'rules' => [],
                'amenities' => ['wifi', 'kitchen', 'washer'],
                'status' => PropertyStatus::Active,
            ]);

        $room = Room::factory()->for($property)->create([
            'status' => RoomStatus::Active,
            'title' => 'Blue room',
            'beds_count' => 2,
            'max_guests' => 2,
        ]);

        $place = SleepingPlace::factory()
            ->for($room)
            ->for($property)
            ->create([
                'status' => SleepingPlaceStatus::Active,
                'display_name' => 'Quiet lower bed',
                'base_price_per_night' => 20,
                'cleaning_fee' => 5,
                'deposit_amount' => 30,
                'currency' => 'EUR',
                'min_nights' => 1,
                'max_guests' => 1,
                'instant_booking_enabled' => false,
                'requires_host_approval' => true,
            ]);

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

        return [$host, $guest, $place, $booking];
    }

    private function guest(string $displayName): User
    {
        $guest = User::factory()->create([
            'email_verified_at' => now(),
            'phone_verified' => true,
            'identity_verified' => true,
            'identity_verified_at' => now(),
            'rating_as_guest' => 4.7,
            'completed_stays_count' => 3,
            'complaints_count' => 1,
            'travel_purpose' => 'work',
        ]);

        UserProfile::factory()->for($guest, 'user')->create([
            'display_name' => $displayName,
            'avatar_path' => 'avatars/guest.jpg',
            'about' => 'Calm guest.',
            'phone_verified_at' => now(),
            'email_verified_at' => now(),
            'identity_verified_at' => now(),
            'travel_purpose' => 'work',
            'rating_average' => 4.7,
            'reviews_count' => 5,
            'complaints_count' => 1,
        ]);

        GuestPreference::factory()->for($guest, 'user')->create([
            'avoids_smoking' => true,
            'needs_quiet_hours' => true,
            'wants_locker' => true,
        ]);

        return $guest;
    }
}
