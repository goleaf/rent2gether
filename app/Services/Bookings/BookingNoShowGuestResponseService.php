<?php

namespace App\Services\Bookings;

use App\Enums\BookingStatus;
use App\Models\BookingNoShow;
use App\Models\BookingNoShowGuestResponse;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class BookingNoShowGuestResponseService
{
    public function __construct(
        private readonly BookingNoShowEventService $events,
        private readonly BookingNoShowNotificationService $notifications,
    ) {}

    public function markOnTheWay(User $guest, BookingNoShow $noShow, ?string $message = null): BookingNoShowGuestResponse
    {
        return $this->recordResponse($guest, $noShow, 'i_am_on_the_way', [
            'message' => $message,
            'status' => 'waiting_period_active',
            'waiting_until' => now()->addMinutes($noShow->waiting_period_minutes),
        ]);
    }

    public function markLate(User $guest, BookingNoShow $noShow, ?string $newArrivalTime = null, ?string $message = null): BookingNoShowGuestResponse
    {
        $waitingUntil = $newArrivalTime
            ? CarbonImmutable::parse($noShow->check_in_date?->toDateString().' '.$newArrivalTime)->addMinutes($noShow->waiting_period_minutes)
            : now()->addMinutes($noShow->waiting_period_minutes);

        return $this->recordResponse($guest, $noShow, 'i_am_late', [
            'message' => $message,
            'new_arrival_time' => $newArrivalTime,
            'status' => 'guest_responded_late_arrival',
            'guest_warned_late_arrival' => true,
            'waiting_until' => $waitingUntil,
        ]);
    }

    public function markArrived(User $guest, BookingNoShow $noShow): BookingNoShowGuestResponse
    {
        $response = $this->recordResponse($guest, $noShow, 'i_arrived', [
            'status' => 'guest_claims_arrived',
            'guest_claimed_arrived' => true,
        ]);

        $noShow->checkIn?->forceFill([
            'status' => 'guest_arrived',
            'guest_arrived_at' => now(),
            'actual_arrival_at' => now(),
        ])->save();

        return $response;
    }

    public function requestCancellation(User $guest, BookingNoShow $noShow, ?string $message = null): BookingNoShowGuestResponse
    {
        return $this->recordResponse($guest, $noShow, 'i_want_to_cancel', [
            'message' => $message,
            'status' => 'guest_responded_cancel',
            'guest_warned_cancellation' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reportCheckInProblem(User $guest, BookingNoShow $noShow, array $data): BookingNoShowGuestResponse
    {
        $response = $this->recordResponse($guest, $noShow, 'i_have_check_in_problem', [
            'message' => $data['message'] ?? null,
            'decision_key' => 'converted_to_check_in_problem',
            'status' => 'guest_claims_arrived',
            'guest_claimed_arrived' => true,
        ]);

        $noShow->checkIn?->forceFill([
            'has_problem' => true,
            'problem_status' => 'reported',
            'problem_reported_at' => now(),
            'problem_summary' => $data['message'] ?? null,
            'status' => 'problem_reported',
        ])->save();

        $this->events->record($noShow->fresh(), 'no_show_rejected', ['decision_key' => 'converted_to_check_in_problem']);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function reportHostNotAnswering(User $guest, BookingNoShow $noShow, array $data): BookingNoShowGuestResponse
    {
        $response = $this->recordResponse($guest, $noShow, 'host_not_answering', [
            'message' => $data['message'] ?? null,
            'status' => 'converted_to_host_unresponsive',
            'decision_key' => 'converted_to_host_unresponsive',
            'host_unresponsive_case_id' => $noShow->id,
            'future_support_review_required' => true,
        ]);

        $noShow->booking?->forceFill(['status' => BookingStatus::HostUnresponsive])->save();
        $noShow->checkIn?->forceFill([
            'has_problem' => true,
            'problem_status' => 'reported',
            'problem_reported_at' => now(),
            'problem_summary' => $data['message'] ?? null,
            'status' => 'host_unresponsive',
        ])->save();

        $this->events->record($noShow->fresh(), 'dispute_opened', ['decision_key' => 'converted_to_host_unresponsive']);

        return $response;
    }

    public function disputeNoShow(User $guest, BookingNoShow $noShow, string $message): BookingNoShowGuestResponse
    {
        $response = $this->recordResponse($guest, $noShow, 'dispute_no_show', [
            'message' => $message,
            'status' => 'dispute_opened',
            'decision_key' => 'dispute_opened',
            'future_support_review_required' => true,
            'future_support_comment' => $message,
        ]);

        $noShow->booking?->forceFill([
            'status' => BookingStatus::DisputeOpened,
            'has_dispute' => true,
        ])->save();

        $this->events->record($noShow->fresh(), 'dispute_opened', ['message' => $message]);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function recordResponse(User $guest, BookingNoShow $noShow, string $responseType, array $changes): BookingNoShowGuestResponse
    {
        if ((int) $noShow->guest_user_id !== (int) $guest->id) {
            throw ValidationException::withMessages([
                'booking' => __('no_show.validation.not_your_booking'),
            ]);
        }

        $response = $noShow->guestResponses()->create([
            'booking_id' => $noShow->booking_id,
            'guest_user_id' => $guest->id,
            'response_type' => $responseType,
            'message' => $changes['message'] ?? null,
            'new_arrival_time' => $changes['new_arrival_time'] ?? null,
        ]);

        $noShow->forceFill([
            'guest_response_type' => $responseType,
            'guest_response_message' => $changes['message'] ?? null,
            'guest_last_response_at' => now(),
            ...collect($changes)->except(['message', 'new_arrival_time'])->all(),
        ])->save();

        $this->events->record($noShow->fresh(), $responseType === 'i_arrived' ? 'guest_claimed_arrived' : 'guest_responded_late', [
            'response_type' => $responseType,
            'user_id' => $guest->id,
        ]);
        $this->notifications->notifyHostGuestResponded($noShow->fresh());

        return $response->fresh();
    }
}
