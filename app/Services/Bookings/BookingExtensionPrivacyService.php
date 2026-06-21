<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingExtension;
use App\Models\User;

class BookingExtensionPrivacyService
{
    public function canGuestCreate(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id
            && in_array($this->bookingStatus($booking), $this->extendableStatuses(), true);
    }

    public function canGuestView(User $guest, BookingExtension $extension): bool
    {
        return (int) $extension->guest_user_id === (int) $guest->id
            || (int) $extension->booking?->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingExtension $extension): bool
    {
        return (int) $extension->host_user_id === (int) $host->id
            || (int) $extension->booking?->host_user_id === (int) $host->id;
    }

    public function canHostRespond(User $host, BookingExtension $extension): bool
    {
        return $this->canHostView($host, $extension)
            && ! in_array($this->extensionStatus($extension), ['applied', 'closed', 'cancelled_by_guest', 'cancelled_by_host', 'rejected'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingExtension $extension): array
    {
        abort_unless($this->canGuestView($guest, $extension), 403);

        return $extension->only([
            'extension_number',
            'booking_id',
            'sleeping_place_id',
            'current_check_out_date',
            'new_check_out_date',
            'additional_nights_count',
            'status',
            'payment_status',
            'requires_host_confirmation',
            'requires_payment',
            'total_payable',
            'currency',
            'guest_message',
            'host_response',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingExtension $extension): array
    {
        abort_unless($this->canHostView($host, $extension), 403);

        return $extension->makeHidden(['payment_method'])->toArray();
    }

    /**
     * @return list<string>
     */
    private function extendableStatuses(): array
    {
        return [
            'guest_checked_in',
            'checked_in',
            'stay_in_progress',
            'in_progress',
            'check_out_soon',
            'active',
            'active_stay',
            'active_with_warning',
            'confirmed',
            'paid',
            'ready_for_check_in',
        ];
    }

    private function bookingStatus(Booking $booking): string
    {
        return $booking->status instanceof \BackedEnum
            ? (string) $booking->status->value
            : (string) $booking->status;
    }

    private function extensionStatus(BookingExtension $extension): string
    {
        return $extension->status instanceof \BackedEnum
            ? (string) $extension->status->value
            : (string) $extension->status;
    }
}
