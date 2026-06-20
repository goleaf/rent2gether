<?php

namespace App\Services\Occupants;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CoLivingVisibilitySetting;
use App\Models\User;

class CoLivingPrivacyService
{
    /**
     * @var list<string>
     */
    private const SENSITIVE_FIELDS = [
        'phone',
        'email',
        'date_of_birth',
        'documents',
        'exact_workplace',
        'exact_school',
        'private_notes',
        'internal_flags',
        'complaint_details',
        'private_messages',
        'password',
        'remember_token',
        'guest_message',
        'host_condition_note',
    ];

    public function canShowBeforeBooking(User $occupant): bool
    {
        return $this->settingsFor($occupant)->allow_profile_in_prebooking_summary;
    }

    public function canShowAfterConfirmedBooking(User $viewer, User $occupant, Booking $booking): bool
    {
        if ((int) $booking->guest_user_id !== (int) $viewer->id) {
            return false;
        }

        if (! $this->bookingAllowsRoommateDetails($booking)) {
            return false;
        }

        if ((int) $viewer->id === (int) $occupant->id) {
            return false;
        }

        return $this->settingsFor($occupant)->allow_profile_after_confirmed_booking;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filterFieldsForPublicSummary(array $data): array
    {
        $data = $this->hideSensitiveFields($data);

        unset(
            $data['alias'],
            $data['public_alias'],
            $data['real_first_name'],
            $data['age_range'],
            $data['avatar'],
            $data['country'],
            $data['city'],
            $data['checkout_date'],
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filterFieldsForConfirmedGuest(array $data): array
    {
        return $this->hideSensitiveFields($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function hideSensitiveFields(array $data): array
    {
        foreach (self::SENSITIVE_FIELDS as $field) {
            unset($data[$field]);
        }

        return $data;
    }

    public function settingsFor(User $user): CoLivingVisibilitySetting
    {
        return $user->coLivingVisibilitySetting
            ?: CoLivingVisibilitySetting::query()->firstOrCreate(['user_id' => $user->id]);
    }

    private function bookingAllowsRoommateDetails(Booking $booking): bool
    {
        $status = $booking->status instanceof BookingStatus ? $booking->status : BookingStatus::tryFrom((string) $booking->status);

        return in_array($status, [
            BookingStatus::Confirmed,
            BookingStatus::Paid,
            BookingStatus::ReadyForCheckIn,
            BookingStatus::CheckedIn,
            BookingStatus::InProgress,
            BookingStatus::ActiveStay,
            BookingStatus::LeavingSoon,
        ], true);
    }
}
