<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Created = 'created';
    case AwaitingHostApproval = 'awaiting_host_approval';
    case AwaitingPayment = 'awaiting_payment';
    case WaitingHostConfirmation = 'waiting_host_confirmation';
    case WaitingGuestResponse = 'waiting_guest_response';
    case WaitingPayment = 'waiting_payment';
    case WaitingIdentityVerification = 'waiting_identity_verification';
    case WaitingDocumentVerification = 'waiting_document_verification';
    case PendingHostConfirmation = 'pending_host';
    case PendingGuestResponse = 'pending_guest';
    case PendingPayment = 'pending_payment';
    case PendingIdentityCheck = 'pending_identity_check';
    case PendingDocumentCheck = 'pending_document_check';
    case Confirmed = 'confirmed';
    case Paid = 'paid';
    case ReadyForCheckIn = 'ready_for_checkin';
    case ReadyForCheckInCore = 'ready_for_check_in';
    case CheckedIn = 'checked_in';
    case GuestCheckedIn = 'guest_checked_in';
    case InProgress = 'in_progress';
    case StayInProgress = 'stay_in_progress';
    case ActiveStay = 'active_stay';
    case LeavingSoon = 'leaving_soon';
    case CheckOutSoon = 'check_out_soon';
    case CheckedOut = 'checked_out';
    case GuestCheckedOut = 'guest_checked_out';
    case WaitingPropertyInspection = 'waiting_property_inspection';
    case WaitingDepositReturn = 'waiting_deposit_return';
    case Completed = 'completed';
    case AwaitingReview = 'awaiting_review';
    case WaitingReview = 'waiting_review';
    case Closed = 'closed';
    case DeclinedByHost = 'declined_by_host';
    case RejectedByHost = 'rejected_by_host';
    case CancelledByGuestFlow = 'cancelled_by_guest';
    case CancelledByHostFlow = 'cancelled_by_host';
    case CancelledByServiceFuture = 'cancelled_by_service_future';
    case Expired = 'expired';
    case CancelledByGuest = 'cancelled_guest';
    case CancelledByHost = 'cancelled_host';
    case CancelledBySystem = 'cancelled_system';
    case CancelledByService = 'cancelled_service';
    case PaymentFailed = 'payment_failed';
    case NoShow = 'no_show';
    case HostUnresponsive = 'host_unresponsive';
    case HostNoShow = 'host_no_show';
    case DisputeOpened = 'dispute_opened';
    case Disputed = 'disputed';
    case ProblemReported = 'problem_reported';
    case RefundRequested = 'refund_requested';
    case FrozenUntilDisputeResolved = 'frozen_until_dispute_resolved';
    case FutureSupportRequired = 'future_support_required';
    case NeedsSupportIntervention = 'needs_support_intervention';

    public function label(): string
    {
        return __('statuses.booking.'.$this->value);
    }

    public function isCancelled(): bool
    {
        return in_array($this, [
            self::CancelledByGuestFlow,
            self::CancelledByHostFlow,
            self::CancelledByServiceFuture,
            self::CancelledByGuest,
            self::CancelledByHost,
            self::CancelledBySystem,
            self::CancelledByService,
        ]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::CheckedIn, self::GuestCheckedIn, self::InProgress, self::StayInProgress, self::ActiveStay]);
    }
}
