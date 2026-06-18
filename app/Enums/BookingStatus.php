<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Draft = 'draft';
    case Created = 'created';
    case PendingHostConfirmation = 'pending_host';
    case PendingGuestResponse = 'pending_guest';
    case PendingPayment = 'pending_payment';
    case PendingIdentityCheck = 'pending_identity_check';
    case PendingDocumentCheck = 'pending_document_check';
    case Confirmed = 'confirmed';
    case Paid = 'paid';
    case ReadyForCheckIn = 'ready_for_checkin';
    case CheckedIn = 'checked_in';
    case ActiveStay = 'active_stay';
    case LeavingSoon = 'leaving_soon';
    case CheckedOut = 'checked_out';
    case Completed = 'completed';
    case AwaitingReview = 'awaiting_review';
    case Closed = 'closed';
    case CancelledByGuest = 'cancelled_guest';
    case CancelledByHost = 'cancelled_host';
    case CancelledBySystem = 'cancelled_system';
    case CancelledByService = 'cancelled_service';
    case NoShow = 'no_show';
    case HostNoShow = 'host_no_show';
    case Disputed = 'disputed';
    case FrozenUntilDisputeResolved = 'frozen_until_dispute_resolved';
    case NeedsSupportIntervention = 'needs_support_intervention';

    public function label(): string
    {
        return __('statuses.booking.'.$this->value);
    }

    public function isCancelled(): bool
    {
        return in_array($this, [
            self::CancelledByGuest,
            self::CancelledByHost,
            self::CancelledBySystem,
            self::CancelledByService,
        ]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::CheckedIn, self::ActiveStay]);
    }
}
