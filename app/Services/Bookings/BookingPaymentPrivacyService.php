<?php

namespace App\Services\Bookings;

use App\Models\BookingPayment;
use App\Models\BookingRefund;
use App\Models\User;

class BookingPaymentPrivacyService
{
    public function canGuestViewPayment(User $guest, BookingPayment $payment): bool
    {
        return (int) $payment->guest_user_id === (int) $guest->id;
    }

    public function canHostViewPayment(User $host, BookingPayment $payment): bool
    {
        return (int) $payment->host_user_id === (int) $host->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterPaymentForGuest(User $guest, BookingPayment $payment): array
    {
        if (! $this->canGuestViewPayment($guest, $payment)) {
            return [];
        }

        return [
            'payment_number' => $payment->payment_number,
            'booking_id' => $payment->booking_id,
            'status' => $payment->status,
            'payment_type' => $payment->payment_type,
            'payment_purpose' => $payment->payment_purpose,
            'payment_method' => $payment->payment_method,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'required_now_amount' => (float) $payment->required_now_amount,
            'remaining_amount' => (float) $payment->remaining_amount,
            'payment_deadline_at' => $payment->payment_deadline_at,
            'paid_at' => $payment->paid_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterPaymentForHost(User $host, BookingPayment $payment): array
    {
        if (! $this->canHostViewPayment($host, $payment)) {
            return [];
        }

        return [
            'payment_number' => $payment->payment_number,
            'booking_id' => $payment->booking_id,
            'status' => $payment->status,
            'payment_type' => $payment->payment_type,
            'payment_purpose' => $payment->payment_purpose,
            'amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'paid_at' => $payment->paid_at,
            'payment_deadline_at' => $payment->payment_deadline_at,
        ];
    }

    public function canGuestViewRefund(User $guest, BookingRefund $refund): bool
    {
        return (int) $refund->guest_user_id === (int) $guest->id;
    }

    public function canHostViewRefund(User $host, BookingRefund $refund): bool
    {
        return (int) $refund->host_user_id === (int) $host->id;
    }
}
