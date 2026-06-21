<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingRelocation;
use App\Models\User;

class BookingRelocationPrivacyService
{
    public function canGuestView(User $guest, BookingRelocation $relocation): bool
    {
        return (int) $relocation->guest_user_id === (int) $guest->id
            || (int) $relocation->originalBooking?->guest_user_id === (int) $guest->id;
    }

    public function canGuestCreate(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id
            && in_array($this->bookingStatus($booking), $this->relocatableStatuses(), true);
    }

    public function canHostView(User $host, BookingRelocation $relocation): bool
    {
        return (int) $relocation->host_user_id === (int) $host->id
            || (int) $relocation->originalBooking?->host_user_id === (int) $host->id;
    }

    public function canHostRespond(User $host, BookingRelocation $relocation): bool
    {
        return $this->canHostView($host, $relocation)
            && ! in_array($this->relocationStatus($relocation), ['applied', 'closed', 'rejected', 'cancelled_by_guest', 'cancelled_by_host'], true);
    }

    public function canGuestConsent(User $guest, BookingRelocation $relocation): bool
    {
        return $this->canGuestView($guest, $relocation);
    }

    public function canHostConsent(User $host, BookingRelocation $relocation): bool
    {
        return $this->canHostView($host, $relocation);
    }

    public function canApply(User $user, BookingRelocation $relocation): bool
    {
        return $this->canGuestView($user, $relocation) || $this->canHostView($user, $relocation);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingRelocation $relocation): array
    {
        abort_unless($this->canGuestView($guest, $relocation), 403);

        return $relocation->only([
            'relocation_number',
            'original_booking_id',
            'new_booking_id',
            'current_room_id',
            'current_sleeping_place_id',
            'new_room_id',
            'new_sleeping_place_id',
            'reason',
            'status',
            'relocation_date',
            'price_difference_amount',
            'additional_payment_amount',
            'refund_amount',
            'currency',
            'price_difference_payer',
            'requires_guest_consent',
            'requires_host_consent',
            'payment_status',
            'refund_status',
            'guest_comment',
            'host_comment',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingRelocation $relocation): array
    {
        abort_unless($this->canHostView($host, $relocation), 403);

        return $relocation->makeHidden(['payment_method'])->toArray();
    }

    /**
     * @return list<string>
     */
    private function relocatableStatuses(): array
    {
        return [
            'confirmed',
            'paid',
            'ready_for_check_in',
            'guest_checked_in',
            'checked_in',
            'stay_in_progress',
            'in_progress',
            'active',
            'active_stay',
            'active_with_warning',
            'check_out_soon',
        ];
    }

    private function bookingStatus(Booking $booking): string
    {
        return $booking->status instanceof \BackedEnum
            ? (string) $booking->status->value
            : (string) $booking->status;
    }

    private function relocationStatus(BookingRelocation $relocation): string
    {
        return $relocation->status instanceof \BackedEnum
            ? (string) $relocation->status->value
            : (string) $relocation->status;
    }
}
