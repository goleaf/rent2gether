<?php

namespace App\Services\CheckIn;

use App\Models\Booking;
use App\Models\BookingCheckIn;
use App\Models\BookingCheckInMedia;
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

    public function canGuestView(User $guest, BookingCheckIn $checkIn): bool
    {
        return (int) $checkIn->guest_user_id === (int) $guest->id;
    }

    public function canHostView(User $host, BookingCheckIn $checkIn): bool
    {
        return (int) $checkIn->host_user_id === (int) $host->id;
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForGuest(User $guest, BookingCheckIn $checkIn): array
    {
        if (! $this->canGuestView($guest, $checkIn)) {
            return [];
        }

        $instructions = $this->instructions->getVisibleInstructions($guest, $checkIn);

        return [
            'id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
            'status' => $checkIn->status,
            'check_in_date' => $checkIn->check_in_date?->toDateString(),
            'planned_check_in_time' => $checkIn->planned_check_in_time,
            'instructions' => $instructions,
            'has_problem' => (bool) $checkIn->has_problem,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function filterForHost(User $host, BookingCheckIn $checkIn): array
    {
        if (! $this->canHostView($host, $checkIn)) {
            return [];
        }

        return [
            'id' => $checkIn->id,
            'booking_id' => $checkIn->booking_id,
            'status' => $checkIn->status,
            'guest_user_id' => $checkIn->guest_user_id,
            'check_in_date' => $checkIn->check_in_date?->toDateString(),
            'planned_check_in_time' => $checkIn->planned_check_in_time,
            'has_problem' => (bool) $checkIn->has_problem,
        ];
    }

    public function canViewMedia(User $user, BookingCheckInMedia $media): bool
    {
        $media->loadMissing('checkIn:id,guest_user_id,host_user_id');

        if ($media->visibility === 'internal') {
            return false;
        }

        if ($media->visibility === 'guest_only') {
            return (int) $media->checkIn?->guest_user_id === (int) $user->id;
        }

        if ($media->visibility === 'host_only') {
            return (int) $media->checkIn?->host_user_id === (int) $user->id;
        }

        return in_array((int) $user->id, [
            (int) $media->checkIn?->guest_user_id,
            (int) $media->checkIn?->host_user_id,
        ], true);
    }
}
