<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Livewire\Host\Notifications\HostUrgentNotificationsPanel;
use App\Livewire\Notifications\NotificationCenterPage;
use App\Livewire\Notifications\NotificationSettingsPage;
use App\Models\Booking;
use App\Models\ConversationSystemEvent;
use App\Models\HostRepresentative;
use App\Models\Notification;
use App\Models\NotificationDigest;
use App\Models\NotificationEvent;
use App\Models\NotificationReminder;
use App\Models\Property;
use App\Models\Room;
use App\Models\SleepingPlace;
use App\Models\User;
use App\Services\Notifications\ConversationNotificationIntegrationService;
use App\Services\Notifications\NotificationActionService;
use App\Services\Notifications\NotificationDeduplicationService;
use App\Services\Notifications\NotificationDigestService;
use App\Services\Notifications\NotificationDueProcessorService;
use App\Services\Notifications\NotificationEventService;
use App\Services\Notifications\NotificationNotificationCenterService;
use App\Services\Notifications\NotificationPreferenceService;
use App\Services\Notifications\NotificationPrivacyService;
use App\Services\Notifications\NotificationQuietHoursService;
use App\Services\Notifications\NotificationRecipientService;
use App\Services\Notifications\NotificationReminderService;
use App\Services\Notifications\NotificationService;
use App\Services\Notifications\NotificationTemplateService;
use App\Services\Notifications\NotificationThrottleService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\CursorPaginator;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsPointTwentyFiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow('2026-06-22 10:00:00');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function test_templates_events_context_notifications_and_privacy_work(): void
    {
        [$guest, $host, $booking] = $this->bookingContext();

        $templates = app(NotificationTemplateService::class)->seedDefaultTemplates();

        $this->assertGreaterThanOrEqual(30, $templates->count());
        $this->assertTrue(app(NotificationTemplateService::class)->isCritical('host_unresponsive_reported'));

        $event = app(NotificationEventService::class)->createBookingEvent($booking, 'payment_required', [
            'source_type' => 'payment',
            'source_id' => 55,
        ]);

        $guestNotification = app(NotificationService::class)->createFromEvent($event, $guest, 'payment_required', [
            'recipient_type' => 'guest',
        ]);
        $hostNotification = app(NotificationService::class)->createFromEvent($event, $host, 'guest_sent_message', [
            'recipient_type' => 'host',
        ]);

        $this->assertInstanceOf(NotificationEvent::class, $event);
        $this->assertSame($booking->id, $guestNotification->booking_id);
        $this->assertSame($booking->property_id, $guestNotification->property_id);
        $this->assertSame($booking->room_id, $guestNotification->room_id);
        $this->assertSame($booking->sleeping_place_id, $guestNotification->sleeping_place_id);
        $this->assertSame('open_payment', $guestNotification->action_type);
        $this->assertSame('notifications.events.payment_required', $guestNotification->title_translation_key);
        $this->assertDatabaseHas('notification_deliveries', [
            'notification_id' => $guestNotification->id,
            'recipient_user_id' => $guest->id,
            'channel' => 'in_app',
        ]);
        $this->assertTrue(app(NotificationPrivacyService::class)->canView($guest, $guestNotification));
        $this->assertFalse(app(NotificationPrivacyService::class)->canView($guest, $hostNotification));
        $this->assertTrue(app(NotificationPrivacyService::class)->canView($host, $hostNotification));
    }

    public function test_notification_action_services_expose_only_safe_internal_action_urls(): void
    {
        $guest = User::factory()->create();
        $unsafeNotification = Notification::factory()->create([
            'user_id' => $guest->id,
            'recipient_user_id' => $guest->id,
            'action_type' => 'open_booking',
            'action_url' => 'javascript:alert(1)',
            'status' => 'created',
            'expired_at' => null,
        ]);
        $internalNotification = Notification::factory()->create([
            'user_id' => $guest->id,
            'recipient_user_id' => $guest->id,
            'action_type' => 'open_booking',
            'action_url' => url('/en/bookings/123/payment?from=notification#pay'),
            'status' => 'created',
            'expired_at' => null,
        ]);
        $cancelledNotification = Notification::factory()->create([
            'user_id' => $guest->id,
            'recipient_user_id' => $guest->id,
            'action_type' => 'open_booking',
            'action_url' => url('/en/bookings/123/payment'),
            'status' => 'cancelled',
            'expired_at' => null,
        ]);

        $actions = app(NotificationActionService::class);
        $privacy = app(NotificationPrivacyService::class);

        $this->assertNull($actions->getActionUrl($unsafeNotification));
        $this->assertNull($privacy->filterForUser($guest, $unsafeNotification)['action_url']);
        $this->assertSame('/en/bookings/123/payment?from=notification#pay', $actions->getActionUrl($internalNotification));
        $this->assertSame('/en/bookings/123/payment?from=notification#pay', $privacy->filterForUser($guest, $internalNotification)['action_url']);
        $this->assertNull($actions->getActionUrl($cancelledNotification));
        $this->assertNull($privacy->filterForUser($guest, $cancelledNotification)['action_url']);
    }

    public function test_deliveries_preferences_quiet_hours_and_future_channels_work(): void
    {
        [$guest, , $booking] = $this->bookingContext();
        app(NotificationTemplateService::class)->seedDefaultTemplates();

        app(NotificationPreferenceService::class)->updatePreferences($guest, [
            'in_app_enabled' => false,
            'email_enabled' => true,
            'sms_future_enabled' => true,
            'push_future_enabled' => true,
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '09:00',
            'quiet_hours_end' => '18:00',
            'timezone' => 'Europe/Vilnius',
        ]);
        app(NotificationPreferenceService::class)->updateCategoryPreference($guest, 'payment', [
            'in_app_enabled' => false,
            'email_enabled' => true,
            'sms_future_enabled' => true,
            'push_future_enabled' => true,
        ]);

        $normal = app(NotificationService::class)->createForUser($guest, 'payment_required', [
            'booking' => $booking,
            'priority' => 'normal',
        ]);
        $critical = app(NotificationService::class)->createForUser($guest, 'host_unresponsive_reported', [
            'booking' => $booking,
            'priority' => 'critical',
        ]);

        $this->assertTrue(app(NotificationQuietHoursService::class)->isQuietHours($guest, CarbonImmutable::now()));
        $this->assertTrue($normal->scheduled_at !== null || $normal->deliveries()->where('status', 'skipped_by_quiet_hours')->exists());
        $this->assertTrue($critical->deliveries()->where('channel', 'in_app')->exists());
        $this->assertTrue($normal->deliveries()->where('channel', 'email')->exists());
        $this->assertTrue($normal->deliveries()->where('channel', 'sms_future')->whereNull('sent_at')->exists());
        $this->assertTrue($normal->deliveries()->where('channel', 'push_future')->whereNull('sent_at')->exists());
    }

    public function test_read_dismiss_action_expiry_reminders_and_due_processing_work(): void
    {
        [$guest, , $booking] = $this->bookingContext();
        app(NotificationTemplateService::class)->seedDefaultTemplates();

        $notification = app(NotificationService::class)->createForUser($guest, 'payment_required', [
            'booking' => $booking,
        ]);

        app(NotificationService::class)->markRead($guest, $notification);
        app(NotificationService::class)->markDismissed($guest, $notification);
        app(NotificationActionService::class)->performAction($guest, $notification);
        app(NotificationService::class)->expire($notification->refresh());

        $this->assertTrue($notification->refresh()->is_read);
        $this->assertTrue($notification->is_dismissed);
        $this->assertSame('expired', $notification->status);
        $this->assertDatabaseHas('notification_actions', [
            'notification_id' => $notification->id,
            'user_id' => $guest->id,
            'status' => 'expired',
        ]);

        foreach (['payment_deadline', 'check_in_soon', 'checkout_soon', 'deposit_guest_response_due'] as $type) {
            app(NotificationReminderService::class)->scheduleReminder($guest, $type, CarbonImmutable::now()->subMinute(), [
                'booking' => $booking,
            ]);
        }

        $processed = app(NotificationDueProcessorService::class)->processDueForUser($guest);

        $this->assertSame(4, $processed);
        $this->assertSame(4, NotificationReminder::query()->where('status', 'processed')->count());
        $this->assertGreaterThanOrEqual(4, Notification::query()->where('recipient_user_id', $guest->id)->where('notification_type', 'reminder')->count());
    }

    public function test_deduplication_throttling_digest_recipients_and_sensitive_payload_work(): void
    {
        [$guest, $host, $booking] = $this->bookingContext();
        app(NotificationTemplateService::class)->seedDefaultTemplates();

        $representativeUser = User::factory()->create();
        HostRepresentative::factory()->create([
            'host_user_id' => $host->id,
            'representative_user_id' => $representativeUser->id,
            'can_help_with_check_in' => true,
            'active' => true,
        ]);
        HostRepresentative::factory()->create([
            'host_user_id' => $host->id,
            'representative_user_id' => User::factory()->create()->id,
            'can_help_with_check_in' => false,
            'active' => true,
        ]);

        $event = app(NotificationEventService::class)->createBookingEvent($booking, 'guest_arrived');
        $recipients = app(NotificationRecipientService::class)->getRecipientsForEvent($event);

        $this->assertTrue($recipients->contains(fn (User $user): bool => $user->is($host)));
        $this->assertTrue($recipients->contains(fn (User $user): bool => $user->is($representativeUser)));

        $deduplication = app(NotificationDeduplicationService::class);
        $deduplicationKey = $deduplication->buildDeduplicationKey('guest_sent_message', $host, [
            'booking' => $booking,
        ]);
        $first = app(NotificationService::class)->createForUser($host, 'guest_sent_message', [
            'booking' => $booking,
            'deduplication_key' => $deduplicationKey,
        ]);
        $second = app(NotificationService::class)->createForUser($host, 'guest_sent_message', [
            'booking' => $booking,
            'deduplication_key' => $deduplicationKey,
        ]);

        $this->assertTrue($first->is($second));
        $this->assertSame(1, Notification::query()->where('deduplication_key', $deduplicationKey)->count());

        app(NotificationThrottleService::class)->setThrottle($guest, 'saved-search:demo', CarbonImmutable::now()->addHour());
        $this->assertTrue(app(NotificationThrottleService::class)->shouldThrottle($guest, 'saved-search:demo'));

        $digest = app(NotificationDigestService::class)->createDigest($host, 'daily', CarbonImmutable::now()->subDay(), CarbonImmutable::now());
        app(NotificationDigestService::class)->addNotification($digest, $first);

        $this->assertInstanceOf(NotificationDigest::class, $digest->refresh());
        $this->assertSame(1, $digest->notification_count);

        $filtered = app(NotificationPrivacyService::class)->hideSensitivePayload(
            app(NotificationService::class)->createForUser($guest, 'check_in_instruction_available', [
                'booking' => $booking,
                'payload' => [
                    'door_code' => '1234',
                    'exact_address' => 'Secret Street 10',
                    'safe' => 'shown',
                ],
            ]),
        );

        $this->assertArrayNotHasKey('door_code', $filtered);
        $this->assertArrayNotHasKey('exact_address', $filtered);
        $this->assertSame('shown', $filtered['safe']);
    }

    public function test_notification_center_cursor_pagination_conversation_events_and_livewire_ui_render(): void
    {
        [$guest, $host, $booking] = $this->bookingContext();
        app(NotificationTemplateService::class)->seedDefaultTemplates();

        $notification = app(NotificationService::class)->createForUser($guest, 'booking_confirmed', [
            'booking' => $booking,
            'priority' => 'urgent',
        ]);

        app(ConversationNotificationIntegrationService::class)->addNotificationEventToConversation($notification);

        $this->assertSame(1, ConversationSystemEvent::query()->where('event_key', 'booking_confirmed')->count());
        $this->assertInstanceOf(CursorPaginator::class, app(NotificationNotificationCenterService::class)->getForUser($guest));
        $this->assertSame(1, app(NotificationNotificationCenterService::class)->getUnreadCount($guest));
        $this->assertSame(1, app(NotificationNotificationCenterService::class)->getUrgentUnreadCount($guest));

        app()->setLocale('en');
        Livewire::actingAs($guest)
            ->test(NotificationCenterPage::class)
            ->assertSee(__('notifications.title', [], 'en'))
            ->assertSee(__('notifications.events.booking_confirmed', [], 'en'));

        app()->setLocale('ru');
        Livewire::actingAs($guest)
            ->test(NotificationSettingsPage::class)
            ->assertSee(__('notifications.settings.title', [], 'ru'));

        Livewire::actingAs($host)
            ->test(HostUrgentNotificationsPanel::class)
            ->assertSee(__('notifications.sections.urgent', [], 'ru'));
    }

    /**
     * @return array{0:User,1:User,2:Booking}
     */
    private function bookingContext(): array
    {
        $guest = User::factory()->create([
            'preferred_locale' => 'en',
            'is_guest' => true,
        ]);
        $host = User::factory()->create([
            'preferred_locale' => 'en',
            'is_host' => true,
        ]);
        $property = Property::factory()->for($host, 'host')->create([
            'host_user_id' => $host->id,
            'user_id' => $host->id,
        ]);
        $room = Room::factory()->for($property)->create();
        $place = SleepingPlace::factory()->for($property)->for($room)->create([
            'display_name' => 'Demo notification bed',
        ]);

        $booking = Booking::factory()->create([
            'booking_number' => 'BK-NOTIFY-001',
            'reference' => 'BK-NOTIFY-001',
            'bed_id' => null,
            'guest_user_id' => $guest->id,
            'host_user_id' => $host->id,
            'property_id' => $property->id,
            'room_id' => $room->id,
            'sleeping_place_id' => $place->id,
            'status' => BookingStatus::Confirmed->value,
            'check_in_date' => '2026-07-10',
            'check_out_date' => '2026-07-12',
            'payment_deadline_at' => CarbonImmutable::now()->addHours(2),
        ]);

        return [$guest, $host, $booking];
    }
}
