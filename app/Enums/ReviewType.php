<?php

namespace App\Enums;

enum ReviewType: string
{
    case GuestToPlace = 'guest_to_place';
    case HostToGuest = 'host_to_guest';
    case RoommateExperience = 'roommate_experience';
    case GuestCheckIn = 'guest_check_in';
    case GuestCheckOut = 'guest_check_out';
    case ProblemResolution = 'problem_resolution';

    public function label(): string
    {
        return __('statuses.review_type.'.$this->value);
    }
}
