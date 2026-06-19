<?php

namespace App\Enums;

enum ComplaintType: string
{
    case CheckinProblem = 'checkin_problem';
    case Dirty = 'dirty';
    case Mismatch = 'mismatch';
    case MissingAmenity = 'missing_amenity';
    case Unsafe = 'unsafe';
    case NeighborIssue = 'neighbor_issue';
    case HostUnresponsive = 'host_unresponsive';
    case GuestRuleViolation = 'guest_rule_violation';
    case Damage = 'damage';
    case Theft = 'theft';
    case Noise = 'noise';
    case PaymentIssue = 'payment_issue';
    case RefundIssue = 'refund_issue';
    case Other = 'other';

    public function label(): string
    {
        return __('statuses.complaint_type.'.$this->value);
    }
}
