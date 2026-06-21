<?php

namespace App\Livewire\Bookings\Payments\Concerns;

use App\Models\BookingPayment;
use App\Models\BookingRefund;

trait BuildsPaymentViewData
{
    protected function loadPayment(int $paymentId): BookingPayment
    {
        return BookingPayment::query()
            ->with([
                'booking:id,booking_number,sleeping_place_id,room_id,check_in_date,check_out_date,nights_count,total_payable,currency',
                'booking.sleepingPlace:id,display_name,title',
                'booking.room:id,title',
                'allocations:id,booking_payment_id,allocation_type,amount,currency,refundable',
                'attempts:id,booking_payment_id,attempt_number,status,payment_method,amount,currency,started_at,succeeded_at,failed_at',
                'deadlines:id,booking_payment_id,deadline_type,due_at,status',
                'receipt:id,booking_payment_id,receipt_number,status,issued_at',
            ])
            ->findOrFail($paymentId);
    }

    protected function loadRefund(int $refundId): BookingRefund
    {
        return BookingRefund::query()
            ->with('bookingPayment:id,payment_number,status')
            ->findOrFail($refundId);
    }

    /**
     * @return array<string, mixed>
     */
    protected function paymentSummary(BookingPayment $payment): array
    {
        $booking = $payment->booking;

        return [
            'payment_number' => $payment->payment_number,
            'status' => __('payments.statuses.'.$payment->status),
            'status_color' => $this->paymentStatusColor($payment->status),
            'amount' => $this->money($payment->amount, $payment->currency),
            'required_now_amount' => $this->money($payment->required_now_amount, $payment->currency),
            'remaining_amount' => $this->money($payment->remaining_amount, $payment->currency),
            'deadline' => $payment->payment_deadline_at?->translatedFormat('d M, H:i'),
            'booking_number' => $booking?->booking_number,
            'sleeping_place' => $booking?->sleepingPlace?->display_name ?? $booking?->sleepingPlace?->title,
            'room' => $booking?->room?->title,
            'dates' => trim(($booking?->check_in_date?->format('d M') ?? '').' - '.($booking?->check_out_date?->format('d M') ?? '')),
            'nights_count' => $booking?->nights_count,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function allocationRows(BookingPayment $payment): array
    {
        return $payment->allocations
            ->map(fn ($allocation): array => [
                'label' => __('payments.allocation_types.'.$allocation->allocation_type),
                'amount' => $this->money($allocation->amount, $allocation->currency),
                'refundable' => (bool) $allocation->refundable,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function attemptRows(BookingPayment $payment): array
    {
        return $payment->attempts
            ->map(fn ($attempt): array => [
                'number' => $attempt->attempt_number,
                'status' => __('payments.attempt_statuses.'.$attempt->status),
                'amount' => $this->money($attempt->amount, $attempt->currency),
                'started_at' => $attempt->started_at?->translatedFormat('d M, H:i'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function deadlineRows(BookingPayment $payment): array
    {
        return $payment->deadlines
            ->map(fn ($deadline): array => [
                'type' => __('payments.deadline_types.'.$deadline->deadline_type),
                'status' => __('payments.deadline_statuses.'.$deadline->status),
                'due_at' => $deadline->due_at?->translatedFormat('d M, H:i'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function receiptSummary(BookingPayment $payment): array
    {
        $receipt = $payment->receipt;

        return [
            'receipt_number' => $receipt?->receipt_number,
            'status' => $receipt ? __('payments.receipt_statuses.'.$receipt->status) : __('payments.empty_states.no_receipt'),
            'issued_at' => $receipt?->issued_at?->translatedFormat('d M, H:i'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function refundSummary(BookingRefund $refund): array
    {
        return [
            'refund_number' => $refund->refund_number,
            'type' => __('payments.refund_types.'.$refund->refund_type),
            'status' => __('payments.refund_statuses.'.$refund->status),
            'status_color' => $this->refundStatusColor($refund->status),
            'amount' => $this->money($refund->amount, $refund->currency),
            'reason' => $refund->reason_key ? __('payments.refund_reasons.'.$refund->reason_key) : null,
        ];
    }

    protected function money(float|int|string|null $amount, ?string $currency): string
    {
        return trim(number_format((float) $amount, 2).' '.($currency ?: 'EUR'));
    }

    protected function paymentStatusColor(string $status): string
    {
        return match ($status) {
            'paid' => 'green',
            'partially_paid', 'pending', 'payment_started' => 'amber',
            'failed', 'expired', 'cancelled', 'disputed' => 'red',
            default => 'zinc',
        };
    }

    protected function refundStatusColor(string $status): string
    {
        return match ($status) {
            'completed' => 'green',
            'processing', 'approved' => 'amber',
            'failed', 'cancelled' => 'red',
            default => 'zinc',
        };
    }
}
