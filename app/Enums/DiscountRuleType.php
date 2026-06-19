<?php

namespace App\Enums;

enum DiscountRuleType: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case LastMinute = 'last_minute';
    case EarlyBird = 'early_bird';
}
