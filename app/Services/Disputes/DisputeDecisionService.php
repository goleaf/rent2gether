<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;
use App\Models\DisputeDecision;
use App\Models\DisputeResolutionProposal;

class DisputeDecisionService
{
    public function __construct(
        private readonly DisputeStatusService $statuses,
        private readonly DisputeEventService $events,
        private readonly DisputeNotificationService $notifications,
    ) {}

    public function recordMutualAgreement(DisputeCase $dispute, DisputeResolutionProposal $proposal): DisputeDecision
    {
        $decision = DisputeDecision::query()->create([
            'dispute_case_id' => $dispute->id,
            'decision_type' => 'mutual_agreement',
            'resolution_type' => $proposal->resolution_type,
            'amount_to_guest' => $proposal->amount ?: 0,
            'currency' => $proposal->currency ?: $dispute->currency,
            'reason_summary' => $proposal->description,
            'decided_by_type' => 'mutual_agreement',
            'decided_at' => now(),
            'status' => 'recorded',
        ]);

        $this->events->record($dispute, 'mutual_agreement_reached', ['proposal_id' => $proposal->id, 'decision_id' => $decision->id]);

        return $decision->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordSystemRuleDecision(DisputeCase $dispute, array $data): DisputeDecision
    {
        return $this->recordDecision($dispute, 'system_rule', $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordFutureDecision(DisputeCase $dispute, array $data): DisputeDecision
    {
        $dispute->forceFill([
            'future_decision_required' => true,
            'future_decision_comment' => $data['decision_note'] ?? $data['reason_summary'] ?? null,
            'future_decided_at' => now(),
        ])->save();

        return $this->recordDecision($dispute->fresh(), 'future_reviewer', $data);
    }

    public function applyDecision(DisputeDecision $decision): void
    {
        $decision->loadMissing('disputeCase');
        $decision->forceFill(['status' => 'applied'])->save();
        $decision->disputeCase->forceFill([
            'final_resolution_type' => $decision->resolution_type,
            'final_resolution_note' => $decision->decision_note ?: $decision->reason_summary,
        ])->save();

        $dispute = $this->statuses->transition($decision->disputeCase->fresh(), 'resolved');
        $this->events->record($dispute, 'decision_recorded_future', ['decision_id' => $decision->id, 'decision_type' => $decision->decision_type]);
        $this->events->record($dispute, 'dispute_resolved', ['decision_id' => $decision->id]);
        $this->notifications->notifyDisputeResolved($dispute);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function recordDecision(DisputeCase $dispute, string $decidedByType, array $data): DisputeDecision
    {
        return DisputeDecision::query()->create([
            'dispute_case_id' => $dispute->id,
            'decision_type' => $data['decision_type'] ?? $decidedByType,
            'resolution_type' => $data['resolution_type'] ?? 'no_action',
            'amount_to_guest' => $data['amount_to_guest'] ?? 0,
            'amount_to_host' => $data['amount_to_host'] ?? 0,
            'deposit_return_amount' => $data['deposit_return_amount'] ?? 0,
            'deposit_deduction_amount' => $data['deposit_deduction_amount'] ?? 0,
            'host_payout_adjustment_amount' => $data['host_payout_adjustment_amount'] ?? 0,
            'currency' => $data['currency'] ?? $dispute->currency,
            'reason_summary' => $data['reason_summary'] ?? null,
            'decision_note' => $data['decision_note'] ?? null,
            'decided_by_type' => $decidedByType,
            'decided_by_user_id' => $data['decided_by_user_id'] ?? null,
            'decided_at' => now(),
            'status' => 'recorded',
        ]);
    }
}
