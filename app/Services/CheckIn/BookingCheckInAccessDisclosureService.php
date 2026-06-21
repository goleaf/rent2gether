<?php

namespace App\Services\CheckIn;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInAccessDisclosure;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingCheckInAccessDisclosureService
{
    public function recordDisclosure(User $guest, BookingCheckIn $checkIn, string $type): BookingCheckInAccessDisclosure
    {
        if ((int) $checkIn->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('check_in.validation.not_your_booking'),
            ]);
        }

        $disclosure = BookingCheckInAccessDisclosure::query()->firstOrCreate(
            [
                'booking_check_in_id' => $checkIn->id,
                'guest_user_id' => $guest->id,
                'disclosure_type' => $type,
            ],
            [
                'booking_id' => $checkIn->booking_id,
                'shown_at' => now(),
                'shown_by_user_id' => $guest->id,
                'ip_address' => request()?->ip(),
                'user_agent' => request()?->userAgent(),
            ],
        );

        $this->touchCheckInDisclosureTimestamp($checkIn, $type);

        return $disclosure->refresh();
    }

    public function hasSeen(User $guest, BookingCheckIn $checkIn, string $type): bool
    {
        return BookingCheckInAccessDisclosure::query()
            ->where('booking_check_in_id', $checkIn->id)
            ->where('guest_user_id', $guest->id)
            ->where('disclosure_type', $type)
            ->exists();
    }

    /**
     * @return Collection<int, BookingCheckInAccessDisclosure>
     */
    public function getDisclosuresForBooking(Booking $booking): Collection
    {
        return BookingCheckInAccessDisclosure::query()
            ->where('booking_id', $booking->id)
            ->orderBy('shown_at')
            ->get();
    }

    private function touchCheckInDisclosureTimestamp(BookingCheckIn $checkIn, string $type): void
    {
        $field = match ($type) {
            'exact_address' => 'address_shown_at',
            'door_code', 'intercom_code', 'key_safe_code', 'night_entry_instruction' => 'access_details_shown_at',
            'host_contact' => 'host_contact_shown_at',
            'representative_contact' => 'representative_contact_shown_at',
            default => null,
        };

        if ($field !== null && $checkIn->{$field} === null) {
            $checkIn->forceFill([$field => now()])->save();
        }

        if ($checkIn->instructions_shown_at === null) {
            $checkIn->forceFill(['instructions_shown_at' => now()])->save();
        }
    }
}
