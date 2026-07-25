<?php

namespace App\Actions\Payments;

use App\Enums\BookingStatus;
use App\Enums\PaymentRecordStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\PropertyTranslation;
use App\Models\User;
use App\Services\Availability\AvailabilityService;
use App\Services\Localization\LocalizedModelContentResolver;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ConfirmDemoPayment
{
    public function __construct(
        private readonly AvailabilityService $availability,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(User $guest, Booking $booking): Booking
    {
        $this->ensureDemoDriverIsAllowed();

        return DB::transaction(function () use ($guest, $booking): Booking {
            $booking = Booking::query()
                ->with([
                    'guest:id,name',
                    'host:id,name',
                    'host.setting:id,user_id,locale',
                    'guest.setting:id,user_id,locale',
                    'property:id,address_line_1,address_line_2,house_number,apartment_number,city,district,show_exact_address_after_payment',
                    'property.translations:id,property_id,locale,check_in_instructions',
                    'sleepingPlace:id',
                ])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $this->ensureGuestOwnsBooking($guest, $booking);
            $this->ensureBookingCanBePaid($booking);

            $amount = (float) ($booking->total_amount ?: $booking->total);
            $currency = $booking->currency ?: 'EUR';

            $booking->paymentRecords()->create([
                'payer_user_id' => $guest->id,
                'provider' => 'demo_manual',
                'provider_reference' => 'demo-'.Str::uuid(),
                'amount' => $amount,
                'currency' => $currency,
                'status' => PaymentRecordStatus::Paid,
                'paid_at' => now(),
                'metadata_json' => [
                    'driver' => 'demo_manual',
                    'environment' => app()->environment(),
                ],
            ]);

            $fromStatus = $this->statusValue($booking);

            $booking->forceFill([
                'status' => BookingStatus::Confirmed,
                'payment_status' => PaymentStatus::Paid,
                'payment_method' => 'demo_manual',
                'payment_paid_at' => now(),
                'availability_hold_expires_at' => null,
                'check_in_instructions' => $this->guestAccessInstructions($booking),
            ])->save();

            $booking->statusHistories()->create([
                'from_status' => $fromStatus,
                'to_status' => BookingStatus::Confirmed->value,
                'changed_by_user_id' => $guest->id,
                'note' => 'booking.payment.history.demo_paid',
            ]);

            $booking->refresh();
            $this->availability->blockForBooking($booking);
            $this->notifyHost($booking);
            app(NotificationService::class)->notifyGuestPaymentConfirmed($booking);

            return $booking->load(['paymentRecords', 'statusHistories']);
        });
    }

    /**
     * @return list<string>
     */
    public static function payablePaymentStatuses(): array
    {
        return [
            PaymentStatus::Unpaid->value,
            PaymentStatus::AwaitingPayment->value,
            PaymentStatus::WaitingPayment->value,
            PaymentStatus::Pending->value,
            PaymentStatus::Failed->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function payableBookingStatuses(): array
    {
        return [
            BookingStatus::AwaitingPayment->value,
            BookingStatus::PendingPayment->value,
        ];
    }

    /**
     * @throws ValidationException
     */
    private function ensureDemoDriverIsAllowed(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw ValidationException::withMessages([
                'payment' => __('booking.payment_page.errors.demo_unavailable'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureGuestOwnsBooking(User $guest, Booking $booking): void
    {
        if ((int) $booking->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'payment' => __('booking.payment_page.errors.not_your_booking'),
            ]);
        }
    }

    /**
     * @throws ValidationException
     */
    private function ensureBookingCanBePaid(Booking $booking): void
    {
        if (! in_array($this->statusValue($booking), self::payableBookingStatuses(), true)) {
            throw ValidationException::withMessages([
                'payment' => __('booking.payment_page.errors.not_payable'),
            ]);
        }

        if (! in_array($this->paymentStatusValue($booking), self::payablePaymentStatuses(), true)) {
            throw ValidationException::withMessages([
                'payment' => __('booking.payment_page.errors.not_payable'),
            ]);
        }
    }

    private function guestAccessInstructions(Booking $booking): ?string
    {
        $property = $booking->property;

        if (! $property || ! $property->show_exact_address_after_payment) {
            return null;
        }

        $translation = app(LocalizedModelContentResolver::class)->resolve(
            $property->translations,
            $booking->guest?->setting?->locale ?: app()->getLocale(),
            config('localization.fallback_locale'),
        );

        $address = collect([
            $property->city,
            $property->district,
            $property->address_line_1,
            $property->house_number,
            $property->apartment_number,
        ])
            ->filter()
            ->implode(', ');

        $instructions = $translation instanceof PropertyTranslation
            ? $translation->check_in_instructions
            : null;

        $parts = array_filter([$address, $instructions]);

        return $parts === []
            ? null
            : implode("\n\n", $parts);
    }

    private function notifyHost(Booking $booking): void
    {
        if (! $booking->host instanceof User) {
            return;
        }

        Notification::query()->create([
            'id' => (string) Str::uuid(),
            'type' => 'booking_payment_received',
            'notifiable_type' => User::class,
            'notifiable_id' => $booking->host->id,
            'user_id' => $booking->host->id,
            'data' => [
                'booking_id' => $booking->id,
                'reference' => $booking->reference,
                'amount' => (float) ($booking->total_amount ?: $booking->total),
                'currency' => $booking->currency,
                'status' => BookingStatus::Confirmed->value,
            ],
            'title_key' => 'notifications.booking_payment_received.title',
            'body_key' => 'notifications.booking_payment_received.body',
            'action_url' => route('host.bookings.manage', [
                'locale' => $this->localeFor($booking->host),
                'booking' => $booking,
            ]),
            'channel' => 'database',
            'status' => 'unread',
        ]);
    }

    private function localeFor(User $user): string
    {
        $locale = $user->setting?->locale ?: app()->getLocale();

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

    private function paymentStatusValue(Booking $booking): string
    {
        return $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status->value
            : (string) $booking->payment_status;
    }
}
