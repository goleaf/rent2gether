<?php

namespace App\Services\Bookings;

use App\Models\BookingHostUnresponsiveCase;
use App\Models\HostUnresponsiveGuestAction;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class HostUnresponsiveGuestActionService
{
    public function markAtAddress(User $guest, BookingHostUnresponsiveCase $case, ?string $note = null): HostUnresponsiveGuestAction
    {
        return $this->record($guest, $case, 'marked_at_address', [
            'guest_at_address' => true,
            'guest_marked_arrived' => true,
            'actual_guest_arrival_at' => $case->actual_guest_arrival_at ?? now(),
        ], $note);
    }

    public function markWaitingOutside(User $guest, BookingHostUnresponsiveCase $case, ?string $note = null): HostUnresponsiveGuestAction
    {
        $action = $this->record($guest, $case, 'marked_waiting_outside', [
            'guest_waiting_outside' => true,
            'guest_at_address' => true,
            'status' => 'guest_waiting',
        ], $note);

        app(HostUnresponsiveNotificationService::class)->notifyHostGuestWaiting($case->fresh());
        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'guest_waiting_started', ['user_id' => $guest->id]);

        return $action;
    }

    public function markFeelsUnsafe(User $guest, BookingHostUnresponsiveCase $case, ?string $note = null): HostUnresponsiveGuestAction
    {
        return $this->record($guest, $case, 'marked_feels_unsafe', [
            'guest_feels_unsafe' => true,
            'status' => 'guest_waiting',
        ], $note);
    }

    public function requestCancellation(User $guest, BookingHostUnresponsiveCase $case): HostUnresponsiveGuestAction
    {
        return $this->record($guest, $case, 'requested_cancellation', [
            'guest_wants_cancellation' => true,
            'guest_wants_refund' => true,
        ]);
    }

    public function requestRelocation(User $guest, BookingHostUnresponsiveCase $case): HostUnresponsiveGuestAction
    {
        return $this->record($guest, $case, 'requested_relocation', [
            'guest_wants_relocation' => true,
        ]);
    }

    public function continueWaiting(User $guest, BookingHostUnresponsiveCase $case): HostUnresponsiveGuestAction
    {
        $newWaitingUntil = now()->addMinutes($case->response_deadline_minutes);

        return $this->record($guest, $case, 'continued_waiting', [
            'response_deadline_at' => $newWaitingUntil,
        ], null, $newWaitingUntil);
    }

    public function openDispute(User $guest, BookingHostUnresponsiveCase $case): HostUnresponsiveGuestAction
    {
        $action = $this->record($guest, $case, 'opened_dispute', [
            'status' => 'dispute_opened',
            'decision_key' => 'dispute_opened',
            'future_support_review_required' => true,
        ]);

        app(HostUnresponsiveEventService::class)->record($case->fresh(), 'dispute_opened', ['user_id' => $guest->id]);

        return $action;
    }

    private function record(User $guest, BookingHostUnresponsiveCase $case, string $type, array $caseChanges = [], ?string $note = null, mixed $newWaitingUntil = null): HostUnresponsiveGuestAction
    {
        if ((int) $case->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('host_unresponsive.validation.not_your_booking'),
            ]);
        }

        $case->forceFill($caseChanges)->save();

        return $case->guestActions()->create([
            'booking_id' => $case->booking_id,
            'guest_user_id' => $guest->id,
            'action_type' => $type,
            'message' => $note,
            'guest_location_note' => $note,
            'new_waiting_until' => $newWaitingUntil,
        ]);
    }
}
