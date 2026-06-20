<?php

namespace App\Services;

use App\Data\RefundEstimate;
use App\Enums\CancellationPolicy;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class RefundCalculator
{
    public function calculate(Booking $booking, string $cancelledBy = 'guest', CarbonInterface|string|null $now = null): RefundEstimate
    {
        $policy = $booking->cancellation_policy instanceof CancellationPolicy
            ? $booking->cancellation_policy
            : CancellationPolicy::tryFrom((string) $booking->cancellation_policy) ?? CancellationPolicy::Flexible;

        $currency = strtoupper((string) ($booking->currency ?: 'EUR'));
        $paidAmount = $this->paidAmount($booking);
        $subtotal = $this->money($this->firstMoney($booking->subtotal_amount, $booking->subtotal));
        $discount = $this->money($booking->discount_amount);
        $stayAmount = $this->money(max(0.0, $subtotal - $discount));
        $cleaningFee = $this->money($this->firstMoney($booking->cleaning_fee_amount, $booking->cleaning_fee));
        $deposit = $this->money($this->firstMoney($booking->deposit_amount, $booking->deposit));
        $serviceFee = $this->money($this->firstMoney($booking->service_fee_amount, $booking->service_fee));
        $clock = $this->clock($now);
        $checkIn = $this->checkInAt($booking);
        $beforeCheckIn = $clock->lessThan($checkIn);

        if ($cancelledBy === 'host') {
            return $this->estimate(
                policy: $policy,
                currency: $currency,
                paidAmount: $paidAmount,
                stayRefund: $this->money(max(0.0, $paidAmount - $deposit - $cleaningFee - $serviceFee)),
                cleaningRefund: $cleaningFee,
                depositRefund: min($deposit, $paidAmount),
                serviceFeeRefund: $serviceFee,
                explanationKey: 'booking.cancellation.policy_explanations.host_full_refund',
                window: 'host_full_refund',
            );
        }

        if ($paidAmount <= 0.0) {
            return $this->estimate(
                policy: $policy,
                currency: $currency,
                paidAmount: 0.0,
                stayRefund: 0.0,
                cleaningRefund: 0.0,
                depositRefund: 0.0,
                serviceFeeRefund: 0.0,
                explanationKey: 'booking.cancellation.policy_explanations.unpaid',
                window: 'unpaid',
            );
        }

        if ($this->isFreeCancellation($booking, $policy, $clock, $checkIn)) {
            return $this->estimate(
                policy: $policy,
                currency: $currency,
                paidAmount: $paidAmount,
                stayRefund: $stayAmount,
                cleaningRefund: $cleaningFee,
                depositRefund: min($deposit, $paidAmount),
                serviceFeeRefund: $serviceFee,
                explanationKey: 'booking.cancellation.policy_explanations.free_window',
                window: 'free',
            );
        }

        $depositRefund = $beforeCheckIn && $policy->isDepositRefundableBeforeCheckIn()
            ? min($deposit, $paidAmount)
            : 0.0;

        $isPartialWindow = $this->isPartialRefundWindow($policy, $clock, $checkIn);
        $stayRefund = $isPartialWindow
            ? $this->money($stayAmount * $policy->stayRefundRateAfterFreeWindow())
            : 0.0;
        $cleaningRefund = $isPartialWindow && $policy->isCleaningFeeRefundableAfterFreeWindow()
            ? $cleaningFee
            : 0.0;
        $serviceFeeRefund = $isPartialWindow && $policy->isServiceFeeRefundableAfterFreeWindow()
            ? $serviceFee
            : 0.0;

        return $this->estimate(
            policy: $policy,
            currency: $currency,
            paidAmount: $paidAmount,
            stayRefund: $stayRefund,
            cleaningRefund: $cleaningRefund,
            depositRefund: $depositRefund,
            serviceFeeRefund: $serviceFeeRefund,
            explanationKey: $isPartialWindow
                ? 'booking.cancellation.policy_explanations.partial_window'
                : $policy->explanationKey(),
            window: $isPartialWindow ? 'partial' : 'late',
        );
    }

    private function isFreeCancellation(
        Booking $booking,
        CancellationPolicy $policy,
        CarbonImmutable $now,
        CarbonImmutable $checkIn,
    ): bool {
        if ($booking->free_cancel_before) {
            $freeCancelBefore = $booking->free_cancel_before instanceof CarbonInterface
                ? CarbonImmutable::instance($booking->free_cancel_before)
                : CarbonImmutable::parse((string) $booking->free_cancel_before);

            return $now->lessThan($freeCancelBefore);
        }

        $hours = $policy->freeCancellationUntilHoursBeforeCheckIn();

        return $hours > 0 && $now->lessThanOrEqualTo($checkIn->subHours($hours));
    }

    private function isPartialRefundWindow(CancellationPolicy $policy, CarbonImmutable $now, CarbonImmutable $checkIn): bool
    {
        $hours = $policy->partialRefundUntilHoursBeforeCheckIn();

        if ($hours === null || ! $now->lessThan($checkIn)) {
            return false;
        }

        return $now->lessThan($checkIn->subHours($hours));
    }

    private function checkInAt(Booking $booking): CarbonImmutable
    {
        $date = $booking->check_in_date ?: $booking->check_in ?: now();
        $checkIn = $date instanceof CarbonInterface
            ? CarbonImmutable::instance($date)->startOfDay()
            : CarbonImmutable::parse((string) $date)->startOfDay();

        if ($booking->check_in_time) {
            $time = $booking->check_in_time instanceof CarbonInterface
                ? CarbonImmutable::instance($booking->check_in_time)
                : CarbonImmutable::parse((string) $booking->check_in_time);

            return $checkIn->setTime((int) $time->format('H'), (int) $time->format('i'));
        }

        return $checkIn;
    }

    private function paidAmount(Booking $booking): float
    {
        $paymentStatus = $booking->payment_status instanceof PaymentStatus
            ? $booking->payment_status
            : PaymentStatus::tryFrom((string) $booking->payment_status);

        if (! in_array($paymentStatus, [
            PaymentStatus::Paid,
            PaymentStatus::PartiallyPaid,
            PaymentStatus::RefundedPartial,
            PaymentStatus::RefundedFull,
        ], true)) {
            return 0.0;
        }

        return $this->money($this->firstMoney($booking->total_amount, $booking->total));
    }

    private function estimate(
        CancellationPolicy $policy,
        string $currency,
        float $paidAmount,
        float $stayRefund,
        float $cleaningRefund,
        float $depositRefund,
        float $serviceFeeRefund,
        string $explanationKey,
        string $window,
    ): RefundEstimate {
        $refundAmount = min(
            $paidAmount,
            $this->money($stayRefund + $cleaningRefund + $depositRefund + $serviceFeeRefund),
        );
        $nonRefundableAmount = $this->money(max(0.0, $paidAmount - $refundAmount));
        $lines = [];

        $lines[] = $this->line('stay', $stayRefund, $currency, $stayRefund > 0.0);
        $lines[] = $this->line('cleaning_fee', $cleaningRefund, $currency, $cleaningRefund > 0.0);
        $lines[] = $this->line('deposit', $depositRefund, $currency, $depositRefund > 0.0);
        $lines[] = $this->line('service_fee', $serviceFeeRefund, $currency, $serviceFeeRefund > 0.0);
        $lines[] = $this->line('non_refundable', $nonRefundableAmount, $currency, false);

        return new RefundEstimate(
            policy: $policy,
            currency: $currency,
            paidAmount: $this->money($paidAmount),
            refundAmount: $this->money($refundAmount),
            depositRefundAmount: $this->money($depositRefund),
            nonRefundableAmount: $nonRefundableAmount,
            penaltyAmount: $nonRefundableAmount,
            depositRefunded: $depositRefund > 0.0,
            explanationKey: $explanationKey,
            window: $window,
            lines: $lines,
        );
    }

    /**
     * @return array{type:string,label_key:string,amount:float,currency:string,is_refundable:bool}
     */
    private function line(string $type, float $amount, string $currency, bool $refundable): array
    {
        return [
            'type' => $type,
            'label_key' => 'booking.cancellation.lines.'.$type,
            'amount' => $this->money($amount),
            'currency' => $currency,
            'is_refundable' => $refundable,
        ];
    }

    private function clock(CarbonInterface|string|null $now): CarbonImmutable
    {
        if ($now instanceof CarbonInterface) {
            return CarbonImmutable::instance($now);
        }

        if (is_string($now)) {
            return CarbonImmutable::parse($now);
        }

        return CarbonImmutable::now();
    }

    private function firstMoney(mixed $primary, mixed $fallback): float
    {
        return (float) (($primary !== null && (float) $primary !== 0.0) ? $primary : $fallback);
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }
}
