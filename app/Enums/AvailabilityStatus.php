<?php

namespace App\Enums;

enum AvailabilityStatus: string
{
    case Available = 'available';
    case BlockedByHost = 'blocked_by_host';
    case ClosedByHost = 'closed_by_host';
    case ClosedByService = 'closed_by_service';
    case ClosedByServiceFuture = 'closed_by_service_future';
    case Booked = 'booked';
    case PendingPayment = 'pending_payment';
    case PaymentPending = 'payment_pending';
    case PendingApproval = 'pending_approval';
    case PendingHostConfirmation = 'pending_host_confirmation';
    case HostConfirmationPending = 'host_confirmation_pending';
    case Occupied = 'occupied';
    case GuestCheckedIn = 'guest_checked_in';
    case GuestCheckedOut = 'guest_checked_out';
    case Cleaning = 'cleaning';
    case Repair = 'repair';
    case Broken = 'broken';
    case ComplaintBlocked = 'complaint_blocked';
    case Hidden = 'hidden';
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
        return __('statuses.availability.'.$this->canonicalValue());
    }

    public function canonicalValue(): string
    {
        return match ($this) {
            self::BlockedByHost => self::ClosedByHost->value,
            self::ClosedByServiceFuture => self::ClosedByService->value,
            self::PaymentPending => self::PendingPayment->value,
            self::PendingApproval, self::HostConfirmationPending => self::PendingHostConfirmation->value,
            self::UnavailableBreakdown => self::Broken->value,
            self::UnavailableComplaint => self::ComplaintBlocked->value,
            self::TemporarilyHidden => self::Hidden->value,
            self::Blocked => self::ClosedByHost->value,
            self::Maintenance => self::Repair->value,
            default => $this->value,
        };
    }

    /**
     * @return list<string>
     */
    public static function blocksStayValues(): array
    {
        return [
            self::BlockedByHost->value,
            self::ClosedByHost->value,
            self::ClosedByService->value,
            self::ClosedByServiceFuture->value,
            self::Booked->value,
            self::PendingPayment->value,
            self::PaymentPending->value,
            self::PendingApproval->value,
            self::PendingHostConfirmation->value,
            self::HostConfirmationPending->value,
            self::Occupied->value,
            self::GuestCheckedIn->value,
            self::Cleaning->value,
            self::Repair->value,
            self::Broken->value,
            self::ComplaintBlocked->value,
            self::Hidden->value,
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
            self::PendingHostConfirmation->value,
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
            self::Broken->value,
            self::Hidden->value,
            self::Unavailable->value,
            self::UnavailableBreakdown->value,
            self::TemporarilyHidden->value,
            self::RequestOnly->value,
            self::CheckInOnly->value,
            self::CheckOutOnly->value,
        ];
    }
}
