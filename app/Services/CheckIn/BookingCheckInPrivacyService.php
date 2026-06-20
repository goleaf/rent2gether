<?php

namespace App\Services\CheckIn;

use App\Models\Booking;
use App\Models\User;

class BookingCheckInPrivacyService
{
    public function __construct(
        private readonly BookingCheckInInstructionService $instructions,
    ) {}

    public function canGuestSeeAddress(User $guest, Booking $booking): bool
    {
        return $this->instructions->canShowExactAddress($guest, $booking);
    }

    public function canGuestSeeCodes(User $guest, Booking $booking): bool
    {
        return $this->instructions->canShowAccessCodes($guest, $booking);
    }

    public function canGuestSeeHostContact(User $guest, Booking $booking): bool
    {
        return (bool) $this->instructions->getHostContact($guest, $booking)['chat'];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterInstructionsForGuest(User $guest, Booking $booking): array
    {
        return $this->instructions->getGuestInstructions($guest, $booking);
    }
}
