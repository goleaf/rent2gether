<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\User;

class BookingPrivacyService
{
    public function canGuestView(User $guest, Booking $booking): bool
    {
        return (int) $booking->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, Booking $booking): bool
    {
        return (int) $booking->host_user_id === (int) $host->id;
    }

    public function canHostRespond(User $host, Booking $booking): bool
    {
        return $this->canHostView($host, $booking)
            && in_array($this->value($booking->status), ['waiting_host_confirmation', 'waiting_guest_response', 'awaiting_host_approval'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, Booking $booking): array
    {
        if (! $this->canGuestView($guest, $booking)) {
            return [];
        }

        $booking->loadMissing('requirements');

        return [
            'booking_number' => $booking->booking_number,
            'status' => $this->value($booking->status),
            'payment_status' => $this->value($booking->payment_status),
            'sleeping_place_id' => $booking->sleeping_place_id,
            'room_id' => $booking->room_id,
            'property_id' => $booking->property_id,
            'check_in_date' => $booking->check_in_date?->toDateString(),
            'check_out_date' => $booking->check_out_date?->toDateString(),
            'nights_count' => (int) $booking->nights_count,
            'guests_count' => (int) $booking->guests_count,
            'total_payable' => (float) $booking->total_payable,
            'refundable_amount' => (float) $booking->refundable_amount,
            'non_refundable_amount' => (float) $booking->non_refundable_amount,
            'currency' => $booking->currency,
            'requirements' => $booking->requirements
                ->map(fn ($requirement): array => [
                    'requirement_key' => $requirement->requirement_key,
                    'status' => $requirement->status,
                    'required' => (bool) $requirement->required,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, Booking $booking): array
    {
        if (! $this->canHostView($host, $booking)) {
            return [];
        }

        return [
            'booking_number' => $booking->booking_number,
            'status' => $this->value($booking->status),
            'payment_status' => $this->value($booking->payment_status),
            'guest_user_id' => $booking->guest_user_id,
            'sleeping_place_id' => $booking->sleeping_place_id,
            'room_id' => $booking->room_id,
            'property_id' => $booking->property_id,
            'check_in_date' => $booking->check_in_date?->toDateString(),
            'check_out_date' => $booking->check_out_date?->toDateString(),
            'nights_count' => (int) $booking->nights_count,
            'guests_count' => (int) $booking->guests_count,
            'total_payable' => (float) $booking->total_payable,
            'host_payout_amount' => (float) $booking->host_payout_amount,
            'currency' => $booking->currency,
            'has_dispute' => (bool) $booking->has_dispute,
            'has_complaint' => (bool) $booking->has_complaint,
        ];
    }

    private function value(mixed $value): string
    {
        return $value instanceof \BackedEnum ? (string) $value->value : (string) $value;
    }
}
