<?php

namespace App\Services\HostOccupants;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class HostOccupantPrivacyService
{
    public function canViewOccupant(User $host, Booking $booking): bool
    {
        return (int) $booking->host_user_id === (int) $host->id;
    }

    public function canViewGuestContact(User $host, Booking $booking): bool
    {
        if (! $this->canViewOccupant($host, $booking)) {
            return false;
        }

        $status = $this->value($booking->status);

        return in_array($status, [
            BookingStatus::Confirmed->value,
            BookingStatus::Paid->value,
            BookingStatus::ReadyForCheckIn->value,
            BookingStatus::CheckedIn->value,
            BookingStatus::InProgress->value,
            BookingStatus::ActiveStay->value,
            BookingStatus::LeavingSoon->value,
        ], true);
    }

    /**
     * @return array{chat:bool, phone:?string, email:?string}
     */
    public function filterGuestContactForHost(User $host, Booking $booking): array
    {
        return $this->hidePrivateGuestData([
            'chat' => $this->canViewGuestContact($host, $booking),
            'phone' => null,
            'email' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function hidePrivateGuestData(array $data): array
    {
        $data['phone'] = null;
        $data['email'] = null;

        return $data;
    }

    private function value(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return $value === null ? null : (string) $value;
    }
}
