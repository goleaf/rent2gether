<?php

namespace App\Enums;

enum BookingType: string
{
    case Instant = 'instant';
    case HostApproval = 'host_approval';
    case StayRequest = 'stay_request';
    case PreliminaryRequest = 'preliminary_request';
    case PreliminaryInquiry = 'preliminary_inquiry';
    case LongTermRequest = 'long_term_request';
    case UrgentToday = 'urgent_today';
    case SameDayUrgent = 'same_day_urgent';
    case AwaitingPayment = 'awaiting_payment';
    case WithDeposit = 'with_deposit';
    case NoDeposit = 'no_deposit';
    case PartialPayment = 'partial_payment';
    case FullPayment = 'full_payment';
    case Extension = 'extension';
    case Relocation = 'relocation';
    case GroupChild = 'group_child';
    case Reassignment = 'reassignment';
    case Group = 'group';
    case DoubleOccupancy = 'double_occupancy';

    public function label(): string
    {
        return __('statuses.booking_type.'.$this->value);
    }
}
