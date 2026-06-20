<?php

namespace App\Services;

use App\Enums\BookingExtensionStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentRecordStatus;
use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\Notification;
use App\Models\SleepingPlace;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExtensionService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PricingService $pricing,
    ) {}

    /**
     * @return array{
     *     current_checkout_date:string,
     *     requested_new_checkout_date:string,
     *     additional_nights:int,
     *     additional_amount:float,
     *     discount_amount:float,
     *     service_fee_amount:float,
     *     total_extra:float,
     *     new_total:float,
     *     currency:string,
     *     payment_required:bool,
     *     requires_host_approval:bool,
     *     next_status:string
     * }
     *
     * @throws ValidationException
     */
    public function preview(User $guest, Booking $booking, CarbonInterface|string $newCheckOut): array
    {
        $booking = $this->loadBooking($booking);

        $this->ensureGuestOwnsBooking($guest, $booking);

        return $this->quote($booking, $newCheckOut);
    }

    /**
     * @throws ValidationException
     * @throws AuthorizationException
     */
    public function request(User $guest, Booking $booking, CarbonInterface|string $newCheckOut, ?string $guestMessage = null): BookingExtension
    {
        return DB::transaction(function () use ($guest, $booking, $newCheckOut, $guestMessage): BookingExtension {
            $booking = $this->loadBooking($booking, lock: true);

            $this->ensureGuestOwnsBooking($guest, $booking);

            $quote = $this->quote($booking, $newCheckOut);
            $status = BookingExtensionStatus::from($quote['next_status']);

            $extension = BookingExtension::query()->create([
                'booking_id' => $booking->id,
                'current_checkout_date' => $quote['current_checkout_date'],
                'requested_new_checkout_date' => $quote['requested_new_checkout_date'],
                'additional_nights' => $quote['additional_nights'],
                'additional_amount' => $quote['additional_amount'],
                'original_check_out' => $quote['current_checkout_date'],
                'new_check_out' => $quote['requested_new_checkout_date'],
                'extra_nights' => $quote['additional_nights'],
                'extra_amount' => $quote['additional_amount'],
                'discount_amount' => $quote['discount_amount'],
                'total_extra' => $quote['total_extra'],
                'new_total' => $quote['new_total'],
                'payment_required' => $quote['payment_required'],
                'payment_deadline_at' => $quote['payment_required'] ? CarbonImmutable::now()->addMinutes(30) : null,
                'requires_host_approval' => $quote['requires_host_approval'],
                'guest_message' => $guestMessage,
                'status' => $status,
            ]);

            if ($status === BookingExtensionStatus::Approved) {
                $this->applyExtension($booking, $extension);
                $this->notifyGuest($booking, $extension, 'booking_extension_approved');
            }

            if ($status === BookingExtensionStatus::AwaitingPayment) {
                $this->notifyGuest($booking, $extension, 'booking_extension_awaiting_payment');
            }

            $this->notifyHost($booking, $extension, 'booking_extension_requested');

            return $extension->refresh();
        });
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function approve(User $host, BookingExtension $extension, ?string $hostResponse = null): BookingExtension
    {
        return DB::transaction(function () use ($host, $extension, $hostResponse): BookingExtension {
            $extension = $this->loadExtension($extension, lock: true);
            $booking = $extension->booking;

            $this->ensureHostOwnsBooking($host, $booking);
            $this->ensureExtensionStatus($extension, [BookingExtensionStatus::AwaitingHostApproval]);

            $quote = $this->quote($booking, $extension->requested_new_checkout_date);
            $status = $quote['payment_required']
                ? BookingExtensionStatus::AwaitingPayment
                : BookingExtensionStatus::Approved;

            $extension->forceFill([
                'additional_nights' => $quote['additional_nights'],
                'additional_amount' => $quote['additional_amount'],
                'extra_nights' => $quote['additional_nights'],
                'extra_amount' => $quote['additional_amount'],
                'discount_amount' => $quote['discount_amount'],
                'total_extra' => $quote['total_extra'],
                'new_total' => $quote['new_total'],
                'payment_required' => $quote['payment_required'],
                'payment_deadline_at' => $quote['payment_required'] ? CarbonImmutable::now()->addMinutes(30) : null,
                'status' => $status,
                'host_reply' => $hostResponse,
                'host_response' => $hostResponse,
                'approved_at' => $status === BookingExtensionStatus::Approved ? now() : null,
            ])->save();

            if ($status === BookingExtensionStatus::Approved) {
                $this->applyExtension($booking, $extension);
            }

            $this->notifyGuest($booking, $extension, $status === BookingExtensionStatus::AwaitingPayment
                ? 'booking_extension_awaiting_payment'
                : 'booking_extension_approved');

            return $extension->refresh();
        });
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function markPaid(User $guest, BookingExtension $extension): BookingExtension
    {
        return DB::transaction(function () use ($guest, $extension): BookingExtension {
            $extension = $this->loadExtension($extension, lock: true);
            $booking = $extension->booking;

            $this->ensureGuestOwnsBooking($guest, $booking);
            $this->ensureExtensionStatus($extension, [BookingExtensionStatus::AwaitingPayment]);

            if (! app()->environment(['local', 'testing'])) {
                throw ValidationException::withMessages([
                    'extension' => __('booking.extension.errors.demo_payment_unavailable'),
                ]);
            }

            $this->quote($booking, $extension->requested_new_checkout_date);

            $booking->paymentRecords()->create([
                'payer_user_id' => $guest->id,
                'provider' => 'extension_demo_manual',
                'provider_reference' => 'extension-'.Str::uuid(),
                'amount' => $extension->total_extra,
                'currency' => $booking->currency ?: 'EUR',
                'status' => PaymentRecordStatus::Paid,
                'paid_at' => now(),
                'metadata_json' => [
                    'driver' => 'extension_demo_manual',
                    'booking_extension_id' => $extension->id,
                    'environment' => app()->environment(),
                ],
            ]);

            $extension->forceFill([
                'status' => BookingExtensionStatus::Approved,
                'paid_at' => now(),
                'approved_at' => now(),
            ])->save();

            $this->applyExtension($booking, $extension);
            $this->notifyHost($booking, $extension, 'booking_extension_paid');
            $this->notifyGuest($booking, $extension, 'booking_extension_approved');

            return $extension->refresh();
        });
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function reject(User $host, BookingExtension $extension, ?string $reason = null, ?string $hostResponse = null): BookingExtension
    {
        return DB::transaction(function () use ($host, $extension, $reason, $hostResponse): BookingExtension {
            $extension = $this->loadExtension($extension, lock: true);
            $booking = $extension->booking;

            $this->ensureHostOwnsBooking($host, $booking);
            $this->ensureExtensionStatus($extension, [
                BookingExtensionStatus::AwaitingHostApproval,
                BookingExtensionStatus::AwaitingPayment,
            ]);

            $extension->forceFill([
                'status' => BookingExtensionStatus::Declined,
                'reject_reason' => $reason,
                'host_reply' => $hostResponse,
                'host_response' => $hostResponse,
                'declined_at' => now(),
            ])->save();

            $this->notifyGuest($booking, $extension, 'booking_extension_declined');

            return $extension->refresh();
        });
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function cancel(User $guest, BookingExtension $extension): BookingExtension
    {
        return DB::transaction(function () use ($guest, $extension): BookingExtension {
            $extension = $this->loadExtension($extension, lock: true);
            $booking = $extension->booking;

            $this->ensureGuestOwnsBooking($guest, $booking);
            $this->ensureExtensionStatus($extension, [
                BookingExtensionStatus::AwaitingHostApproval,
                BookingExtensionStatus::AwaitingPayment,
            ]);

            $extension->forceFill([
                'status' => BookingExtensionStatus::Cancelled,
                'cancelled_at' => now(),
            ])->save();

            $this->notifyHost($booking, $extension, 'booking_extension_cancelled');

            return $extension->refresh();
        });
    }

    private function loadBooking(Booking $booking, bool $lock = false): Booking
    {
        $query = Booking::query()
            ->with([
                'guest:id,name',
                'guest.setting:id,user_id,locale',
                'host:id,name',
                'host.setting:id,user_id,locale',
                'sleepingPlace:id,room_id,property_id,status,min_nights,max_nights,max_guests,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,instant_booking_enabled,requires_host_approval,extensions_allowed',
                'sleepingPlace.room:id,property_id,status',
                'sleepingPlace.property:id,status',
            ]);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($booking->id);
    }

    private function loadExtension(BookingExtension $extension, bool $lock = false): BookingExtension
    {
        $query = BookingExtension::query()
            ->with([
                'booking.guest:id,name',
                'booking.guest.setting:id,user_id,locale',
                'booking.host:id,name',
                'booking.host.setting:id,user_id,locale',
                'booking.sleepingPlace:id,room_id,property_id,status,min_nights,max_nights,max_guests,base_price_per_night,weekly_price,monthly_price,weekend_price,cleaning_fee,deposit_amount,currency,instant_booking_enabled,requires_host_approval,extensions_allowed',
                'booking.sleepingPlace.room:id,property_id,status',
                'booking.sleepingPlace.property:id,status',
            ]);

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($extension->id);
    }

    /**
     * @return array{
     *     current_checkout_date:string,
     *     requested_new_checkout_date:string,
     *     additional_nights:int,
     *     additional_amount:float,
     *     discount_amount:float,
     *     service_fee_amount:float,
     *     total_extra:float,
     *     new_total:float,
     *     currency:string,
     *     payment_required:bool,
     *     requires_host_approval:bool,
     *     next_status:string
     * }
     *
     * @throws ValidationException
     */
    private function quote(Booking $booking, CarbonInterface|string|null $newCheckOut): array
    {
        $this->ensureBookingCanExtend($booking);

        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            throw ValidationException::withMessages([
                'booking' => __('booking.extension.errors.place_missing'),
            ]);
        }

        $currentCheckOut = CarbonImmutable::parse($booking->check_out_date ?: $booking->check_out)->startOfDay();
        $requestedCheckOut = CarbonImmutable::parse($newCheckOut)->startOfDay();
        $checkIn = CarbonImmutable::parse($booking->check_in_date ?: $booking->check_in)->startOfDay();

        if ($requestedCheckOut->lessThanOrEqualTo($currentCheckOut)) {
            throw ValidationException::withMessages([
                'requestedNewCheckout' => __('booking.extension.errors.checkout_after_current'),
            ]);
        }

        $additionalNights = (int) $currentCheckOut->diffInDays($requestedCheckOut);
        $newTotalNights = (int) $checkIn->diffInDays($requestedCheckOut);

        if ($place->max_nights !== null && $newTotalNights > (int) $place->max_nights) {
            throw ValidationException::withMessages([
                'requestedNewCheckout' => trans_choice('booking.extension.errors.max_nights', (int) $place->max_nights, [
                    'count' => (int) $place->max_nights,
                ]),
            ]);
        }

        if (! $this->availability->isAvailable($place, $currentCheckOut, $requestedCheckOut)) {
            throw ValidationException::withMessages([
                'requestedNewCheckout' => __('booking.extension.errors.unavailable'),
            ]);
        }

        $quote = $this->pricing
            ->calculate($booking->guest, $place, $currentCheckOut, $requestedCheckOut, (int) $booking->guests_count)
            ->toArray();

        $additionalAmount = $this->money($quote['base_amount'] + $quote['weekend_adjustment_amount'] + $quote['date_override_amount']);
        $discount = $this->money($quote['weekly_discount_amount'] + $quote['monthly_discount_amount']);
        $serviceFee = $this->money($quote['service_fee_amount']);
        $totalExtra = $this->money(max(0.0, $additionalAmount - $discount) + $serviceFee);
        $currentTotal = $this->money($booking->total_amount ?: $booking->total);
        $requiresHostApproval = (bool) $place->requires_host_approval || ! (bool) $place->instant_booking_enabled;
        $paymentRequired = $totalExtra > 0.0;

        $nextStatus = match (true) {
            $requiresHostApproval => BookingExtensionStatus::AwaitingHostApproval,
            $paymentRequired => BookingExtensionStatus::AwaitingPayment,
            default => BookingExtensionStatus::Approved,
        };

        return [
            'current_checkout_date' => $currentCheckOut->toDateString(),
            'requested_new_checkout_date' => $requestedCheckOut->toDateString(),
            'additional_nights' => $additionalNights,
            'additional_amount' => $additionalAmount,
            'discount_amount' => $discount,
            'service_fee_amount' => $serviceFee,
            'total_extra' => $totalExtra,
            'new_total' => $this->money($currentTotal + $totalExtra),
            'currency' => $booking->currency ?: $quote['currency'],
            'payment_required' => $paymentRequired,
            'requires_host_approval' => $requiresHostApproval,
            'next_status' => $nextStatus->value,
        ];
    }

    /**
     * @throws ValidationException
     */
    private function ensureBookingCanExtend(Booking $booking): void
    {
        if (! in_array($this->bookingStatusValue($booking), $this->extendableBookingStatuses(), true)) {
            throw ValidationException::withMessages([
                'booking' => __('booking.extension.errors.booking_not_extendable'),
            ]);
        }

        $place = $booking->sleepingPlace;

        if (! $place instanceof SleepingPlace) {
            throw ValidationException::withMessages([
                'booking' => __('booking.extension.errors.place_missing'),
            ]);
        }

        if (! (bool) $place->extensions_allowed) {
            throw ValidationException::withMessages([
                'booking' => __('booking.extension.errors.not_allowed'),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function extendableBookingStatuses(): array
    {
        return [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
        ];
    }

    /**
     * @param  list<BookingExtensionStatus>  $allowed
     *
     * @throws ValidationException
     */
    private function ensureExtensionStatus(BookingExtension $extension, array $allowed): void
    {
        $status = $extension->status instanceof BookingExtensionStatus
            ? $extension->status
            : BookingExtensionStatus::tryFrom((string) $extension->status);

        if (! $status || ! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages([
                'extension' => __('booking.extension.errors.status_changed'),
            ]);
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureHostOwnsBooking(User $host, Booking $booking): void
    {
        if ((int) $booking->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('booking.extension.errors.not_host_booking'));
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureGuestOwnsBooking(User $guest, Booking $booking): void
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id) {
            throw new AuthorizationException(__('booking.extension.errors.not_your_booking'));
        }
    }

    private function applyExtension(Booking $booking, BookingExtension $extension): void
    {
        $booking->refresh();

        $newCheckout = $extension->requested_new_checkout_date ?: $extension->new_check_out;
        $checkIn = CarbonImmutable::parse($booking->check_in_date ?: $booking->check_in)->startOfDay();
        $newTotalNights = (int) $checkIn->diffInDays(CarbonImmutable::parse($newCheckout)->startOfDay());
        $serviceFeeExtra = $this->money($extension->total_extra - max(0.0, (float) $extension->additional_amount - (float) $extension->discount_amount));

        $booking->forceFill([
            'check_out' => $newCheckout,
            'check_out_date' => $newCheckout,
            'nights' => $newTotalNights,
            'nights_count' => $newTotalNights,
            'calendar_days_count' => $newTotalNights + 1,
            'subtotal' => $this->money((float) ($booking->subtotal ?: 0) + (float) $extension->additional_amount),
            'subtotal_amount' => $this->money((float) ($booking->subtotal_amount ?: 0) + (float) $extension->additional_amount),
            'discount_amount' => $this->money((float) ($booking->discount_amount ?: 0) + (float) $extension->discount_amount),
            'service_fee' => $this->money((float) ($booking->service_fee ?: 0) + $serviceFeeExtra),
            'service_fee_amount' => $this->money((float) ($booking->service_fee_amount ?: 0) + $serviceFeeExtra),
            'total' => $extension->new_total,
            'total_amount' => $extension->new_total,
            'non_refundable_amount' => $this->money((float) ($booking->non_refundable_amount ?: 0) + (float) $extension->total_extra),
        ])->save();

        $booking->refresh();
        $this->availability->blockForBooking($booking);
    }

    private function notifyHost(Booking $booking, BookingExtension $extension, string $type): void
    {
        if (! $booking->host instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $booking->host->id,
            'user_id' => $booking->host->id,
            'data' => $this->notificationData($booking, $extension),
            'title_key' => 'notifications.'.$type.'.title',
            'body_key' => 'notifications.'.$type.'.body',
            'action_url' => route('host.extensions.manage', [
                'locale' => $this->localeFor($booking->host),
                'booking' => $booking,
                'extension' => $extension,
            ]),
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    private function notifyGuest(Booking $booking, BookingExtension $extension, string $type): void
    {
        if (! $booking->guest instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $booking->guest->id,
            'user_id' => $booking->guest->id,
            'data' => $this->notificationData($booking, $extension),
            'title_key' => 'notifications.'.$type.'.title',
            'body_key' => 'notifications.'.$type.'.body',
            'action_url' => route('guest.bookings.show', [
                'locale' => $this->localeFor($booking->guest),
                'booking' => $booking,
            ]),
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function notificationData(Booking $booking, BookingExtension $extension): array
    {
        return [
            'booking_id' => $booking->id,
            'booking_extension_id' => $extension->id,
            'reference' => $booking->reference,
            'current_checkout_date' => $extension->current_checkout_date?->toDateString(),
            'requested_new_checkout_date' => $extension->requested_new_checkout_date?->toDateString(),
            'additional_nights' => $extension->additional_nights,
            'total_extra' => (float) $extension->total_extra,
            'status' => $extension->status instanceof BookingExtensionStatus
                ? $extension->status->value
                : (string) $extension->status,
        ];
    }

    private function localeFor(User $user): string
    {
        $locale = $user->setting?->locale ?: app()->getLocale();

        return in_array($locale, config('localization.supported_locales'), true)
            ? $locale
            : (string) config('app.fallback_locale', 'en');
    }

    private function bookingStatusValue(Booking $booking): string
    {
        return $booking->status instanceof BookingStatus
            ? $booking->status->value
            : (string) $booking->status;
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
