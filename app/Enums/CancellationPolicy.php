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
            self::Flexible => __('app.cancellation_policy.flexible'),
            self::Moderate => __('app.cancellation_policy.moderate'),
            self::Strict => __('app.cancellation_policy.strict'),
            self::NonRefundable => __('app.cancellation_policy.non_refundable'),
        };
    }

    public function freeCancelHours(): int
    {
        return match ($this) {
            self::Flexible => 24,
            self::Moderate => 120,
            self::Strict => 168,
            self::NonRefundable => 0,
        };
    }
}
