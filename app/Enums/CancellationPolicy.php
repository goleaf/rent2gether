<?php

namespace App\Enums;

enum CancellationPolicy: string
{
    case Flexible = 'flexible';
    case Moderate = 'moderate';
    case Strict = 'strict';
    case NonRefundable = 'non_refundable';

    public function label(): string
    {
        return match ($this) {
            self::Flexible => __('listing.cancellation_policy.flexible'),
            self::Moderate => __('listing.cancellation_policy.moderate'),
            self::Strict => __('listing.cancellation_policy.strict'),
            self::NonRefundable => __('listing.cancellation_policy.non_refundable'),
        };
    }

    public function freeCancelHours(): int
    {
        return $this->freeCancellationUntilHoursBeforeCheckIn();
    }

    public function freeCancellationUntilHoursBeforeCheckIn(): int
    {
        return match ($this) {
            self::Flexible => 24,
            self::Moderate => 120,
            self::Strict => 168,
            self::NonRefundable => 0,
        };
    }

    public function partialRefundUntilHoursBeforeCheckIn(): ?int
    {
        return match ($this) {
            self::Flexible => 0,
            self::Moderate => 24,
            self::Strict => 72,
            self::NonRefundable => null,
        };
    }

    public function stayRefundRateAfterFreeWindow(): float
    {
        return match ($this) {
            self::Flexible, self::Moderate, self::Strict => 0.5,
            self::NonRefundable => 0.0,
        };
    }

    public function isCleaningFeeRefundableAfterFreeWindow(): bool
    {
        return match ($this) {
            self::Flexible, self::Moderate => true,
            self::Strict, self::NonRefundable => false,
        };
    }

    public function isDepositRefundableBeforeCheckIn(): bool
    {
        return true;
    }

    public function isServiceFeeRefundableAfterFreeWindow(): bool
    {
        return false;
    }

    public function explanationKey(): string
    {
        return 'booking.cancellation.policy_explanations.'.$this->value;
    }
}
