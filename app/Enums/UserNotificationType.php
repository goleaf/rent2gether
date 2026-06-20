<?php

namespace App\Enums;

enum UserNotificationType: string
{
    case BookingRequestSent = 'booking_request_sent';
    case BookingRequestAccepted = 'booking_request_accepted';
    case BookingRequestDeclined = 'booking_request_declined';
    case PaymentRequired = 'payment_required';
    case PaymentReceived = 'payment_received';
    case BookingPaymentReceived = 'booking_payment_received';
    case BookingConfirmed = 'booking_confirmed';
    case BookingCancelledByGuest = 'booking_cancelled_by_guest';
    case BookingCancelledByHost = 'booking_cancelled_by_host';
    case CheckInInstructionsAvailable = 'check_in_instructions_available';
    case TomorrowCheckIn = 'tomorrow_check_in';
    case TodayCheckIn = 'today_check_in';
    case CheckoutTomorrow = 'checkout_tomorrow';
    case ExtensionAvailable = 'extension_available';
    case RefundUpdated = 'refund_updated';
    case DepositReturned = 'deposit_returned';
    case MessageReceived = 'message_received';
    case ReviewReminder = 'review_reminder';
    case FavoritePriceDrop = 'decision.favorite_price_drop';
    case WaitlistAvailable = 'decision.waitlist_available';
    case NewBookingRequest = 'new_booking_request';
    case GuestArrivingSoon = 'guest_arriving_soon';
    case GuestCheckedIn = 'guest_checked_in';
    case GuestReportsProblem = 'guest_reports_problem';
    case GuestCheckedOut = 'guest_checked_out';
    case ReviewReceived = 'review_received';
    case PlaceAvailabilityChanged = 'place_availability_changed';

    public function label(): string
    {
        return __('statuses.notification_type.'.str_replace('.', '_', $this->value));
    }
}
