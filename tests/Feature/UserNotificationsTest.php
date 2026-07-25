<?php

namespace Tests\Feature;

use App\Actions\Bookings\BookingSubmit;
use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoomStatus;
use App\Enums\SleepingPlaceStatus;
use App\Livewire\Notifications\NotificationBell;
use App\Livewire\Notifications\NotificationsPage;
use App\Models\GuestPreference;
use App\Models\HostProfile;
use App\Models\Notification;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Models\UserProfile;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserNotificationsTest extends TestCase
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

    public function test_notification_created_on_booking_request(): void
    {
        [$guest, $host, $place] = $this->createBookablePlace([
            'instant_booking_enabled' => false,
            'requires_host_approval' => true,
        ]);

        $booking = app(BookingSubmit::class)->handle($guest, $place, $this->bookingData());

        $this->assertTrue($booking->status === BookingStatus::AwaitingHostApproval);
        $this->assertTrue($booking->payment_status === PaymentStatus::Unpaid);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $guest->id,
            'type' => 'booking_request_sent',
            'title_key' => 'notifications.booking_request_sent.title',
            'body_key' => 'notifications.booking_request_sent.body',
            'status' => 'unread',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'new_booking_request',
            'title_key' => 'notifications.new_booking_request.title',
            'body_key' => 'notifications.new_booking_request.body',
            'status' => 'unread',
        ]);
    }

    public function test_notification_text_is_localized_with_params(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'notifiable_id' => $user->id,
            'type' => 'booking_request_sent',
            'title_key' => 'notifications.booking_request_sent.title',
            'body_key' => 'notifications.booking_request_sent.body',
            'data' => [
                'params' => [
                    'place' => 'Quiet lower bed',
                    'reference' => 'RTG-1001',
                ],
            ],
        ]);

        $this->assertSame('Booking request sent', $notification->title('en'));
        $this->assertSame('Запрос отправлен', $notification->title('ru'));
        $this->assertStringContainsString('Quiet lower bed', $notification->body('en'));
        $this->assertStringContainsString('Quiet lower bed', $notification->body('ru'));
    }

    public function test_read_and_unread_state_can_be_updated(): void
    {
        $user = User::factory()->create();
        $notification = $this->notificationFor($user);

        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->call('markAsRead', $notification->id)
            ->assertHasNoErrors();

        $notification->refresh();

        $this->assertNotNull($notification->read_at);
        $this->assertSame('read', $notification->status);

        $secondNotification = $this->notificationFor($user);

        Livewire::actingAs($user)
            ->test(NotificationsPage::class)
            ->call('markAllAsRead')
            ->assertHasNoErrors();

        $this->assertSame('read', $secondNotification->refresh()->status);
        $this->assertSame(0, Notification::query()->forUser($user)->unread()->count());
    }

    public function test_mobile_notification_component_and_page_render(): void
    {
        $user = User::factory()->create();
        $this->notificationFor($user);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSee(__('notifications.bell.label'))
            ->assertSee('1');

        $this->actingAs($user)
            ->get('/en/notifications')
            ->assertOk()
            ->assertSeeLivewire(NotificationsPage::class)
            ->assertSee(__('notifications.page.title', [], 'en'))
            ->assertSee(__('notifications.status.unread', [], 'en'));
    }

    public function test_notifications_page_hides_unsafe_action_urls(): void
    {
        $user = User::factory()->create();

        $this->notificationFor($user)->forceFill([
            'action_url' => 'javascript:alert(1)',
        ])->save();

        $this->actingAs($user)
            ->get('/en/notifications')
            ->assertOk()
            ->assertDontSee('href="javascript:alert(1)"', false)
            ->assertDontSee('javascript:alert(1)', false);
    }

    public function test_notifications_page_renders_internal_action_urls(): void
    {
        $user = User::factory()->create();

        $this->notificationFor($user)->forceFill([
            'action_url' => route('guest.bookings.payment', ['locale' => 'en', 'booking' => 123]),
        ])->save();

        $this->actingAs($user)
            ->get('/en/notifications')
            ->assertOk()
            ->assertSee('/en/bookings/123/payment', false)
            ->assertSee(__('notifications.actions.open', [], 'en'));
    }

    /**
     * @param  array<string, mixed>  $placeAttributes
     * @return array{0: User, 1: User, 2: SleepingPlace}
     */
    private function createBookablePlace(array $placeAttributes = []): array
    {
        $guest = User::factory()->create();
        UserProfile::factory()->for($guest, 'user')->create([
            'avatar_path' => 'avatars/guest.jpg',
            'about' => 'Calm guest.',
            'phone_verified_at' => now(),
        ]);
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

        return [$guest, $host, $place];
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

    private function notificationFor(User $user): Notification
    {
        return Notification::factory()->create([
            'user_id' => $user->id,
            'notifiable_id' => $user->id,
            'type' => 'booking_request_sent',
            'title_key' => 'notifications.booking_request_sent.title',
            'body_key' => 'notifications.booking_request_sent.body',
            'data' => [
                'params' => [
                    'place' => 'Quiet lower bed',
                    'reference' => 'RTG-1001',
                ],
            ],
        ]);
    }
}
