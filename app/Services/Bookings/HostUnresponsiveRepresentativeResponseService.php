<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveRepresentativeResponse;

class HostUnresponsiveRepresentativeResponseService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function markCanHelp(BookingHostUnresponsiveCase $case, array $data): HostUnresponsiveRepresentativeResponse
    {
        return $this->record($case, 'i_can_help', $data['message'] ?? null, ['will_meet_guest' => true]);
    }

    public function markOnTheWay(BookingHostUnresponsiveCase $case, ?string $eta = null): HostUnresponsiveRepresentativeResponse
    {
        return $this->record($case, 'i_am_on_the_way', null, ['will_meet_guest' => true, 'estimated_arrival_time' => $eta]);
    }

    public function markMetGuest(BookingHostUnresponsiveCase $case): HostUnresponsiveRepresentativeResponse
    {
        return $this->record($case, 'i_met_guest', null, ['will_meet_guest' => true]);
    }

    public function markAccessHelped(BookingHostUnresponsiveCase $case): HostUnresponsiveRepresentativeResponse
    {
        return $this->record($case, 'access_helped', null, ['access_help_provided' => true]);
    }

    public function markGuestCheckedIn(BookingHostUnresponsiveCase $case): HostUnresponsiveRepresentativeResponse
    {
        return $this->record($case, 'guest_checked_in', null, ['guest_checked_in' => true, 'keys_handed_over' => true]);
    }

    public function markCannotHelp(BookingHostUnresponsiveCase $case, ?string $message = null): HostUnresponsiveRepresentativeResponse
    {
        return $this->record($case, 'cannot_help', $message);
    }

    /**
     * @param  array<string, mixed>  $responseChanges
     */
    private function record(BookingHostUnresponsiveCase $case, string $type, ?string $message = null, array $responseChanges = []): HostUnresponsiveRepresentativeResponse
    {
        $case->forceFill([
            'status' => 'representative_responded',
            'representative_response' => $message ?? $case->representative_response,
            'representative_last_response_at' => now(),
        ])->save();

        $response = $case->representativeResponses()->create([
            'booking_id' => $case->booking_id,
            'host_representative_id' => $case->host_representative_id,
            'representative_user_id' => $case->hostRepresentative?->representative_user_id,
            'response_type' => $type,
            'message' => $message,
            ...$responseChanges,
        ]);

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'representative_responded', ['response_type' => $type]);
        app(HostUnresponsiveNotificationService::class)->notifyGuestHostResponded($case->fresh());

        return $response;
    }
}
