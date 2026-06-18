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
            self::Flexible => 'Flexible',
            self::Moderate => 'Moderate',
            self::Strict => 'Strict',
            self::NonRefundable => 'Non-refundable',
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
