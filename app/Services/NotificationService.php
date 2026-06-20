<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\UserNotificationType;
use App\Models\Booking;
use App\Models\Notification;
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
