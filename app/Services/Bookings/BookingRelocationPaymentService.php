<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\BookingRelocation;

class BookingRelocationPaymentService
{
    public function __construct(
        private readonly BookingPaymentNumberService $numbers,
        private readonly BookingRelocationHoldService $holds,
        private readonly BookingRelocationEventService $events,
        private readonly BookingRelocationNotificationService $notifications,
    ) {}

    public function createPaymentIfNeeded(BookingRelocation $relocation): ?BookingPayment
    {
        if (! $relocation->requires_payment || (float) $relocation->additional_payment_amount <= 0 || $relocation->price_difference_payer !== 'guest') {
            $relocation->forceFill([
                'payment_status' => 'not_required',
            ])->save();

            return null;
        }

        if ($relocation->booking_payment_id) {
            return BookingPayment::query()->find($relocation->booking_payment_id);
        }

        $payment = BookingPayment::query()->create([
            'payment_number' => $this->numbers->generatePaymentNumber(),
            'booking_id' => $relocation->original_booking_id,
            'booking_quote_id' => null,
            'booking_request_id' => null,
            'booking_extension_id' => null,
            'booking_relocation_id' => $relocation->id,
            'guest_user_id' => $relocation->guest_user_id,
            'host_user_id' => $relocation->host_user_id,
            'property_id' => $relocation->new_property_id ?: $relocation->current_property_id,
            'room_id' => $relocation->new_room_id ?: $relocation->current_room_id,
            'sleeping_place_id' => $relocation->new_sleeping_place_id ?: $relocation->current_sleeping_place_id,
            'payment_type' => 'full_payment',
            'payment_purpose' => 'relocation_payment',
            'payment_method' => $relocation->payment_method ?: 'internal_test',
            'status' => 'waiting_payment',
            'amount' => $relocation->additional_payment_amount,
            'currency' => $relocation->currency,
            'required_now_amount' => $relocation->additional_payment_amount,
            'remaining_amount' => 0,
            'provider' => null,
            'provider_payment_id' => null,
            'provider_status' => null,
            'payment_deadline_at' => $relocation->payment_deadline_at ?: now()->addMinutes(30),
            'description' => 'booking_relocations.payment.description',
        ]);

        $relocation->forceFill([
            'booking_payment_id' => $payment->id,
            'payment_status' => 'waiting_payment',
            'payment_deadline_at' => $payment->payment_deadline_at,
            'status' => 'waiting_payment',
        ])->save();

        $this->events->record($relocation->refresh(), 'payment_required');
        $this->notifications->notifyPaymentRequired($relocation->refresh());

        return $payment;
    }

    public function markWaitingPayment(BookingRelocation $relocation): BookingRelocation
    {
        $this->createPaymentIfNeeded($relocation);

        return $relocation->refresh();
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function markPaid(BookingRelocation $relocation, array $paymentData = []): BookingRelocation
    {
        $payment = $this->createPaymentIfNeeded($relocation);

        if ($payment instanceof BookingPayment) {
            $payment->forceFill([
                'status' => 'paid',
                'provider_payment_id' => $paymentData['provider_payment_id'] ?? $payment->provider_payment_id,
                'provider_status' => 'paid',
                'paid_at' => now(),
            ])->save();
        }

        $relocation->forceFill([
            'payment_status' => 'paid',
            'status' => 'paid',
            'paid_at' => now(),
        ])->save();

        $this->events->record($relocation->refresh(), 'payment_completed');

        return $relocation->refresh();
    }

    public function markPaymentFailed(BookingRelocation $relocation, string $reason): BookingRelocation
    {
        if ($relocation->booking_payment_id) {
            BookingPayment::query()
                ->whereKey($relocation->booking_payment_id)
                ->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $reason,
                    'updated_at' => now(),
                ]);
        }

        $relocation->forceFill([
            'status' => 'failed',
            'payment_status' => 'failed',
        ])->save();

        $this->holds->releaseNewPlaceHold($relocation->refresh(), 'payment_failed');
        $this->events->record($relocation, 'relocation_failed', ['reason' => $reason]);

        return $relocation->refresh();
    }
}
