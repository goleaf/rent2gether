<?php

namespace App\Services\Messaging;

use App\Models\BookingDepositDecision;

class ConversationDepositIntegrationService
{
    public function addDepositReviewStartedEvent(BookingDepositDecision $case): void
    {
        $this->add($case, 'deposit_review_started');
    }

    public function addDepositDeductionRequestedEvent(BookingDepositDecision $deduction): void
    {
        $this->add($deduction, 'deposit_deduction_requested', 'important');
    }

    public function addDepositReturnedEvent(BookingDepositDecision $record): void
    {
        $this->add($record, 'deposit_returned');
    }

    private function add(BookingDepositDecision $deposit, string $eventKey, string $importance = 'normal'): void
    {
        app(ConversationSystemEventService::class)->addDepositEvent($deposit, $eventKey, [
            'importance_level' => $importance,
        ]);
    }
}
