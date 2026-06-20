<?php

namespace App\Services\Rooms;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;

class RoomPrivacyService
{
    public function canShowRoomNumber(User $user, Room $room, ?Booking $booking = null): bool
    {
        if ($this->isRoomHost($user, $room)) {
            return true;
        }

        return $this->hasConfirmedBookingForRoom($user, $room, $booking);
    }

    public function canShowOccupantDetails(User $user, Room $room, ?Booking $booking = null): bool
    {
        return $this->isRoomHost($user, $room);
    }

    /**
     * @return array<string, mixed>
     */
    public function getSafeRoomSummary(Room $room, mixed $range = null): array
    {
        unset($range);

        return [
            'room_type' => $room->room_type?->value ?? $room->type?->value,
            'gender_policy' => $room->gender_policy?->value,
            'sleeping_places_count' => (int) ($room->sleeping_places_count ?: $room->beds_count ?: 0),
            'free_sleeping_places_count' => (int) ($room->free_sleeping_places_count ?: $room->available_places_count ?: 0),
            'occupied_sleeping_places_count' => (int) ($room->occupied_sleeping_places_count ?: $room->occupied_places_count ?: 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function hidePrivateOccupantData(array $data): array
    {
        unset($data['names'], $data['emails'], $data['phones'], $data['private_notes']);

        return $data;
    }

    private function isRoomHost(User $user, Room $room): bool
    {
        $room->loadMissing('property:id,host_user_id,user_id');

        return (int) $room->property?->host_user_id === $user->id
            || (int) $room->property?->user_id === $user->id;
    }

    private function hasConfirmedBookingForRoom(User $user, Room $room, ?Booking $booking): bool
    {
        if (! $booking instanceof Booking) {
            return false;
        }

        return (int) $booking->guest_user_id === $user->id
            && (int) $booking->room_id === $room->id
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
