<?php

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingQuote;
use App\Models\User;

class BookingCoreService
{
    public function __construct(
        private readonly BookingCreationService $creation,
        private readonly BookingStatusService $statuses,
        private readonly BookingPrivacyService $privacy,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createInstantBooking(User $guest, BookingQuote $quote, array $data = []): Booking
    {
        return $this->creation->createInstantBooking($guest, $quote, $data);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(Booking $booking, string $newStatus, ?User $user = null, array $context = []): Booking
    {
        return $this->statuses->transition($booking, $newStatus, $user, $context);
    }

    public function canGuestView(User $guest, Booking $booking): bool
    {
        return $this->privacy->canGuestView($guest, $booking);
    }

    public function canHostView(User $host, Booking $booking): bool
    {
        return $this->privacy->canHostView($host, $booking);
    }
}
