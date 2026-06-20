<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case BlockedByHost = 'blocked_by_host';
    case Booked = 'booked';
    case PendingPayment = 'pending_payment';
    case PendingApproval = 'pending_approval';
    case Cleaning = 'cleaning';
    case Repair = 'repair';
    case Unavailable = 'unavailable';
    case CheckInOnly = 'check_in_only';
    case CheckOutOnly = 'check_out_only';

    case Blocked = 'blocked';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return __('statuses.availability.'.$this->value);
    }

    /**
     * @return list<string>
     */
    public static function blocksStayValues(): array
    {
        return [
            self::BlockedByHost->value,
            self::Booked->value,
            self::PendingPayment->value,
            self::PendingApproval->value,
            self::Cleaning->value,
            self::Repair->value,
            self::Unavailable->value,
            self::CheckOutOnly->value,
            self::Blocked->value,
            self::Maintenance->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function bookingHoldValues(): array
    {
        return [
            self::Booked->value,
            self::PendingPayment->value,
            self::PendingApproval->value,
        ];
    }

    /**
     * @return list<string>
     */
    public static function hostEditableValues(): array
    {
        return [
            self::Available->value,
            self::BlockedByHost->value,
            self::Cleaning->value,
            self::Repair->value,
            self::Unavailable->value,
            self::CheckInOnly->value,
            self::CheckOutOnly->value,
        ];
    }
}
