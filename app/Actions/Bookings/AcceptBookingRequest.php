<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcceptBookingRequest
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(User $host, Booking $booking, CarbonInterface|string|null $paymentDeadline = null, ?string $hostMessage = null): Booking
    {
        return DB::transaction(function () use ($host, $booking, $paymentDeadline, $hostMessage): Booking {
            $booking = Booking::query()
                ->with(['guest:id,name', 'guest.setting:id,user_id,locale', 'sleepingPlace:id'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $this->authorize($host, $booking);
            $this->ensureRequestCanBeChanged($booking);

            if (! $this->availability->isAvailableForBooking($booking)) {
                throw ValidationException::withMessages([
                    'booking' => __('host.requests.errors.not_available'),
                ]);
            }

            $deadline = $this->paymentDeadline($paymentDeadline);
            $fromStatus = $this->statusValue($booking);

            $booking->forceFill([
                'status' => BookingStatus::AwaitingPayment,
                'payment_status' => PaymentStatus::AwaitingPayment,
                'payment_deadline_at' => $deadline,
                'availability_hold_expires_at' => $deadline,
                'host_response' => $hostMessage ?: $booking->host_response,
            ])->save();

            $booking->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => BookingStatus::AwaitingPayment->value,
                'changed_by_user_id' => $host->id,
                'note' => 'host.requests.history.accepted',
            ]);

            $booking->refresh();
            $this->availability->blockForBooking($booking);
            $this->notifyGuest($booking);

            return $booking->load(['guest', 'priceLines', 'statusHistories']);
        });
    }

    /**
     * @return list<string>
     */
    public static function requestStatuses(): array
    {
        return [
            BookingStatus::AwaitingHostApproval->value,
            BookingStatus::PendingHostConfirmation->value,
        ];
    }

    /**
     * @throws AuthorizationException
     */
    private function authorize(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('host.requests.errors.not_your_request'));
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureRequestCanBeChanged(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), self::requestStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => __('host.requests.errors.status_changed'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function paymentDeadline(CarbonInterface|string|null $paymentDeadline): CarbonImmutable
    {
        $deadline = $paymentDeadline === null
            ? CarbonImmutable::now()->addMinutes(30)
            : CarbonImmutable::parse($paymentDeadline);

        if ($deadline->lessThanOrEqualTo(CarbonImmutable::now())) {
            throw ValidationException::withMessages([
                'paymentDeadline' => __('host.requests.errors.expiry_must_be_future'),
            ]);
        }

        return $deadline;
    }

    private function notifyGuest(Booking $booking): void
    {
        if (! $booking->guest instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'booking_request_accepted',
            'notifiable_type' => User::class,
            'notifiable_id' => $booking->guest->id,
            'user_id' => $booking->guest->id,
            'data' => [
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'status' => BookingStatus::AwaitingPayment->value,
                'payment_deadline_at' => $booking->payment_deadline_at?->toISOString(),
            ],
            'title_key' => 'notifications.booking_request_accepted.title',
            'body_key' => 'notifications.booking_request_accepted.body',
            'action_url' => route('guest.bookings.show', [
                'locale' => $this->localeFor($booking->guest),
                'booking' => $booking,
            ]),
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    private function localeFor(User $guest): string
    {
        $locale = $guest->setting?->locale ?: app()->getLocale();

        return in_array($locale, config('localization.supported_locales'), true)
            ? $locale
            : (string) config('app.fallback_locale', 'en');
    }

    private function statusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }
}
