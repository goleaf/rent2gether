<?php

namespace App\Services\Bookings;

use App\Models\BookingCheckIn;
use App\Models\BookingHostUnresponsiveCase;
use App\Models\User;
use App\Services\CheckIn\BookingCheckInAccessDisclosureService;
use Illuminate\Validation\ValidationException;

class HostUnresponsiveInstructionService
{
    public function showAllowedInstructions(User $guest, BookingHostUnresponsiveCase $case): array
    {
        if ((int) $case->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('host_unresponsive.validation.not_your_booking'),
            ]);
        }

        $case->loadMissing('checkIn');
        $checkIn = $case->checkIn;
        $disclosures = [
            'instruction' => true,
            'exact_address' => $checkIn instanceof BookingCheckIn,
            'host_contact' => $checkIn instanceof BookingCheckIn,
            'representative_contact' => $case->host_representative_id !== null && $checkIn instanceof BookingCheckIn,
            'door_code' => $checkIn instanceof BookingCheckIn && (bool) $checkIn->door_code_provided,
            'intercom_code' => $checkIn instanceof BookingCheckIn && (bool) $checkIn->intercom_code_provided,
            'key_safe_code' => $checkIn instanceof BookingCheckIn && (bool) $checkIn->key_safe_code_provided,
        ];

        foreach ($disclosures as $type => $allowed) {
            if ($allowed && $type !== 'instruction') {
                $this->recordInstructionDisclosure($case, $type);
            }
        }

        $case->forceFill([
            'instruction_was_available' => true,
            'exact_address_was_shown' => (bool) $disclosures['exact_address'],
            'door_code_was_shown' => (bool) $disclosures['door_code'],
            'intercom_code_was_shown' => (bool) $disclosures['intercom_code'],
            'key_safe_code_was_shown' => (bool) $disclosures['key_safe_code'],
            'host_contact_was_shown' => (bool) $disclosures['host_contact'],
            'representative_contact_was_shown' => (bool) $disclosures['representative_contact'],
        ])->save();

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'instructions_auto_shown', ['disclosures' => array_keys(array_filter($disclosures))]);

        return $disclosures;
    }

    public function resendInstructionsToGuest(BookingHostUnresponsiveCase $case): void
    {
        $case->forceFill(['status' => 'instructions_resent'])->save();
        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'instructions_auto_shown');
    }

    public function recordInstructionDisclosure(BookingHostUnresponsiveCase $case, string $disclosureType): void
    {
        $case->loadMissing('guest', 'checkIn');

        if (! $case->guest instanceof User || ! $case->checkIn instanceof BookingCheckIn) {
            return;
        }

        app(BookingCheckInAccessDisclosureService::class)->recordDisclosure($case->guest, $case->checkIn, $disclosureType);
    }
}
