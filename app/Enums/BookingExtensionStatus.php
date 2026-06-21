<?php

namespace App\Enums;

enum BookingExtensionStatus: string
{
    case Draft = 'draft';
    case QuoteCreated = 'quote_created';
    case AvailabilityCheckFailed = 'availability_check_failed';
    case WaitingHostConfirmation = 'waiting_host_confirmation';
    case WaitingGuestResponse = 'waiting_guest_response';
    case AwaitingHostApproval = 'awaiting_host_approval';
    case AwaitingPayment = 'awaiting_payment';
    case Approved = 'approved';
    case ApprovedWaitingPayment = 'approved_waiting_payment';
    case WaitingPayment = 'waiting_payment';
    case Paid = 'paid';
    case Scheduled = 'scheduled';
    case Applied = 'applied';
    case Declined = 'declined';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case CancelledByGuest = 'cancelled_by_guest';
    case CancelledByHost = 'cancelled_by_host';
    case PaymentFailed = 'payment_failed';
    case DatesUnavailable = 'dates_unavailable';
    case Disputed = 'disputed';
    case Closed = 'closed';

    public function label(): string
    {
        return __('booking_extensions.statuses.'.$this->value);
    }
}
