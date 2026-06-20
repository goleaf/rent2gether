<?php

namespace App\Enums;

enum ComplaintType: string
{
    case CannotCheckIn = 'cannot_check_in';
    case HostNotResponding = 'host_not_responding';
    case WrongAddress = 'wrong_address';
    case PlaceNotAsDescribed = 'place_not_as_described';
    case DirtyRoom = 'dirty_room';
    case UnsafeSituation = 'unsafe_situation';
    case WantsCancellation = 'wants_cancellation';
    case WantsRefund = 'wants_refund';
    case GuestDidNotArrive = 'guest_did_not_arrive';
    case GuestBrokeRules = 'guest_broke_rules';
    case GuestDamagedProperty = 'guest_damaged_property';
    case GuestDidNotCheckOut = 'guest_did_not_check_out';
    case GuestDisturbedOthers = 'guest_disturbed_others';
    case GuestLeftMess = 'guest_left_mess';
    case WantsDepositHold = 'wants_deposit_hold';
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

    /**
     * @return list<self>
     */
    public static function guestTypes(): array
    {
        return [
            self::CannotCheckIn,
            self::HostNotResponding,
            self::WrongAddress,
            self::PlaceNotAsDescribed,
            self::DirtyRoom,
            self::UnsafeSituation,
            self::NeighborIssue,
            self::MissingAmenity,
            self::WantsCancellation,
            self::WantsRefund,
            self::Other,
        ];
    }

    /**
     * @return list<self>
     */
    public static function hostTypes(): array
    {
        return [
            self::GuestDidNotArrive,
            self::GuestBrokeRules,
            self::GuestDamagedProperty,
            self::GuestDidNotCheckOut,
            self::GuestDisturbedOthers,
            self::GuestLeftMess,
            self::WantsDepositHold,
            self::Other,
        ];
    }
}
