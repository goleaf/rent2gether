<?php

namespace App\Services\BookingRequests;

use App\Models\BookingRequest;
use App\Models\BookingRequestHostResponse;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingRequestHostResponseService
{
    public function __construct(
        private readonly BookingRequestPrivacyService $privacy,
        private readonly BookingRequestAvailabilityHoldService $holds,
        private readonly BookingRequestNotificationService $notifications,
    ) {}

    public function approve(User $host, BookingRequest $request, ?string $message = null): BookingRequestHostResponse
    {
        $this->assertHostCanRespond($host, $request);
        $response = $this->response($request, $host, BookingRequestHostResponse::TYPE_APPROVE, ['message' => $message]);
        $this->transition($request, $host, BookingRequest::STATUS_APPROVED, [
            'approved_at' => now(),
            'host_response' => $message,
        ]);
        $this->notifications->notifyGuestRequestApproved($request->fresh(['guest', 'host']));

        return $response;
    }

    public function reject(User $host, BookingRequest $request, string $reason): BookingRequestHostResponse
    {
        $this->assertHostCanRespond($host, $request);
        $response = $this->response($request, $host, BookingRequestHostResponse::TYPE_REJECT, [
            'rejection_reason' => $reason,
        ]);
        $this->transition($request, $host, BookingRequest::STATUS_REJECTED, [
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
        $this->holds->releaseHold($request, 'rejected');
        $this->notifications->notifyGuestRequestRejected($request->fresh(['guest', 'host']));

        return $response;
    }

    public function askQuestion(User $host, BookingRequest $request, string $message): BookingRequestHostResponse
    {
        $this->assertHostCanRespond($host, $request);
        $response = $this->response($request, $host, BookingRequestHostResponse::TYPE_ASK_QUESTION, [
            'message' => $message,
        ]);
        $this->transition($request, $host, BookingRequest::STATUS_WAITING_GUEST_RESPONSE, [
            'host_response' => $message,
        ]);
        $this->notifications->notifyGuestQuestionAsked($response);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function proposeTimeChange(User $host, BookingRequest $request, array $data): BookingRequestHostResponse
    {
        $this->assertHostCanRespond($host, $request);
        $response = $this->response($request, $host, BookingRequestHostResponse::TYPE_PROPOSE_TIME_CHANGE, [
            'message' => $data['message'] ?? null,
            'proposed_check_in_time' => $data['proposed_check_in_time'] ?? null,
            'proposed_check_out_time' => $data['proposed_check_out_time'] ?? null,
        ]);
        $this->transition($request, $host, BookingRequest::STATUS_WAITING_GUEST_RESPONSE);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function proposeDateChange(User $host, BookingRequest $request, array $data): BookingRequestHostResponse
    {
        $this->assertHostCanRespond($host, $request);
        $response = $this->response($request, $host, BookingRequestHostResponse::TYPE_PROPOSE_DATE_CHANGE, [
            'message' => $data['message'] ?? null,
            'proposed_check_in_date' => $data['proposed_check_in_date'] ?? null,
            'proposed_check_out_date' => $data['proposed_check_out_date'] ?? null,
        ]);
        $this->transition($request, $host, BookingRequest::STATUS_WAITING_GUEST_RESPONSE);

        return $response;
    }

    public function offerAlternativePlace(User $host, BookingRequest $request, SleepingPlace $place): BookingRequestHostResponse
    {
        $this->assertHostCanRespond($host, $request);

        if ((int) $place->user_id !== (int) $host->id) {
            throw ValidationException::withMessages([
                'alternative_sleeping_place_id' => __('booking_requests.validation.alternative_place_not_available'),
            ]);
        }

        $response = $this->response($request, $host, BookingRequestHostResponse::TYPE_OFFER_ALTERNATIVE_SLEEPING_PLACE, [
            'alternative_sleeping_place_id' => $place->id,
            'alternative_room_id' => $place->room_id,
        ]);
        $this->transition($request, $host, BookingRequest::STATUS_WAITING_GUEST_RESPONSE);

        return $response;
    }

    private function assertHostCanRespond(User $host, BookingRequest $request): void
    {
        if (! $this->privacy->canHostRespond($host, $request)) {
            throw ValidationException::withMessages([
                'booking_request' => __('booking_requests.validation.host_cannot_respond'),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function response(BookingRequest $request, User $host, string $type, array $data = []): BookingRequestHostResponse
    {
        return $request->hostResponses()->create([
            'host_user_id' => $host->id,
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
            'reason_key' => 'booking_requests.host_response',
        ]);
    }
}
