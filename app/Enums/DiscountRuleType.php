<?php

namespace App\Enums;

enum DiscountRuleType: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case LastMinute = 'last_minute';
    case EarlyBird = 'early_bird';

    public function label(): string
    {
        return __('statuses.discount_rule_type.'.$this->value);
    }
}
