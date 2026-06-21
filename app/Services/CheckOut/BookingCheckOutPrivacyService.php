<?php

namespace App\Services\CheckOut;

use App\Models\Booking;
use App\Models\BookingCheckOut;
use App\Models\BookingCheckOutMedia;
use App\Models\User;

class BookingCheckOutPrivacyService
{
    public function canGuestView(User $guest, BookingCheckOut $checkOut): bool
    {
        return (int) $checkOut->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingCheckOut $checkOut): bool
    {
        return (int) $checkOut->host_user_id === (int) $host->id;
    }

    public function canGuestConfirm(User $guest, BookingCheckOut $checkOut): bool
    {
        return $this->canGuestView($guest, $checkOut) && ! in_array($checkOut->status, ['closed', 'cancelled'], true);
    }

    public function canHostConfirm(User $host, BookingCheckOut $checkOut): bool
    {
        return $this->canHostView($host, $checkOut) && ! in_array($checkOut->status, ['closed', 'cancelled'], true);
    }

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
            'guest_confirmed_checkout_at' => $checkOut->guest_confirmed_checkout_at?->toIso8601String(),
            'host_confirmed_checkout_at' => $checkOut->host_confirmed_checkout_at?->toIso8601String(),
            'has_forgotten_items' => (bool) $checkOut->has_forgotten_items,
            'has_dispute' => (bool) $checkOut->has_dispute,
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

        return $checkOut->only([
            'checkout_number',
            'status',
            'check_out_date',
            'planned_check_out_time',
            'actual_check_out_at',
            'has_damage',
            'has_extra_dirty',
            'has_extra_dirt',
            'has_forgotten_items',
            'has_inventory_issue',
            'deposit_deduction_requested',
            'cleaning_required',
            'inspection_required',
            'repair_required',
            'internal_host_note',
        ]);
    }

    public function canViewMedia(User $user, BookingCheckOutMedia $media): bool
    {
        $media->loadMissing('checkOut');
        $checkOut = $media->checkOut;

        if (! $checkOut) {
            return false;
        }

        if ((int) $user->id === (int) $checkOut->host_user_id) {
            return ! in_array($media->visibility, ['internal', 'future_support_only'], true);
        }

        if ((int) $user->id !== (int) $checkOut->guest_user_id) {
            return false;
        }

        return in_array($media->visibility, ['guest_and_host', 'guest_only'], true);
    }
}
