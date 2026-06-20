<?php

namespace App\Enums;

enum MessageThreadType: string
{
    case PreBooking = 'pre_booking';
    case Booking = 'booking';
    case CurrentStay = 'current_stay';
    case ComplaintRelated = 'complaint_related';

    public function label(): string
    {
        return __('statuses.message_thread_type.'.$this->value);
    }
}
