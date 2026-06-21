<?php

namespace App\Services\Bookings;

use App\Models\BookingExtension;
use App\Models\BookingPayment;

class BookingExtensionPaymentService
{
    public function __construct(
        private readonly BookingPaymentNumberService $numbers,
        private readonly BookingExtensionHoldService $holds,
        private readonly BookingExtensionEventService $events,
    ) {}

    public function createPaymentIfRequired(BookingExtension $extension): ?BookingPayment
    {
        if (! $extension->requires_payment || (float) $extension->total_payable <= 0) {
            $extension->forceFill([
                'payment_status' => 'not_required',
            ])->save();

            return null;
        }

        if ($extension->booking_payment_id) {
            return BookingPayment::query()->find($extension->booking_payment_id);
        }

        $payment = BookingPayment::query()->create([
            'payment_number' => $this->numbers->generatePaymentNumber(),
            'booking_id' => $extension->booking_id,
            'booking_quote_id' => $extension->booking_quote_id,
            'booking_request_id' => null,
            'booking_extension_id' => $extension->id,
            'booking_relocation_id' => null,
            'guest_user_id' => $extension->guest_user_id,
            'host_user_id' => $extension->host_user_id,
            'property_id' => $extension->property_id,
            'room_id' => $extension->room_id,
            'sleeping_place_id' => $extension->sleeping_place_id,
            'payment_type' => 'full_payment',
            'payment_purpose' => 'extension_payment',
            'payment_method' => $extension->payment_method ?: 'internal_test',
            'status' => 'waiting_payment',
            'amount' => $extension->total_payable,
            'currency' => $extension->currency,
            'required_now_amount' => $extension->total_payable,
            'remaining_amount' => 0,
            'provider' => null,
            'provider_payment_id' => null,
            'provider_status' => null,
            'payment_deadline_at' => $extension->payment_deadline_at ?: now()->addMinutes(30),
            'description' => 'booking_extensions.payment.description',
        ]);

        $extension->forceFill([
            'booking_payment_id' => $payment->id,
            'payment_status' => 'waiting_payment',
            'payment_deadline_at' => $payment->payment_deadline_at,
            'status' => 'approved_waiting_payment',
        ])->save();

        return $payment;
    }

    public function markWaitingPayment(BookingExtension $extension): BookingExtension
    {
        $this->createPaymentIfRequired($extension);

        return $extension->refresh();
    }

    /**
     * @param  array<string, mixed>  $paymentData
     */
    public function markPaid(BookingExtension $extension, array $paymentData = []): BookingExtension
    {
        $payment = $this->createPaymentIfRequired($extension);

        if ($payment instanceof BookingPayment) {
            $payment->forceFill([
                'status' => 'paid',
                'provider_payment_id' => $paymentData['provider_payment_id'] ?? $payment->provider_payment_id,
                'provider_status' => 'paid',
                'paid_at' => now(),
            ])->save();
        }

        $extension->forceFill([
            'payment_status' => 'paid',
            'status' => 'paid',
            'paid_at' => now(),
        ])->save();

        $this->events->record($extension->refresh(), 'payment_completed');

        return $extension->refresh();
    }

    public function markPaymentFailed(BookingExtension $extension, string $reason): BookingExtension
    {
        if ($extension->booking_payment_id) {
            BookingPayment::query()
                ->whereKey($extension->booking_payment_id)
                ->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'failure_reason' => $reason,
                    'updated_at' => now(),
                ]);
        }

        $extension->forceFill([
            'status' => 'payment_failed',
            'payment_status' => 'failed',
        ])->save();

        $this->holds->releaseHold($extension->refresh(), 'payment_failed');
        $this->events->record($extension, 'payment_failed', ['reason' => $reason]);

        return $extension->refresh();
    }

    public function isPaymentDeadlinePassed(BookingExtension $extension): bool
    {
        return $extension->payment_deadline_at !== null && $extension->payment_deadline_at->isPast();
    }
}
