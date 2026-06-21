<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\BookingRequestGuestResponse;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingRequestGuestResponseService
{
    public function __construct(
        private readonly BookingRequestPrivacyService $privacy,
        private readonly BookingRequestAvailabilityHoldService $holds,
        private readonly BookingRequestNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function acceptProposal(User $guest, BookingRequest $request, array $data): BookingRequestGuestResponse
    {
        $this->assertGuestCanRespond($guest, $request);
        $response = $this->response($request, $guest, BookingRequestGuestResponse::TYPE_ACCEPT_PROPOSAL, [
            'message' => $data['message'] ?? null,
            'accepted_proposed_check_in_time' => $data['accepted_proposed_check_in_time'] ?? null,
            'accepted_proposed_check_out_time' => $data['accepted_proposed_check_out_time'] ?? null,
            'accepted_proposed_check_in_date' => $data['accepted_proposed_check_in_date'] ?? null,
            'accepted_proposed_check_out_date' => $data['accepted_proposed_check_out_date'] ?? null,
            'accepted_alternative_sleeping_place_id' => $data['accepted_alternative_sleeping_place_id'] ?? null,
        ]);
        $this->transition($request, $guest, BookingRequest::STATUS_WAITING_HOST_RESPONSE);
        $this->notifications->notifyHostGuestResponded($request->fresh(['guest', 'host']));

        return $response;
    }

    public function rejectProposal(User $guest, BookingRequest $request, string $message): BookingRequestGuestResponse
    {
        $this->assertGuestCanRespond($guest, $request);
        $response = $this->response($request, $guest, BookingRequestGuestResponse::TYPE_REJECT_PROPOSAL, [
            'message' => $message,
        ]);
        $this->transition($request, $guest, BookingRequest::STATUS_WAITING_HOST_RESPONSE);
        $this->notifications->notifyHostGuestResponded($request->fresh(['guest', 'host']));

        return $response;
    }

    public function answerQuestion(User $guest, BookingRequest $request, string $message): BookingRequestGuestResponse
    {
        $this->assertGuestCanRespond($guest, $request);
        $response = $this->response($request, $guest, BookingRequestGuestResponse::TYPE_ANSWER_QUESTION, [
            'message' => $message,
        ]);
        $this->transition($request, $guest, BookingRequest::STATUS_WAITING_HOST_RESPONSE);
        $this->notifications->notifyHostGuestResponded($request->fresh(['guest', 'host']));

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function changeRequest(User $guest, BookingRequest $request, array $data): BookingRequestGuestResponse
    {
        $this->assertGuestCanRespond($guest, $request);
        $response = $this->response($request, $guest, BookingRequestGuestResponse::TYPE_CHANGE_REQUEST, [
            'message' => $data['message'] ?? null,
        ]);
        $this->transition($request, $guest, BookingRequest::STATUS_WAITING_HOST_RESPONSE, [
            'guest_message' => $data['guest_message'] ?? $request->guest_message,
            'planned_arrival_time' => $data['planned_arrival_time'] ?? $request->planned_arrival_time,
        ]);
        $this->notifications->notifyHostGuestResponded($request->fresh(['guest', 'host']));

        return $response;
    }

    public function withdrawRequest(User $guest, BookingRequest $request, ?string $message = null): BookingRequestGuestResponse
    {
        if (! $this->privacy->canGuestView($guest, $request)) {
            throw ValidationException::withMessages([
                'booking_request' => __('booking_requests.validation.guest_cannot_respond'),
            ]);
        }

        $response = $this->response($request, $guest, BookingRequestGuestResponse::TYPE_WITHDRAW_REQUEST, [
            'message' => $message,
        ]);
        $this->transition($request, $guest, BookingRequest::STATUS_WITHDRAWN_BY_GUEST);
        $this->holds->releaseHold($request, 'withdrawn_by_guest');
        $this->notifications->notifyRequestWithdrawn($request->fresh(['guest', 'host']));

        return $response;
    }

    private function assertGuestCanRespond(User $guest, BookingRequest $request): void
    {
        if (! $this->privacy->canGuestRespond($guest, $request)) {
            throw ValidationException::withMessages([
                'booking_request' => __('booking_requests.validation.guest_cannot_respond'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function response(BookingRequest $request, User $guest, string $type, array $data = []): BookingRequestGuestResponse
    {
        return $request->guestResponses()->create([
            'guest_user_id' => $guest->id,
            'response_type' => $type,
            ...$data,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function transition(BookingRequest $request, User $user, string $newStatus, array $attributes = []): void
    {
        $oldStatus = $request->status;
        $request->forceFill([
            ...$attributes,
            'status' => $newStatus,
        ])->save();
        $request->statusLogs()->create([
            'user_id' => $user->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason_key' => 'booking_requests.guest_response',
        ]);
    }
}
