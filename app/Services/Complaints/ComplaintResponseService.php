<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\ComplaintResponse;
use App\Models\DisputeCase;
use App\Models\User;
use App\Services\Disputes\DisputeCaseService;
use Illuminate\Auth\Access\AuthorizationException;

class ComplaintResponseService
{
    public function __construct(
        private readonly ComplaintPrivacyService $privacy,
        private readonly ComplaintPartyService $parties,
        private readonly ComplaintStatusService $statuses,
        private readonly ComplaintEventService $events,
        private readonly ComplaintNotificationService $notifications,
        private readonly ComplaintResolutionService $resolutions,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function respond(User $user, ComplaintCase $case, array $data): ComplaintResponse
    {
        if (! $this->privacy->canRespond($user, $case)) {
            throw new AuthorizationException(__('complaints.validation.cannot_respond'));
        }

        $response = ComplaintResponse::query()->create([
            'complaint_case_id' => $case->id,
            'user_id' => $user->id,
            'response_type' => $data['response_type'] ?? 'send_message',
            'message' => $data['message'] ?? null,
            'accepts_problem' => $data['accepts_problem'] ?? null,
            'denies_problem' => $data['denies_problem'] ?? null,
            'offered_resolution_type' => $data['offered_resolution_type'] ?? null,
            'offered_amount' => $data['offered_amount'] ?? null,
            'currency' => $data['currency'] ?? $case->currency,
            'requires_guest_response' => (bool) ($data['requires_guest_response'] ?? false),
            'requires_host_response' => (bool) ($data['requires_host_response'] ?? false),
        ]);

        $case->forceFill([
            'other_party_responded_at' => now(),
        ])->save();

        $case->parties()->where('user_id', $user->id)->each(fn ($party) => $this->parties->markResponded($party));
        $this->statuses->transition($case->fresh(), 'other_party_responded', $user);
        $this->events->record($case->fresh(), 'other_party_responded', ['user_id' => $user->id, 'response_id' => $response->id]);
        $this->notifications->notifyResponseReceived($case->fresh());

        return $response->fresh();
    }

    public function acceptProblem(User $user, ComplaintCase $case, ?string $message = null): ComplaintResponse
    {
        return $this->respond($user, $case, [
            'response_type' => 'accept',
            'message' => $message,
            'accepts_problem' => true,
            'denies_problem' => false,
        ]);
    }

    public function denyProblem(User $user, ComplaintCase $case, string $message): ComplaintResponse
    {
        return $this->respond($user, $case, [
            'response_type' => 'deny',
            'message' => $message,
            'accepts_problem' => false,
            'denies_problem' => true,
        ]);
    }

    public function askForEvidence(User $user, ComplaintCase $case, string $message): ComplaintResponse
    {
        $response = $this->respond($user, $case, [
            'response_type' => 'ask_for_evidence',
            'message' => $message,
            'requires_guest_response' => true,
        ]);
        $this->statuses->transition($case->fresh(), 'evidence_requested', $user, ['note' => $message]);
        $this->events->record($case->fresh(), 'evidence_requested', ['user_id' => $user->id, 'response_id' => $response->id]);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function offerResolution(User $user, ComplaintCase $case, string $resolutionType, array $data): ComplaintResponse
    {
        $responseType = match ($resolutionType) {
            'partial_refund', 'full_refund' => 'offer_refund',
            'compensation' => 'offer_compensation',
            'relocation' => 'offer_relocation',
            'cancellation' => 'offer_cancellation',
            'repair' => 'offer_repair',
            'cleaning' => 'offer_cleaning',
            default => 'offer_solution',
        };

        $response = $this->respond($user, $case, [
            'response_type' => $responseType,
            'message' => $data['message'] ?? $data['description'] ?? null,
            'offered_resolution_type' => $resolutionType,
            'offered_amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? $case->currency,
            'requires_guest_response' => true,
        ]);

        $this->resolutions->createResolutionOption($case->fresh(), $resolutionType, [
            ...$data,
            'offered_by_user_id' => $user->id,
        ]);

        return $response->fresh();
    }

    public function openDispute(User $user, ComplaintCase $case, string $reason): DisputeCase
    {
        $response = $this->respond($user, $case, [
            'response_type' => 'open_dispute',
            'message' => $reason,
        ]);

        return app(DisputeCaseService::class)->openFromComplaint($user, $case->fresh(), [
            'description' => $reason,
            'source_type' => 'complaint_response',
            'source_id' => $response->id,
        ]);
    }
}
