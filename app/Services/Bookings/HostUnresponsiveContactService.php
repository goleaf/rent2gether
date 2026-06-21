<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostRepresentative;
use App\Models\HostUnresponsiveContactAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class HostUnresponsiveContactService
{
    public function __construct(
        private readonly HostUnresponsiveEventService $events,
        private readonly HostUnresponsiveNotificationService $notifications,
    ) {}

    public function contactHost(BookingHostUnresponsiveCase $case): HostUnresponsiveContactAttempt
    {
        $case->loadMissing('host');

        $attempt = $this->createAttempt($case, $case->host, 'host', 'urgent_check_in_alert');

        $case->forceFill([
            'host_contact_attempts_count' => $case->host_contact_attempts_count + 1,
            'last_host_contact_attempt_at' => $attempt->attempted_at,
            'status' => 'host_contact_attempted',
        ])->save();

        $this->events->record($case->fresh(), 'host_contact_attempted', ['attempt_id' => $attempt->id]);
        $this->notifications->notifyHostUrgent($case->fresh());

        return $attempt;
    }

    public function contactRepresentative(BookingHostUnresponsiveCase $case): ?HostUnresponsiveContactAttempt
    {
        $case->loadMissing('hostRepresentative.representativeUser');

        if (! $case->hostRepresentative instanceof HostRepresentative) {
            return null;
        }

        $attempt = $this->createAttempt($case, $case->hostRepresentative->representativeUser, 'host_representative', 'guest_waiting_alert', $case->hostRepresentative);

        $case->forceFill([
            'representative_contact_attempts_count' => $case->representative_contact_attempts_count + 1,
            'last_representative_contact_attempt_at' => $attempt->attempted_at,
            'status' => 'representative_contact_attempted',
        ])->save();

        $this->events->record($case->fresh(), 'representative_contact_attempted', ['attempt_id' => $attempt->id]);
        $this->notifications->notifyRepresentativeUrgent($case->fresh());

        return $attempt;
    }

    /**
     * @return Collection<int, HostUnresponsiveContactAttempt>
     */
    public function sendUrgentAlert(BookingHostUnresponsiveCase $case): Collection
    {
        $attempts = collect([$this->contactHost($case)]);
        $representativeAttempt = $this->contactRepresentative($case->fresh());

        if ($representativeAttempt instanceof HostUnresponsiveContactAttempt) {
            $attempts->push($representativeAttempt);
        }

        return $attempts;
    }

    public function sendFinalWarningToHost(BookingHostUnresponsiveCase $case): HostUnresponsiveContactAttempt
    {
        return $this->createAttempt($case, $case->host()->first(), 'host', 'final_warning');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordManualGuestCall(BookingHostUnresponsiveCase $case, array $data): HostUnresponsiveContactAttempt
    {
        return $this->createAttempt($case, $case->host()->first(), 'host', 'host_reply', null, [
            'contact_channel' => 'manual_guest_call',
            'message_text' => $data['message'] ?? null,
        ]);
    }

    private function createAttempt(BookingHostUnresponsiveCase $case, ?User $target, string $targetType, string $attemptType, ?HostRepresentative $representative = null, array $overrides = []): HostUnresponsiveContactAttempt
    {
        return $case->contactAttempts()->create([
            'booking_id' => $case->booking_id,
            'target_user_id' => $target?->id,
            'target_type' => $targetType,
            'target_name_snapshot' => $target?->name ?? $representative?->name,
            'target_contact_snapshot' => $representative?->phone ?? $representative?->email,
            'contact_channel' => 'in_app',
            'attempt_type' => $attemptType,
            'status' => 'sent',
            'message_key' => 'host_unresponsive.notifications.'.$attemptType,
            'attempted_at' => now(),
            ...$overrides,
        ]);
    }
}
