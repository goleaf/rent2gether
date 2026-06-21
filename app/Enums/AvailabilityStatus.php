<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case BlockedByHost = 'blocked_by_host';
    case ClosedByHost = 'closed_by_host';
    case ClosedByServiceFuture = 'closed_by_service_future';
    case Booked = 'booked';
    case PendingPayment = 'pending_payment';
    case PaymentPending = 'payment_pending';
    case PendingApproval = 'pending_approval';
    case HostConfirmationPending = 'host_confirmation_pending';
    case Occupied = 'occupied';
    case GuestCheckedIn = 'guest_checked_in';
    case GuestCheckedOut = 'guest_checked_out';
    case Cleaning = 'cleaning';
    case Repair = 'repair';
    case Unavailable = 'unavailable';
    case UnavailableBreakdown = 'unavailable_breakdown';
    case UnavailableComplaint = 'unavailable_complaint';
    case TemporarilyHidden = 'temporarily_hidden';
    case RequestOnly = 'request_only';
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
            self::ClosedByHost->value,
            self::ClosedByServiceFuture->value,
            self::Booked->value,
            self::PendingPayment->value,
            self::PaymentPending->value,
            self::PendingApproval->value,
            self::HostConfirmationPending->value,
            self::Occupied->value,
            self::GuestCheckedIn->value,
            self::Cleaning->value,
            self::Repair->value,
            self::Unavailable->value,
            self::UnavailableBreakdown->value,
            self::UnavailableComplaint->value,
            self::TemporarilyHidden->value,
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
            self::PaymentPending->value,
            self::PendingApproval->value,
            self::HostConfirmationPending->value,
            self::GuestCheckedIn->value,
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
            self::ClosedByHost->value,
            self::Cleaning->value,
            self::Repair->value,
            self::Unavailable->value,
            self::UnavailableBreakdown->value,
            self::TemporarilyHidden->value,
            self::RequestOnly->value,
            self::CheckInOnly->value,
            self::CheckOutOnly->value,
        ];
    }
}
