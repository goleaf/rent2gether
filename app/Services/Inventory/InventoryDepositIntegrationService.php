<?php

namespace App\Services\Inventory;

use App\Models\BookingCheckOut;
use App\Models\BookingDepositDecision;
use App\Models\InventoryIssue;

class InventoryDepositIntegrationService
{
    public function createDepositDeductionCandidate(InventoryIssue $issue): ?BookingDepositDecision
    {
        if (! $issue->booking_id || ! in_array($issue->issue_type, ['lost', 'missing', 'damaged', 'broken', 'not_returned'], true)) {
            return null;
        }

        $checkOut = $issue->booking_check_out_id
            ? BookingCheckOut::query()->find($issue->booking_check_out_id)
            : BookingCheckOut::query()->where('booking_id', $issue->booking_id)->latest('id')->first();

        if (! $checkOut || ! $issue->booking) {
            return null;
        }

        $deductionAmount = $issue->deduction_suggested_amount
            ?? $issue->inventoryItem->deposit_deduction_default_amount
            ?? $issue->replacement_cost_amount
            ?? 0;

        $candidate = BookingDepositDecision::query()->create([
            'booking_check_out_id' => $checkOut->id,
            'booking_id' => $issue->booking_id,
            'guest_user_id' => $issue->guest_user_id ?? $issue->booking->guest_user_id,
            'host_user_id' => $issue->host_user_id,
            'deposit_amount' => $issue->booking->deposit_amount,
            'currency' => $issue->currency ?? $issue->booking->currency,
            'decision' => 'deduct_partial',
            'deduction_amount' => $deductionAmount,
            'return_amount' => max(0, (float) $issue->booking->deposit_amount - (float) $deductionAmount),
            'reason' => $issue->description,
            'status' => 'pending_review',
        ]);

        $issue->forceFill([
            'booking_deposit_deduction_id' => $candidate->id,
            'status' => 'deposit_candidate_created',
        ])->save();

        app(InventoryEventService::class)->recordForIssue($issue->refresh(), 'deposit_deduction_candidate_created', ['source_id' => $candidate->id]);

        return $candidate->refresh();
    }

    public function attachInventoryEvidenceToDeposit(InventoryIssue $issue): void
    {
        app(InventoryEventService::class)->recordForIssue($issue, 'inventory_evidence_attached_to_deposit');
    }

    public function syncGuestResponsibilityFromDepositDecision(InventoryIssue $issue): void
    {
        if (! $issue->booking_deposit_deduction_id) {
            return;
        }

        $decision = BookingDepositDecision::query()->find($issue->booking_deposit_deduction_id);

        if (! $decision) {
            return;
        }

        $issue->forceFill([
            'guest_responsibility_status' => $decision->decision === 'deduct_partial' ? 'possibly_guest_fault' : 'rejected_guest_fault',
        ])->save();
    }
}
