<?php

namespace App\Services\Bookings;

use App\Models\BookingListingMismatchGuestResponse;
use App\Models\BookingListingMismatchReport;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ListingMismatchGuestResponseService
{
    public function __construct(
        private readonly ListingMismatchPrivacyService $privacy,
        private readonly ListingMismatchStatusService $statuses,
        private readonly ListingMismatchEventService $events,
        private readonly ListingMismatchNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function acceptResolution(User $guest, BookingListingMismatchReport $report, array $data): BookingListingMismatchGuestResponse
    {
        $response = $this->createResponse($guest, $report, 'accept_resolution', $data);

        $report->forceFill([
            'resolution_type' => $data['accepted_resolution_type'] ?? $report->resolution_type,
            'resolution_status' => 'accepted',
        ])->save();

        $this->notifications->notifyHostGuestAcceptedResolution($report->fresh());

        return $response;
    }

    public function rejectResolution(User $guest, BookingListingMismatchReport $report, string $message): BookingListingMismatchGuestResponse
    {
        $response = $this->createResponse($guest, $report, 'reject_resolution', ['message' => $message]);
        $report->forceFill(['resolution_status' => 'rejected'])->save();
        $this->notifications->notifyHostGuestRejectedResolution($report->fresh());

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function provideMoreEvidence(User $guest, BookingListingMismatchReport $report, array $data): BookingListingMismatchGuestResponse
    {
        return $this->createResponse($guest, $report, 'provide_more_evidence', $data);
    }

    public function requestRelocation(User $guest, BookingListingMismatchReport $report): BookingListingMismatchGuestResponse
    {
        $report->forceFill(['guest_wants_relocation' => true])->save();

        return $this->createResponse($guest, $report, 'request_relocation', []);
    }

    public function requestCancellation(User $guest, BookingListingMismatchReport $report): BookingListingMismatchGuestResponse
    {
        $report->forceFill(['guest_wants_cancellation' => true])->save();

        return $this->createResponse($guest, $report, 'request_cancellation', []);
    }

    public function requestRefund(User $guest, BookingListingMismatchReport $report, float|int|string $amount): BookingListingMismatchGuestResponse
    {
        $report->forceFill([
            'guest_wants_refund' => true,
            'refund_amount' => $amount,
        ])->save();

        return $this->createResponse($guest, $report, 'request_refund', ['accepted_refund_amount' => $amount]);
    }

    public function requestCompensation(User $guest, BookingListingMismatchReport $report, float|int|string $amount): BookingListingMismatchGuestResponse
    {
        $report->forceFill([
            'guest_wants_compensation' => true,
            'compensation_amount' => $amount,
        ])->save();

        return $this->createResponse($guest, $report, 'request_compensation', ['accepted_compensation_amount' => $amount]);
    }

    public function openDispute(User $guest, BookingListingMismatchReport $report, string $message): BookingListingMismatchGuestResponse
    {
        $response = $this->createResponse($guest, $report, 'open_dispute', ['message' => $message]);
        $this->statuses->transition($report->fresh(), 'dispute_opened', $guest, ['note' => $message]);
        $this->events->record($report->fresh(), 'dispute_opened', ['user_id' => $guest->id]);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createResponse(User $guest, BookingListingMismatchReport $report, string $responseType, array $data): BookingListingMismatchGuestResponse
    {
        if (! $this->privacy->canGuestView($guest, $report)) {
            throw new AuthorizationException(__('listing_mismatch.validation.not_allowed'));
        }

        $response = $report->guestResponses()->create([
            'guest_user_id' => $guest->id,
            'response_type' => $responseType,
            'message' => $data['message'] ?? null,
            'accepted_resolution_type' => $data['accepted_resolution_type'] ?? null,
            'accepted_compensation_amount' => $data['accepted_compensation_amount'] ?? null,
            'accepted_refund_amount' => $data['accepted_refund_amount'] ?? null,
            'accepted_relocation_id' => $data['accepted_relocation_id'] ?? null,
        ]);

        $this->statuses->transition($report->fresh(), 'guest_response_required', $guest);
        $this->events->record($report->fresh(), 'guest_responded', ['user_id' => $guest->id, 'response_id' => $response->id]);

        return $response->fresh();
    }
}
