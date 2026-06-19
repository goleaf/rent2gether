<?php

namespace App\Enums;

enum PriceRuleType: string
{
    case DateRange = 'date_range';
    case Weekend = 'weekend';
    case Holiday = 'holiday';
    case Seasonal = 'seasonal';
}
