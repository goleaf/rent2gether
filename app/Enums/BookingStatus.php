<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Created = 'created';
    case AwaitingHostApproval = 'awaiting_host_approval';
    case AwaitingGuestResponse = 'awaiting_guest_response';
    case AwaitingPayment = 'awaiting_payment';
    case AwaitingIdentityVerification = 'awaiting_identity_verification';
    case AwaitingDocumentVerification = 'awaiting_document_verification';
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
    case AwaitingRoomInspection = 'awaiting_room_inspection';
    case WaitingDepositReturn = 'waiting_deposit_return';
    case AwaitingDepositReturn = 'awaiting_deposit_return';
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
    case CancelledByServiceCanonical = 'cancelled_by_service';
    case PaymentFailed = 'payment_failed';
    case Unpaid = 'unpaid';
    case NoShow = 'no_show';
    case GuestNoShow = 'guest_no_show';
    case HostUnresponsive = 'host_unresponsive';
    case HostNoShow = 'host_no_show';
    case DisputeOpened = 'dispute_opened';
    case Disputed = 'disputed';
    case ProblemReported = 'problem_reported';
    case RefundRequested = 'refund_requested';
    case FrozenUntilDisputeResolved = 'frozen_until_dispute_resolved';
    case FrozenPendingDisputeResolution = 'frozen_pending_dispute_resolution';
    case FutureSupportRequired = 'future_support_required';
    case NeedsSupportIntervention = 'needs_support_intervention';
    case RequiresSupportIntervention = 'requires_support_intervention';

    public function label(): string
    {
        return __('statuses.booking.'.$this->canonicalValue());
    }

    public function canonicalValue(): string
    {
        return match ($this) {
            self::WaitingHostConfirmation, self::PendingHostConfirmation => self::AwaitingHostApproval->value,
            self::WaitingGuestResponse, self::PendingGuestResponse => self::AwaitingGuestResponse->value,
            self::WaitingPayment, self::PendingPayment => self::AwaitingPayment->value,
            self::WaitingIdentityVerification, self::PendingIdentityCheck => self::AwaitingIdentityVerification->value,
            self::WaitingDocumentVerification, self::PendingDocumentCheck => self::AwaitingDocumentVerification->value,
            self::ReadyForCheckIn => self::ReadyForCheckInCore->value,
            self::CheckedIn => self::GuestCheckedIn->value,
            self::StayInProgress, self::ActiveStay => self::InProgress->value,
            self::LeavingSoon => self::CheckOutSoon->value,
            self::CheckedOut => self::GuestCheckedOut->value,
            self::WaitingPropertyInspection => self::AwaitingRoomInspection->value,
            self::WaitingDepositReturn => self::AwaitingDepositReturn->value,
            self::WaitingReview => self::AwaitingReview->value,
            self::RejectedByHost => self::DeclinedByHost->value,
            self::CancelledByServiceFuture,
            self::CancelledBySystem,
            self::CancelledByService => self::CancelledByServiceCanonical->value,
            self::PaymentFailed => self::Unpaid->value,
            self::NoShow => self::GuestNoShow->value,
            self::HostNoShow => self::HostUnresponsive->value,
            self::Disputed => self::DisputeOpened->value,
            self::FrozenUntilDisputeResolved => self::FrozenPendingDisputeResolution->value,
            self::FutureSupportRequired, self::NeedsSupportIntervention => self::RequiresSupportIntervention->value,
            default => $this->value,
        };
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
            self::CancelledByServiceCanonical,
        ]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::CheckedIn, self::GuestCheckedIn, self::InProgress, self::StayInProgress, self::ActiveStay]);
    }
}
