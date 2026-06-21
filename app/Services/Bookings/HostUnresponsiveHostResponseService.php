<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostRepresentative;
use App\Models\HostUnresponsiveHostResponse;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class HostUnresponsiveHostResponseService
{
    public function markAvailable(User $host, BookingHostUnresponsiveCase $case, ?string $message = null): HostUnresponsiveHostResponse
    {
        return $this->record($host, $case, 'i_am_available', $message, ['status' => 'host_responded']);
    }

    public function sendInstruction(User $host, BookingHostUnresponsiveCase $case, string $message): HostUnresponsiveHostResponse
    {
        $response = $this->record($host, $case, 'instruction_sent', $message, [
            'status' => 'instructions_resent',
            'instruction_was_available' => true,
        ], ['instruction_resent' => true]);

        app(HostUnresponsiveInstructionService::class)->resendInstructionsToGuest($case->fresh());

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function sendAccessDetails(User $host, BookingHostUnresponsiveCase $case, array $data): HostUnresponsiveHostResponse
    {
        return $this->record($host, $case, 'access_details_sent', $data['message'] ?? null, [
            'status' => 'host_responded',
            'door_code_was_shown' => (bool) ($data['door_code_provided'] ?? false),
            'intercom_code_was_shown' => (bool) ($data['intercom_code_provided'] ?? false),
            'key_safe_code_was_shown' => (bool) ($data['key_safe_code_provided'] ?? false),
        ], ['access_details_provided' => true]);
    }

    public function assignRepresentative(User $host, BookingHostUnresponsiveCase $case, HostRepresentative $representative): HostUnresponsiveHostResponse
    {
        return $this->record($host, $case, 'representative_will_help', null, [
            'host_representative_id' => $representative->id,
            'status' => 'host_responded',
        ], ['representative_assigned' => true]);
    }

    public function offerRelocation(User $host, BookingHostUnresponsiveCase $case, SleepingPlace $place): HostUnresponsiveHostResponse
    {
        app(HostUnresponsiveEventService::class)->record($case, 'relocation_requested', ['sleeping_place_id' => $place->id]);

        return $this->record($host, $case, 'offer_relocation', null, [
            'status' => 'host_responded',
            'guest_wants_relocation' => true,
        ], ['alternative_sleeping_place_id' => $place->id]);
    }

    public function denyUnresponsive(User $host, BookingHostUnresponsiveCase $case, string $message): HostUnresponsiveHostResponse
    {
        return $this->record($host, $case, 'deny_unresponsive', $message, [
            'status' => 'host_responded',
            'decision_key' => 'rejected_host_responsive',
        ]);
    }

    /**
     * @param  array<string, mixed>  $caseChanges
     * @param  array<string, mixed>  $responseChanges
     */
    private function record(User $host, BookingHostUnresponsiveCase $case, string $type, ?string $message, array $caseChanges = [], array $responseChanges = []): HostUnresponsiveHostResponse
    {
        if ((int) $case->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('host_unresponsive.validation.not_host_booking'));
        }

        $case->forceFill([
            ...$caseChanges,
            'host_response' => $message ?? $case->host_response,
            'host_last_response_at' => now(),
        ])->save();

        $response = $case->hostResponses()->create([
            'booking_id' => $case->booking_id,
            'host_user_id' => $host->id,
            'response_type' => $type,
            'message' => $message,
            ...$responseChanges,
        ]);

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'host_responded', ['user_id' => $host->id, 'response_type' => $type]);
        app(HostUnresponsiveNotificationService::class)->notifyGuestHostResponded($case->fresh());

        return $response;
    }
}
