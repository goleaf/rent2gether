<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\DisputeResolutionProposal;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class DisputeProposalService
{
    public function __construct(
        private readonly DisputePrivacyService $privacy,
        private readonly DisputeStatusService $statuses,
        private readonly DisputeEventService $events,
        private readonly DisputeNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createProposal(User $user, DisputeCase $dispute, array $data): DisputeResolutionProposal
    {
        if (! $this->privacy->canMessage($user, $dispute)) {
            throw new AuthorizationException(__('disputes.validation.cannot_message'));
        }

        $proposal = DisputeResolutionProposal::query()->create([
            'dispute_case_id' => $dispute->id,
            'proposed_by_user_id' => $user->id,
            'resolution_type' => $data['resolution_type'] ?? 'no_action',
            'amount' => $data['amount'] ?? null,
            'currency' => $data['currency'] ?? $dispute->currency,
            'description' => $data['description'] ?? null,
            'status' => 'offered',
        ]);

        $dispute->forceFill(['proposed_resolution_type' => $proposal->resolution_type])->save();
        $this->statuses->transition($dispute->fresh(), 'resolution_proposed', $user);
        $this->events->record($dispute->fresh(), 'proposal_created', ['proposal_id' => $proposal->id, 'user_id' => $user->id]);
        $this->notifications->notifyProposalCreated($dispute->fresh());

        return $proposal->fresh();
    }

    public function acceptProposal(User $user, DisputeResolutionProposal $proposal): DisputeResolutionProposal
    {
        $proposal->loadMissing('disputeCase');
        $dispute = $proposal->disputeCase;

        if (! $this->privacy->canMessage($user, $dispute)) {
            throw new AuthorizationException(__('disputes.validation.cannot_message'));
        }

        $attributes = [];

        if ((int) $dispute->guest_user_id === (int) $user->id) {
            $attributes['guest_accepts'] = true;
            $attributes['guest_accepted_at'] = now();
        }

        if ((int) $dispute->host_user_id === (int) $user->id) {
            $attributes['host_accepts'] = true;
            $attributes['host_accepted_at'] = now();
        }

        $proposal->forceFill($attributes)->save();
        $status = $proposal->fresh()->guest_accepts && $proposal->fresh()->host_accepts
            ? 'accepted_by_both'
            : ((int) $dispute->guest_user_id === (int) $user->id ? 'accepted_by_guest' : 'accepted_by_host');

        $proposal->forceFill(['status' => $status])->save();
        $this->events->record($dispute->fresh(), 'proposal_accepted', ['proposal_id' => $proposal->id, 'user_id' => $user->id]);

        if ($status === 'accepted_by_both') {
            $this->events->record($dispute->fresh(), 'mutual_agreement_reached', ['proposal_id' => $proposal->id]);
        }

        $this->notifications->notifyProposalAccepted($dispute->fresh());

        return $proposal->fresh();
    }

    public function rejectProposal(User $user, DisputeResolutionProposal $proposal, ?string $reason = null): DisputeResolutionProposal
    {
        $proposal->loadMissing('disputeCase');
        $dispute = $proposal->disputeCase;

        if (! $this->privacy->canMessage($user, $dispute)) {
            throw new AuthorizationException(__('disputes.validation.cannot_message'));
        }

        $proposal->forceFill(['status' => 'rejected'])->save();
        $this->statuses->transition($dispute->fresh(), 'resolution_rejected', $user, ['note' => $reason]);
        $this->events->record($dispute->fresh(), 'proposal_rejected', ['proposal_id' => $proposal->id, 'user_id' => $user->id, 'reason' => $reason]);
        $this->notifications->notifyProposalRejected($dispute->fresh());

        return $proposal->fresh();
    }

    public function applyProposal(DisputeResolutionProposal $proposal): void
    {
        $proposal->forceFill(['status' => 'applied'])->save();
        $this->statuses->transition($proposal->disputeCase->fresh(), 'resolved');
    }
}
