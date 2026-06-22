<?php

namespace App\Services\Notifications;

use App\Enums\BookingStatus;
use App\Enums\UserNotificationType;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\NotificationEvent;
use App\Models\NotificationTemplate;
use App\Models\Review;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class NotificationService
{
    /**
     * @param  array<string, scalar|null>  $params
     * @param  array<string, mixed>  $data
     */
    public function create(
        User $user,
        UserNotificationType|string $type,
        array $params = [],
        ?string $actionUrl = null,
        array $data = [],
        ?string $titleKey = null,
        ?string $bodyKey = null,
    ): Notification {
        $typeValue = $type instanceof UserNotificationType ? $type->value : $type;
        $translationBase = 'notifications.'.$typeValue;

        return Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $typeValue,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'data' => array_merge($data, [
                'params' => $params,
            ]),
            'title_key' => $titleKey ?: $translationBase.'.title',
            'body_key' => $bodyKey ?: $translationBase.'.body',
            'action_url' => $actionUrl,
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createForUser(User $user, string $templateKey, array $context = []): Notification
    {
        $template = $this->templateFor($templateKey);
        $booking = $this->contextBooking($context);
        $priority = (string) ($context['priority'] ?? $template->default_priority);
        $deduplicationKey = $context['deduplication_key'] ?? $this->deduplicationKey($templateKey, $user, $context);
        $deduplication = app(NotificationDeduplicationService::class);

        if ($deduplicationKey && $existing = $deduplication->findRecentDuplicate($deduplicationKey)) {
            return $deduplication->mergeIntoExisting($existing, $context);
        }

        $params = $this->notificationParams($booking, $context);
        $payload = $context['payload'] ?? [];
        $actionType = $context['action_type'] ?? $template->default_action_type;
        $locale = app(NotificationPreferenceService::class)->getOrCreateForUser($user)->language_locale ?: $this->localeFor($user);

        $notification = Notification::query()->create([
            'id' => (string) Str::uuid(),
            'notification_number' => app(NotificationNumberService::class)->generateNotificationNumber(),
            'notification_event_id' => $context['notification_event_id'] ?? null,
            'notification_template_id' => $template->id,
            'type' => $templateKey,
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'recipient_user_id' => $user->id,
            'recipient_type' => $context['recipient_type'] ?? $this->recipientTypeFor($user, $booking),
            'notification_category' => $context['notification_category'] ?? $template->notification_category,
            'notification_type' => $context['notification_type'] ?? $this->notificationTypeFor($priority),
            'status' => 'created',
            'priority' => $priority,
            'title_key' => $template->title_translation_key,
            'body_key' => $template->body_translation_key,
            'title_translation_key' => $template->title_translation_key,
            'body_translation_key' => $template->body_translation_key,
            'short_body_translation_key' => $template->short_body_translation_key,
            'translation_params_json' => $params,
            'locale' => $locale,
            'source_type' => $context['source_type'] ?? ($booking ? 'booking' : null),
            'source_id' => $context['source_id'] ?? $booking?->id,
            'booking_id' => $booking?->id ?? $context['booking_id'] ?? null,
            'property_id' => $booking?->property_id ?? $context['property_id'] ?? null,
            'room_id' => $booking?->room_id ?? $context['room_id'] ?? null,
            'sleeping_place_id' => $booking?->sleeping_place_id ?? $context['sleeping_place_id'] ?? null,
            'action_type' => $actionType,
            'action_url' => $context['action_url'] ?? null,
            'action_label_translation_key' => $actionType ? 'notifications.actions.'.$actionType : null,
            'deduplication_key' => $deduplicationKey,
            'throttle_key' => $context['throttle_key'] ?? null,
            'data' => [
                'params' => $params,
                'payload' => $payload,
            ],
            'channel' => 'in_app',
            'is_read' => false,
            'is_dismissed' => false,
            'is_action_required' => (bool) ($context['is_action_required'] ?? $template->requires_action),
            'is_urgent' => $priority === 'urgent' || $priority === 'critical',
            'is_critical' => $priority === 'critical' || $template->is_critical,
        ]);

        $notification->loadMissing('recipient');
        app(NotificationDeliveryService::class)->createDeliveries($notification);

        if ($actionType) {
            app(NotificationActionService::class)->createAction($notification, $actionType, $context);
        }

        app(NotificationSystemEventService::class)->record('notification_created', ['notification' => $notification]);

        return $notification->refresh();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function createFromEvent(NotificationEvent $event, User $recipient, string $templateKey, array $context = []): Notification
    {
        return $this->createForUser($recipient, $templateKey, $context + [
            'notification_event_id' => $event->id,
            'notification_category' => $event->notification_category,
            'source_type' => $event->source_type,
            'source_id' => $event->source_id,
            'booking_id' => $event->booking_id,
            'property_id' => $event->property_id,
            'room_id' => $event->room_id,
            'sleeping_place_id' => $event->sleeping_place_id,
            'payload' => $event->payload_json ?? [],
        ]);
    }

    public function markRead(User $user, Notification $notification): Notification
    {
        if (! app(NotificationPrivacyService::class)->canView($user, $notification)) {
            return $notification;
        }

        $notification->forceFill([
            'status' => 'read',
            'read_at' => now(),
            'is_read' => true,
        ])->save();

        app(NotificationSystemEventService::class)->record('notification_read', ['notification' => $notification, 'user_id' => $user->id]);

        return $notification->refresh();
    }

    public function markDismissed(User $user, Notification $notification): Notification
    {
        if (! app(NotificationPrivacyService::class)->canView($user, $notification)) {
            return $notification;
        }

        $notification->forceFill([
            'status' => 'dismissed',
            'dismissed_at' => now(),
            'is_dismissed' => true,
        ])->save();

        app(NotificationSystemEventService::class)->record('notification_dismissed', ['notification' => $notification, 'user_id' => $user->id]);

        return $notification->refresh();
    }

    public function markActionTaken(User $user, Notification $notification): Notification
    {
        app(NotificationActionService::class)->performAction($user, $notification);

        return $notification->refresh();
    }

    public function cancel(Notification $notification, ?string $reason = null): Notification
    {
        $notification->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ])->save();

        app(NotificationSystemEventService::class)->record('notification_cancelled', ['notification' => $notification, 'reason' => $reason]);

        return $notification->refresh();
    }

    public function expire(Notification $notification): Notification
    {
        $notification->forceFill([
            'status' => 'expired',
            'expired_at' => now(),
        ])->save();

        $notification->actions()->update(['status' => 'expired']);
        app(NotificationSystemEventService::class)->record('notification_expired', ['notification' => $notification]);

        return $notification->refresh();
    }

    public function archive(User $user, Notification $notification): Notification
    {
        if (! app(NotificationPrivacyService::class)->canView($user, $notification)) {
            return $notification;
        }

        $notification->forceFill([
            'status' => 'archived',
            'archived_at' => now(),
        ])->save();

        return $notification->refresh();
    }

    public function notifyBookingCreated(Booking $booking): void
    {
        $booking = $this->notificationBooking($booking);

        $status = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::tryFrom((string) $booking->status);

        $params = $this->bookingParams($booking);

        if ($status === BookingStatus::AwaitingHostApproval) {
            if ($booking->guest instanceof User) {
                $this->create(
                    user: $booking->guest,
                    type: UserNotificationType::BookingRequestSent,
                    params: $params,
                    actionUrl: $this->guestBookingUrl($booking->guest, $booking),
                    data: ['booking_id' => $booking->id],
                );
            }

            if ($booking->host instanceof User) {
                $this->create(
                    user: $booking->host,
                    type: UserNotificationType::NewBookingRequest,
                    params: $params,
                    actionUrl: route('host.requests.index', ['locale' => $this->localeFor($booking->host)]),
                    data: ['booking_id' => $booking->id],
                );
            }

            return;
        }

        if ($status === BookingStatus::AwaitingPayment && $booking->guest instanceof User) {
            $this->create(
                user: $booking->guest,
                type: UserNotificationType::PaymentRequired,
                params: $params,
                actionUrl: route('guest.bookings.payment', [
                    'locale' => $this->localeFor($booking->guest),
                    'booking' => $booking,
                ]),
                data: ['booking_id' => $booking->id],
            );

            return;
        }

        if ($status === BookingStatus::Confirmed) {
            if ($booking->guest instanceof User) {
                $this->create(
                    user: $booking->guest,
                    type: UserNotificationType::BookingConfirmed,
                    params: $params,
                    actionUrl: $this->guestBookingUrl($booking->guest, $booking),
                    data: ['booking_id' => $booking->id],
                );
            }

            if ($booking->host instanceof User) {
                $this->create(
                    user: $booking->host,
                    type: UserNotificationType::BookingConfirmed,
                    params: $params,
                    actionUrl: route('host.bookings.manage', [
                        'locale' => $this->localeFor($booking->host),
                        'booking' => $booking,
                    ]),
                    data: ['booking_id' => $booking->id],
                );
            }
        }
    }

    public function notifyGuestPaymentConfirmed(Booking $booking): void
    {
        $booking = $this->notificationBooking($booking);

        if (! $booking->guest instanceof User) {
            return;
        }

        $params = $this->bookingParams($booking);

        $this->create(
            user: $booking->guest,
            type: UserNotificationType::PaymentReceived,
            params: $params,
            actionUrl: $this->guestBookingUrl($booking->guest, $booking),
            data: ['booking_id' => $booking->id],
        );

        $this->create(
            user: $booking->guest,
            type: UserNotificationType::CheckInInstructionsAvailable,
            params: $params,
            actionUrl: $this->guestBookingUrl($booking->guest, $booking),
            data: ['booking_id' => $booking->id],
        );
    }

    public function notifyBookingCancelled(Booking $booking, string $cancelledBy, float $refundAmount): void
    {
        $booking = $this->notificationBooking($booking);
        $type = $cancelledBy === 'host'
            ? UserNotificationType::BookingCancelledByHost
            : UserNotificationType::BookingCancelledByGuest;

        if ($booking->guest instanceof User) {
            $params = $this->bookingParams($booking);
            $params['refund'] = Number::currency($refundAmount, $booking->currency ?: 'EUR', $this->localeFor($booking->guest));

            $this->create(
                user: $booking->guest,
                type: $type,
                params: $params,
                actionUrl: $this->guestBookingUrl($booking->guest, $booking),
                data: [
                    'booking_id' => $booking->id,
                    'cancelled_by' => $cancelledBy,
                    'refund_amount' => $refundAmount,
                ],
            );
        }

        if ($booking->host instanceof User) {
            $params = $this->bookingParams($booking);
            $params['refund'] = Number::currency($refundAmount, $booking->currency ?: 'EUR', $this->localeFor($booking->host));

            $this->create(
                user: $booking->host,
                type: $type,
                params: $params,
                actionUrl: $this->hostBookingUrl($booking->host, $booking),
                data: [
                    'booking_id' => $booking->id,
                    'cancelled_by' => $cancelledBy,
                    'refund_amount' => $refundAmount,
                ],
            );
        }
    }

    public function notifyHostGuestCheckedIn(Booking $booking): void
    {
        $booking = $this->notificationBooking($booking);

        if (! $booking->host instanceof User) {
            return;
        }

        $this->create(
            user: $booking->host,
            type: UserNotificationType::GuestCheckedIn,
            params: $this->bookingParams($booking),
            actionUrl: $this->hostBookingUrl($booking->host, $booking),
            data: ['booking_id' => $booking->id],
        );
    }

    public function notifyHostGuestReportedProblem(Booking $booking): void
    {
        $booking = $this->notificationBooking($booking);

        if (! $booking->host instanceof User) {
            return;
        }

        $this->create(
            user: $booking->host,
            type: UserNotificationType::GuestReportsProblem,
            params: $this->bookingParams($booking),
            actionUrl: $this->hostBookingUrl($booking->host, $booking),
            data: ['booking_id' => $booking->id],
        );
    }

    public function notifyHostGuestCheckedOut(Booking $booking): void
    {
        $booking = $this->notificationBooking($booking);

        if (! $booking->host instanceof User) {
            return;
        }

        $this->create(
            user: $booking->host,
            type: UserNotificationType::GuestCheckedOut,
            params: $this->bookingParams($booking),
            actionUrl: $this->hostBookingUrl($booking->host, $booking),
            data: ['booking_id' => $booking->id],
        );
    }

    public function notifyGuestDepositReturned(Booking $booking): void
    {
        $booking = $this->notificationBooking($booking);

        if (! $booking->guest instanceof User) {
            return;
        }

        $this->create(
            user: $booking->guest,
            type: UserNotificationType::DepositReturned,
            params: $this->bookingParams($booking),
            actionUrl: $this->guestBookingUrl($booking->guest, $booking),
            data: ['booking_id' => $booking->id],
        );
    }

    public function notifyReviewReceived(Review $review): void
    {
        $review->loadMissing(['reviewee:id,name', 'reviewee.setting:id,user_id,locale', 'booking:id']);

        if (! $review->reviewee instanceof User || ! $review->booking instanceof Booking) {
            return;
        }

        $booking = $this->notificationBooking($review->booking);
        $actionUrl = (int) $review->reviewee_id === (int) $booking->host_user_id
            ? $this->hostBookingUrl($review->reviewee, $booking)
            : $this->guestBookingUrl($review->reviewee, $booking);

        $this->create(
            user: $review->reviewee,
            type: UserNotificationType::ReviewReceived,
            params: $this->bookingParams($booking),
            actionUrl: $actionUrl,
            data: [
                'booking_id' => $booking->id,
                'review_id' => $review->id,
            ],
        );
    }

    public function notifyPlaceAvailabilityChanged(SleepingPlace $sleepingPlace, User $host): void
    {
        $sleepingPlace->loadMissing(['translations:id,sleeping_place_id,locale,title']);
        $placeTitle = $sleepingPlace->translations?->firstWhere('locale', $this->localeFor($host))?->title
            ?: $sleepingPlace->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
            ?: $sleepingPlace->display_name
            ?: $sleepingPlace->place_number;

        $this->create(
            user: $host,
            type: UserNotificationType::PlaceAvailabilityChanged,
            params: ['place' => $placeTitle],
            actionUrl: route('host.calendar', ['locale' => $this->localeFor($host)]),
            data: ['sleeping_place_id' => $sleepingPlace->id],
        );
    }

    /**
     * @return array<string, scalar|null>
     */
    private function bookingParams(Booking $booking): array
    {
        $placeTitle = $booking->sleepingPlace?->translations?->firstWhere('locale', app()->getLocale())?->title
            ?: $booking->sleepingPlace?->translations?->firstWhere('locale', config('app.fallback_locale', 'en'))?->title
            ?: $booking->sleepingPlace?->display_name;

        return [
            'reference' => $booking->reference,
            'place' => $placeTitle,
            'guest' => $booking->guest?->name,
            'host' => $booking->host?->name,
            'date' => $booking->check_in_date?->toDateString(),
            'deadline' => $booking->payment_deadline_at?->format('H:i'),
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, scalar|null>
     */
    private function notificationParams(?Booking $booking, array $context): array
    {
        $params = [];

        if ($booking instanceof Booking) {
            $booking->loadMissing([
                'guest:id,name',
                'host:id,name',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ]);

            $params = $this->bookingParams($booking);
        }

        $extra = $context['translation_params_json'] ?? $context['params'] ?? [];

        return array_merge($params, is_array($extra) ? $extra : []);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function contextBooking(array $context): ?Booking
    {
        if (($context['booking'] ?? null) instanceof Booking) {
            return $context['booking'];
        }

        if (isset($context['booking_id'])) {
            return Booking::query()
                ->select([
                    'id',
                    'reference',
                    'guest_user_id',
                    'host_user_id',
                    'property_id',
                    'room_id',
                    'sleeping_place_id',
                    'payment_deadline_at',
                    'check_in_date',
                    'check_out_date',
                ])
                ->find($context['booking_id']);
        }

        return null;
    }

    private function templateFor(string $templateKey): NotificationTemplate
    {
        $template = app(NotificationTemplateService::class)->getByKey($templateKey);

        if (! $template instanceof NotificationTemplate) {
            app(NotificationTemplateService::class)->seedDefaultTemplates();
            $template = app(NotificationTemplateService::class)->getByKey($templateKey);
        }

        if (! $template instanceof NotificationTemplate) {
            $template = NotificationTemplate::query()->create([
                'template_key' => $templateKey,
                'notification_category' => 'system',
                'title_translation_key' => 'notifications.events.'.$templateKey,
                'body_translation_key' => 'notifications.events.'.$templateKey,
                'default_priority' => 'normal',
                'default_action_type' => 'open_booking',
                'supports_in_app' => true,
                'active' => true,
            ]);
        }

        return $template;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function deduplicationKey(string $templateKey, User $user, array $context): ?string
    {
        if (! in_array($templateKey, ['guest_sent_message', 'host_sent_message', 'saved_search_new_results'], true)) {
            return null;
        }

        return app(NotificationDeduplicationService::class)->buildDeduplicationKey($templateKey, $user, $context);
    }

    private function notificationTypeFor(string $priority): string
    {
        return match ($priority) {
            'critical', 'urgent' => 'urgent_alert',
            'high' => 'action_required',
            default => 'info',
        };
    }

    private function recipientTypeFor(User $user, ?Booking $booking): string
    {
        if ($booking instanceof Booking) {
            if ((int) $booking->host_user_id === (int) $user->id) {
                return 'host';
            }

            if ((int) $booking->guest_user_id === (int) $user->id) {
                return 'guest';
            }
        }

        return $user->is_host ? 'host' : 'guest';
    }

    private function notificationBooking(Booking $booking): Booking
    {
        return Booking::query()
            ->select([
                'id',
                'reference',
                'guest_user_id',
                'host_user_id',
                'status',
                'payment_status',
                'currency',
                'payment_deadline_at',
                'check_in_date',
                'check_out_date',
                'sleeping_place_id',
            ])
            ->with([
                'guest:id,name',
                'guest.setting:id,user_id,locale',
                'host:id,name',
                'host.setting:id,user_id,locale',
                'sleepingPlace:id,display_name,place_number',
                'sleepingPlace.translations:id,sleeping_place_id,locale,title',
            ])
            ->findOrFail($booking->id);
    }

    private function guestBookingUrl(User $user, Booking $booking): string
    {
        return route('guest.bookings.show', [
            'locale' => $this->localeFor($user),
            'booking' => $booking,
        ]);
    }

    private function hostBookingUrl(User $user, Booking $booking): string
    {
        return route('host.bookings.manage', [
            'locale' => $this->localeFor($user),
            'booking' => $booking,
        ]);
    }

    private function localeFor(User $user): string
    {
        $locale = $user->setting?->locale ?: app()->getLocale();

        return in_array($locale, config('localization.supported_locales'), true)
            ? $locale
            : (string) config('app.fallback_locale', 'en');
    }
}
