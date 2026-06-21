<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchHostResponse;
use App\Models\BookingListingMismatchReport;
use App\Models\SleepingPlace;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ListingMismatchHostResponseService
{
    public function __construct(
        private readonly ListingMismatchPrivacyService $privacy,
        private readonly ListingMismatchResolutionService $resolutions,
        private readonly ListingMismatchStatusService $statuses,
        private readonly ListingMismatchEventService $events,
        private readonly ListingMismatchNotificationService $notifications,
    ) {}

    public function accept(User $host, BookingListingMismatchReport $report, ?string $message = null): BookingListingMismatchHostResponse
    {
        $response = $this->createResponse($host, $report, 'accept', [
            'message' => $message,
            'accepts_problem' => true,
        ]);

        $report->forceFill(['host_accepts_problem' => true, 'host_denied_problem' => false])->save();

        return $response;
    }

    public function deny(User $host, BookingListingMismatchReport $report, string $message): BookingListingMismatchHostResponse
    {
        $response = $this->createResponse($host, $report, 'deny', [
            'message' => $message,
            'accepts_problem' => false,
        ]);

        $report->forceFill(['host_accepts_problem' => false, 'host_denied_problem' => true])->save();

        return $response;
    }

    public function askForMoreEvidence(User $host, BookingListingMismatchReport $report, string $message): BookingListingMismatchHostResponse
    {
        $response = $this->createResponse($host, $report, 'ask_for_more_evidence', ['message' => $message]);
        $this->statuses->transition($report->fresh(), 'evidence_requested', $host);
        $this->events->record($report->fresh(), 'evidence_requested', ['user_id' => $host->id]);

        return $response;
    }

    public function offerFix(User $host, BookingListingMismatchReport $report, string $message): BookingListingMismatchHostResponse
    {
        return $this->offer($host, $report, 'offer_fix', 'fix_problem', ['message' => $message]);
    }

    public function offerCleaning(User $host, BookingListingMismatchReport $report, string $message): BookingListingMismatchHostResponse
    {
        return $this->offer($host, $report, 'offer_cleaning', 'cleaning', ['message' => $message]);
    }

    public function offerRepair(User $host, BookingListingMismatchReport $report, string $message): BookingListingMismatchHostResponse
    {
        return $this->offer($host, $report, 'offer_repair', 'repair', ['message' => $message]);
    }

    public function offerRelocation(User $host, BookingListingMismatchReport $report, SleepingPlace $place): BookingListingMismatchHostResponse
    {
        return $this->offer($host, $report, 'offer_relocation', 'relocation', [
            'alternative_sleeping_place_id' => $place->id,
            'sleeping_place_id' => $place->id,
            'message' => null,
        ]);
    }

    public function offerRefund(User $host, BookingListingMismatchReport $report, float|int|string $amount): BookingListingMismatchHostResponse
    {
        return $this->offer($host, $report, 'offer_refund', 'partial_refund', [
            'offered_refund_amount' => $amount,
            'amount' => $amount,
            'message' => null,
        ]);
    }

    public function offerCompensation(User $host, BookingListingMismatchReport $report, float|int|string $amount): BookingListingMismatchHostResponse
    {
        return $this->offer($host, $report, 'offer_compensation', 'compensation', [
            'offered_compensation_amount' => $amount,
            'amount' => $amount,
            'message' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function offer(User $host, BookingListingMismatchReport $report, string $responseType, string $resolutionType, array $data): BookingListingMismatchHostResponse
    {
        $response = $this->createResponse($host, $report, $responseType, [
            'message' => $data['message'] ?? null,
            'accepts_problem' => true,
            'proposed_resolution_type' => $resolutionType,
            'offered_compensation_amount' => $data['offered_compensation_amount'] ?? null,
            'offered_refund_amount' => $data['offered_refund_amount'] ?? null,
            'alternative_sleeping_place_id' => $data['alternative_sleeping_place_id'] ?? null,
        ]);

        $this->resolutions->createResolutionOption($report->fresh(), $resolutionType, [
            'description' => $data['message'] ?? null,
            'amount' => $data['amount'] ?? null,
            'currency' => $report->currency,
            'sleeping_place_id' => $data['sleeping_place_id'] ?? null,
            'offered_by_user_id' => $host->id,
        ]);

        $report->forceFill([
            'host_accepts_problem' => true,
            'host_offered_fix' => $report->host_offered_fix || in_array($resolutionType, ['fix_problem', 'cleaning', 'repair'], true),
            'host_offered_relocation' => $report->host_offered_relocation || $resolutionType === 'relocation',
            'host_offered_refund' => $report->host_offered_refund || str_contains($resolutionType, 'refund'),
            'host_offered_compensation' => $report->host_offered_compensation || $resolutionType === 'compensation',
        ])->save();
        $this->statuses->transition($report->fresh(), 'resolution_offered', $host);
        $this->notifications->notifyGuestResolutionOffered($report->fresh());

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createResponse(User $host, BookingListingMismatchReport $report, string $responseType, array $data): BookingListingMismatchHostResponse
    {
        if (! $this->privacy->canHostRespond($host, $report)) {
            throw new AuthorizationException(__('listing_mismatch.validation.not_allowed'));
        }

        $response = $report->hostResponses()->create([
            'host_user_id' => $host->id,
            'response_type' => $responseType,
            'message' => $data['message'] ?? null,
            'accepts_problem' => $data['accepts_problem'] ?? null,
            'proposed_resolution_type' => $data['proposed_resolution_type'] ?? null,
            'offered_compensation_amount' => $data['offered_compensation_amount'] ?? null,
            'offered_refund_amount' => $data['offered_refund_amount'] ?? null,
            'currency' => $report->currency,
            'alternative_sleeping_place_id' => $data['alternative_sleeping_place_id'] ?? null,
            'maintenance_request_id' => $data['maintenance_request_id'] ?? null,
            'cleaning_task_id' => $data['cleaning_task_id'] ?? null,
        ]);

        $report->forceFill(['host_response' => $data['message'] ?? $report->host_response])->save();
        $this->statuses->transition($report->fresh(), 'host_responded', $host);
        $this->events->record($report->fresh(), 'host_responded', ['user_id' => $host->id, 'response_id' => $response->id]);
        $this->notifications->notifyGuestHostResponded($report->fresh());

        return $response->fresh();
    }
}
