<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Support\Money;

class BookingCheckOutDepositIntegrationService
{
    public function startDepositReview(BookingCheckOut $checkOut): mixed
    {
        $checkOut->forceFill([
            'deposit_review_required' => true,
        ])->save();

        app(BookingCheckOutEventService::class)->record($checkOut->refresh(), 'deposit_review_started');

        return app(BookingDepositDecisionService::class)->createPendingReview($checkOut->refresh());
    }

    public function startDepositReturnIfNoIssues(BookingCheckOut $checkOut): mixed
    {
        return app(BookingDepositDecisionService::class)->startReturnIfNoProblems($checkOut);
    }

    public function requestDepositDeduction(BookingCheckOut $checkOut, Money $amount, string $reason): mixed
    {
        app(BookingCheckOutEventService::class)->record($checkOut, 'deposit_deduction_requested', [
            'amount' => $amount->decimal(),
            'currency' => $amount->currency,
        ]);

        return app(BookingDepositDecisionService::class)->requestPartialDeduction(
            $checkOut->host,
            $checkOut,
            $amount->decimal(),
            $reason,
        );
    }

    public function syncDepositStatus(BookingCheckOut $checkOut): void
    {
        if ($checkOut->deposit_deduction_requested || $checkOut->needs_deposit_deduction) {
            $this->startDepositReview($checkOut);
        }
    }
}
