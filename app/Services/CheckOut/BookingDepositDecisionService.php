<?php

namespace App\Services\CheckOut;

use App\Models\BookingCheckOut;
use App\Models\BookingDepositDecision;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class BookingDepositDecisionService
{
    public function createPendingReview(BookingCheckOut $checkOut): BookingDepositDecision
    {
        return BookingDepositDecision::query()->firstOrCreate(
            ['booking_check_out_id' => $checkOut->id],
            [
                'booking_id' => $checkOut->booking_id,
                'guest_user_id' => $checkOut->guest_user_id,
                'host_user_id' => $checkOut->host_user_id,
                'deposit_amount' => $this->depositAmount($checkOut),
                'currency' => $checkOut->booking?->currency ?: 'EUR',
                'decision' => $this->depositAmount($checkOut) > 0 ? 'return_full' : 'no_deposit',
                'deduction_amount' => 0,
                'return_amount' => $this->depositAmount($checkOut),
                'status' => 'pending_review',
            ],
        );
    }

    public function returnFullDeposit(User $host, BookingCheckOut $checkOut): BookingDepositDecision
    {
        $this->authorizeHost($host, $checkOut);

        $amount = $this->depositAmount($checkOut);

        $decision = BookingDepositDecision::query()->updateOrCreate(
            ['booking_check_out_id' => $checkOut->id],
            [
                'booking_id' => $checkOut->booking_id,
                'guest_user_id' => $checkOut->guest_user_id,
                'host_user_id' => $checkOut->host_user_id,
                'deposit_amount' => $amount,
                'currency' => $checkOut->booking?->currency ?: 'EUR',
                'decision' => $amount > 0 ? 'return_full' : 'no_deposit',
                'deduction_amount' => 0,
                'return_amount' => $amount,
                'status' => $amount > 0 ? 'return_pending' : 'resolved',
                'decided_at' => now(),
            ],
        );

        $checkOut->forceFill(['status' => $amount > 0 ? 'deposit_return_pending' : $checkOut->status])->save();

        return $decision->refresh();
    }

    public function requestPartialDeduction(User $host, BookingCheckOut $checkOut, float|int|string $amount, string $reason): BookingDepositDecision
    {
        $this->authorizeHost($host, $checkOut);

        $deposit = $this->depositAmount($checkOut);
        $deduction = min((float) $amount, $deposit);

        $decision = BookingDepositDecision::query()->updateOrCreate(
            ['booking_check_out_id' => $checkOut->id],
            [
                'booking_id' => $checkOut->booking_id,
                'guest_user_id' => $checkOut->guest_user_id,
                'host_user_id' => $checkOut->host_user_id,
                'deposit_amount' => $deposit,
                'currency' => $checkOut->booking?->currency ?: 'EUR',
                'decision' => $deduction >= $deposit ? 'withhold_full' : 'return_partial',
                'deduction_amount' => $deduction,
                'return_amount' => max(0, $deposit - $deduction),
                'reason' => $reason,
                'status' => 'deduction_requested',
                'decided_at' => now(),
            ],
        );

        $checkOut->forceFill([
            'needs_deposit_deduction' => true,
            'deposit_deduction_amount' => $deduction,
            'deposit_deduction_reason' => $reason,
            'status' => 'deposit_deduction_requested',
        ])->save();

        return $decision->refresh();
    }

    public function withholdFullDeposit(User $host, BookingCheckOut $checkOut, string $reason): BookingDepositDecision
    {
        return $this->requestPartialDeduction($host, $checkOut, $this->depositAmount($checkOut), $reason);
    }

    public function guestDispute(User $guest, BookingDepositDecision $decision, string $comment): BookingDepositDecision
    {
        if ((int) $decision->guest_user_id !== (int) $guest->id) {
            throw new AuthorizationException(__('check_out.validation.not_your_booking'));
        }

        $decision->forceFill([
            'status' => 'guest_disputed',
            'guest_comment' => $comment,
            'guest_responded_at' => now(),
        ])->save();

        $decision->checkOut->forceFill(['status' => 'deposit_disputed'])->save();

        return $decision->refresh();
    }

    public function resolveDecision(BookingDepositDecision $decision): BookingDepositDecision
    {
        $decision->forceFill([
            'status' => 'resolved',
            'resolved_at' => now(),
        ])->save();

        return $decision->refresh();
    }

    public function startReturnIfNoProblems(BookingCheckOut $checkOut): BookingDepositDecision
    {
        $existing = $checkOut->depositDecision()->first();

        if ($existing) {
            return $existing;
        }

        if ($checkOut->issueReports()->whereNotIn('status', ['resolved', 'closed'])->exists()) {
            return $this->createPendingReview($checkOut);
        }

        return $this->returnFullDeposit($checkOut->host, $checkOut);
    }

    private function depositAmount(BookingCheckOut $checkOut): float
    {
        $checkOut->loadMissing('booking:id,deposit_amount,deposit,currency');

        return (float) ($checkOut->booking?->deposit_amount ?: $checkOut->booking?->deposit ?: 0);
    }

    private function authorizeHost(User $host, BookingCheckOut $checkOut): void
    {
        if ((int) $checkOut->host_user_id !== (int) $host->id) {
            throw new AuthorizationException(__('check_out.validation.not_host_booking'));
        }
    }
}
