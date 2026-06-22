<?php

namespace App\Services\Disputes;

use App\Models\DisputeCase;

class DisputeFreezeService
{
    public function __construct(
        private readonly DisputeStatusService $statuses,
        private readonly DisputeEventService $events,
    ) {}

    public function freezeBookingIfNeeded(DisputeCase $dispute): void
    {
        if (! $dispute->booking_id) {
            return;
        }

        $dispute->forceFill(['booking_frozen' => true])->save();
        $this->statuses->transition($dispute->fresh(), 'booking_frozen');
        $this->events->record($dispute->fresh(), 'booking_frozen');
    }

    public function freezeRefundIfNeeded(DisputeCase $dispute): void
    {
        if (! $dispute->booking_refund_id && $dispute->dispute_type !== 'refund_dispute') {
            return;
        }

        $dispute->forceFill(['refund_frozen' => true])->save();
        $this->events->record($dispute->fresh(), 'refund_frozen');
    }

    public function freezeDepositIfNeeded(DisputeCase $dispute): void
    {
        if (! $dispute->deposit_case_id && $dispute->dispute_type !== 'deposit_dispute') {
            return;
        }

        $dispute->forceFill(['deposit_frozen' => true])->save();
        $this->events->record($dispute->fresh(), 'deposit_frozen');
    }

    public function freezeHostPayoutIfNeeded(DisputeCase $dispute): void
    {
        if (! in_array($dispute->dispute_type, ['refund_dispute', 'deposit_dispute', 'damage_dispute'], true)) {
            return;
        }

        $dispute->forceFill(['host_payout_frozen' => true])->save();
        $this->events->record($dispute->fresh(), 'host_payout_frozen');
    }

    public function freezeRatingImpactIfNeeded(DisputeCase $dispute): void
    {
        $dispute->forceFill(['rating_impact_frozen' => true])->save();
        $this->events->record($dispute->fresh(), 'rating_impact_frozen');
    }

    public function releaseFreezesAfterResolution(DisputeCase $dispute): void
    {
        $dispute->forceFill([
            'booking_frozen' => false,
            'refund_frozen' => false,
            'deposit_frozen' => false,
            'host_payout_frozen' => false,
            'rating_impact_frozen' => false,
        ])->save();

        $this->events->record($dispute->fresh(), 'dispute_resolved', ['freezes_released' => true]);
    }
}
