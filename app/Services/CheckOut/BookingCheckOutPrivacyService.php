<?php

namespace App\Services\CheckOut;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\User;

class BookingCheckOutPrivacyService
{
    public function canGuestViewCheckout(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id;
    }

    public function canHostViewCheckout(User $host, Booking $booking): bool
    {
        return (int) $booking->host_user_id === (int) $host->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingCheckOut $checkOut): array
    {
        if ((int) $checkOut->guest_user_id !== (int) $guest->id) {
            return [];
        }

        return [
            'status' => $checkOut->status,
            'check_out_date' => $checkOut->check_out_date?->toDateString(),
            'planned_check_out_time' => $checkOut->planned_check_out_time,
            'deposit_status' => $checkOut->depositDecision?->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingCheckOut $checkOut): array
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            return [];
        }

        return $checkOut->only(['status', 'check_out_date', 'planned_check_out_time', 'has_damage', 'has_extra_dirty', 'has_forgotten_items']);
    }
}
