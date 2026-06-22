<?php

namespace App\Services\Notifications;

class DepositNotificationIntegrationService
{
    use NotificationIntegrationSupport;

    public function notifyDepositReviewStarted(mixed $case): void
    {
        $this->notifyGuest($this->bookingFrom($case), 'deposit_review_started');
    }

    public function notifyDepositDeductionRequested(mixed $deduction): void
    {
        $this->notifyGuest($this->bookingFrom($deduction), 'deposit_deduction_requested', ['priority' => 'high']);
    }

    public function notifyGuestRejectedDeduction(mixed $deduction): void
    {
        $this->notifyHost($this->bookingFrom($deduction), 'deposit_deduction_requested', ['priority' => 'high']);
    }

    public function notifyDepositReturned(mixed $record): void
    {
        $this->notifyGuest($this->bookingFrom($record), 'deposit_returned');
    }
}
