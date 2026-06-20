<?php

namespace App\Enums;

enum PriceRuleType: string
{
    case DateRange = 'date_range';
    case Weekend = 'weekend';
    case Holiday = 'holiday';
    case Seasonal = 'seasonal';

    public function label(): string
    {
        return __('statuses.price_rule_type.'.$this->value);
    }
}
