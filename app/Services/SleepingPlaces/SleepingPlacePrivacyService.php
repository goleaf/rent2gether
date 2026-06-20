<?php

namespace App\Services\SleepingPlaces;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\SleepingPlace;
use App\Models\User;

class SleepingPlacePrivacyService
{
    public function canShowInternalName(User $user, SleepingPlace $place, ?Booking $booking = null): bool
    {
        if ($this->isHost($user, $place)) {
            return true;
        }

        return $this->hasConfirmedBooking($user, $place, $booking);
    }

    public function canShowHostConditionNote(User $user, SleepingPlace $place, ?Booking $booking = null): bool
    {
        unset($booking);

        return $this->isHost($user, $place);
    }

    /**
     * @return array<string, mixed>
     */
    public function publicPlaceSummary(SleepingPlace $place): array
    {
        return [
            'sleeping_place_type' => $place->sleeping_place_type?->value ?? $place->type?->value,
            'place_number' => $place->place_number,
            'bunk_level' => $place->bunk_level,
            'max_guests' => (int) $place->max_guests,
        ];
    }

    private function isHost(User $user, SleepingPlace $place): bool
    {
        $place->loadMissing('property:id,host_user_id,user_id');

        return (int) $place->property?->host_user_id === $user->id
            || (int) $place->property?->user_id === $user->id;
    }

    private function hasConfirmedBooking(User $user, SleepingPlace $place, ?Booking $booking): bool
    {
        if (! $booking instanceof Booking) {
            return false;
        }

        return (int) $booking->guest_user_id === $user->id
            && (int) $booking->sleeping_place_id === $place->id
            && in_array($booking->status, [
                BookingStatus::Confirmed,
                BookingStatus::Paid,
                BookingStatus::ReadyForCheckIn,
                BookingStatus::CheckedIn,
                BookingStatus::InProgress,
                BookingStatus::ActiveStay,
            ], true)
            && in_array($booking->payment_status, [
                PaymentStatus::Paid,
                PaymentStatus::PartiallyPaid,
            ], true);
    }
}
