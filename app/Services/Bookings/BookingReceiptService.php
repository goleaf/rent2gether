<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\PaymentReceipt;

class BookingReceiptService
{
    public function __construct(
        private readonly BookingPaymentNumberService $numbers,
    ) {}

    public function createReceipt(BookingPayment $payment): PaymentReceipt
    {
        return PaymentReceipt::query()->firstOrCreate(
            ['booking_payment_id' => $payment->id],
            [
                'booking_id' => $payment->booking_id,
                'guest_user_id' => $payment->guest_user_id,
                'receipt_number' => $this->numbers->generateReceiptNumber(),
                'status' => 'draft',
                'receipt_data_json' => $this->getReceiptData($payment),
            ],
        );
    }

    public function issueReceipt(PaymentReceipt $receipt): PaymentReceipt
    {
        $receipt->forceFill([
            'status' => 'issued',
            'issued_at' => now(),
        ])->save();

        return $receipt->fresh();
    }

    public function cancelReceipt(PaymentReceipt $receipt): PaymentReceipt
    {
        $receipt->forceFill(['status' => 'cancelled'])->save();

        return $receipt->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function getReceiptData(BookingPayment $payment): array
    {
        $payment->loadMissing('booking', 'allocations');

        return [
            'payment_number' => $payment->payment_number,
            'booking_number' => $payment->booking?->booking_number,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'status' => $payment->status,
            'paid_at' => $payment->paid_at?->toIso8601String(),
            'allocations' => $payment->allocations->map(fn ($allocation): array => [
                'type' => $allocation->allocation_type,
                'amount' => (float) $allocation->amount,
                'refundable' => (bool) $allocation->refundable,
            ])->values()->all(),
        ];
    }
}
