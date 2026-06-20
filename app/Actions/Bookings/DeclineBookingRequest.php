<?php

namespace App\Actions\Bookings;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use App\Services\AvailabilityService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeclineBookingRequest
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(User $host, Booking $booking, string $reason, ?string $message = null): Booking
    {
        return DB::transaction(function () use ($host, $booking, $reason, $message): Booking {
            $booking = Booking::query()
                ->with(['guest:id,name', 'guest.setting:id,user_id,locale'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $this->authorize($host, $booking);
            $this->ensureRequestCanBeChanged($booking);
            $this->ensureReasonIsValid($reason);

            $fromStatus = $this->statusValue($booking);

            $booking->forceFill([
                'status' => BookingStatus::DeclinedByHost,
                'payment_status' => PaymentStatus::Unpaid,
                'cancelled_by' => 'host',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'cancellation_reason' => $reason,
                'host_response' => $message,
                'availability_hold_expires_at' => null,
            ])->save();

            $booking->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => BookingStatus::DeclinedByHost->value,
                'changed_by_user_id' => $host->id,
                'note' => 'host.requests.history.declined.'.$reason,
            ]);

            $booking->refresh();
            $this->availability->releaseForBooking($booking);
            $this->notifyGuest($booking, $reason);

            return $booking->load(['guest', 'statusHistories']);
        });
    }

    /**
     * @return list<string>
     */
    public static function reasonKeys(): array
    {
        return [
            'dates_unavailable',
            'rules_mismatch',
            'guest_profile_incomplete',
            'not_a_good_fit',
            'other',
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
        if (! in_array($this->statusValue($booking), AcceptBookingRequest::requestStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => __('host.requests.errors.status_changed'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureReasonIsValid(string $reason): void
    {
        if (! in_array($reason, self::reasonKeys(), true)) {
            throw ValidationException::withMessages([
                'declineReason' => __('host.requests.errors.reason_required'),
            ]);
        }
    }

    private function notifyGuest(Booking $booking, string $reason): void
    {
        if (! $booking->guest instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'booking_request_declined',
            'notifiable_type' => User::class,
            'notifiable_id' => $booking->guest->id,
            'user_id' => $booking->guest->id,
            'data' => [
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'reason' => $reason,
                'status' => BookingStatus::DeclinedByHost->value,
            ],
            'title_key' => 'notifications.booking_request_declined.title',
            'body_key' => 'notifications.booking_request_declined.body',
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
