<?php

namespace App\Services\Complaints;

use App\Models\ComplaintCase;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class ComplaintNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function notifyComplaintSubmitted(ComplaintCase $case): void
    {
        $this->notifyOtherParty($case);
    }

    public function notifyOtherParty(ComplaintCase $case): void
    {
        $case->loadMissing('reporter', 'against', 'guest', 'host');
        $recipient = $case->submitted_by_type === 'guest' ? $case->host : $case->guest;
        $recipient ??= $case->against;

        if ($recipient instanceof User) {
            $this->notifications->create(
                user: $recipient,
                type: 'complaint_case_submitted',
                data: ['complaint_case_id' => $case->id, 'booking_id' => $case->booking_id],
                titleKey: 'complaints.notifications.submitted.title',
                bodyKey: 'complaints.notifications.submitted.body',
            );
        }
    }

    public function notifyEvidenceRequested(ComplaintCase $case): void
    {
        $this->notifyReporter($case, 'complaint_evidence_requested', 'evidence_requested');
    }

    public function notifyResponseReceived(ComplaintCase $case): void
    {
        $this->notifyReporter($case, 'complaint_response_received', 'response_received');
    }

    public function notifyResolutionOffered(ComplaintCase $case): void
    {
        $this->notifyReporter($case, 'complaint_resolution_offered', 'resolution_offered');
    }

    public function notifyResolutionAccepted(ComplaintCase $case): void
    {
        $this->notifyOtherParty($case);
    }

    public function notifyResolutionRejected(ComplaintCase $case): void
    {
        $this->notifyOtherParty($case);
    }

    public function notifyDisputeOpened(ComplaintCase $case): void
    {
        $this->notifyOtherParty($case);
    }

    public function notifyComplaintClosed(ComplaintCase $case): void
    {
        $this->notifyReporter($case, 'complaint_closed', 'closed');
        $this->notifyOtherParty($case);
    }

    private function notifyReporter(ComplaintCase $case, string $type, string $key): void
    {
        $case->loadMissing('reporter');

        if ($case->reporter instanceof User) {
            $this->notifications->create(
                user: $case->reporter,
                type: $type,
                data: ['complaint_case_id' => $case->id, 'booking_id' => $case->booking_id],
                titleKey: 'complaints.notifications.'.$key.'.title',
                bodyKey: 'complaints.notifications.'.$key.'.body',
            );
        }
    }
}
