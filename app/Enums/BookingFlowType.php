<?php

namespace App\Enums;

enum BookingFlowType: string
{
    case InstantBooking = 'instant_booking';
    case HostConfirmationBooking = 'host_confirmation_booking';
    case StayRequest = 'stay_request';
    case PreliminaryInquiry = 'preliminary_inquiry';
    case LongTermRequest = 'long_term_request';
    case UrgentTodayBooking = 'urgent_today_booking';

    public function label(): string
    {
        return __('statuses.booking_flow_type.'.$this->value);
    }
}
