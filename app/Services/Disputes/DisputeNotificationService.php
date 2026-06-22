<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\User;
use App\Services\Notifications\NotificationService;

class DisputeNotificationService
{
    public function __construct(
        private readonly NotificationService $notifications,
    ) {}

    public function notifyDisputeOpened(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_opened', 'opened');
    }

    public function notifyEvidenceRequested(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_evidence_requested', 'evidence_requested');
    }

    public function notifyEvidenceSubmitted(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_evidence_submitted', 'evidence_submitted');
    }

    public function notifyProposalCreated(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_proposal_created', 'proposal_created');
    }

    public function notifyProposalAccepted(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_proposal_accepted', 'proposal_accepted');
    }

    public function notifyProposalRejected(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_proposal_rejected', 'proposal_rejected');
    }

    public function notifyDisputeResolved(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_resolved', 'resolved');
    }

    public function notifyDisputeClosed(DisputeCase $dispute): void
    {
        $this->notifyBoth($dispute, 'dispute_closed', 'closed');
    }

    private function notifyBoth(DisputeCase $dispute, string $type, string $key): void
    {
        $dispute->loadMissing('guest', 'host');

        foreach ([$dispute->guest, $dispute->host] as $user) {
            if ($user instanceof User) {
                $this->notifications->create(
                    user: $user,
                    type: $type,
                    data: ['dispute_case_id' => $dispute->id, 'booking_id' => $dispute->booking_id],
                    titleKey: 'disputes.notifications.'.$key.'.title',
                    bodyKey: 'disputes.notifications.'.$key.'.body',
                );
            }
        }
    }
}
