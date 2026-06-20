<?php

return [
    'bell' => [
        'label' => 'Open notifications',
        'unread_count' => '{1} :count unread notification|[2,*] :count unread notifications',
    ],
    'page' => [
        'eyebrow' => 'Updates',
        'title' => 'Notifications',
        'helper' => 'Important booking, payment, stay, message, and saved-place updates appear here.',
        'empty_title' => 'Nothing new right now',
        'empty_text' => 'When something needs your attention, we will keep it here with a clear next step.',
        'empty_action' => 'Search places',
    ],
    'actions' => [
        'open' => 'Open',
        'mark_read' => 'Mark as read',
        'mark_all_read' => 'Mark all read',
        'saving' => 'Saving...',
    ],
    'status' => [
        'read' => 'Read',
        'unread' => 'New',
    ],
    'saved' => [
        'title' => 'Saved',
        'body' => 'Your changes have been saved.',
    ],
    'booking_request_sent' => [
        'title' => 'Booking request sent',
        'body' => 'Your request for :place was sent. The host can review it now.',
    ],
    'new_booking_request' => [
        'title' => 'New booking request',
        'body' => ':guest wants to book :place. Review the dates when you have a moment.',
    ],
    'booking_requested' => [
        'title' => 'Booking request sent',
        'body' => 'The host can now review your request.',
    ],
    'booking_confirmed' => [
        'title' => 'Booking confirmed',
        'body' => 'Booking :reference is confirmed. Check the booking page for the latest details.',
    ],
    'booking_cancelled_by_guest' => [
        'title' => 'Booking cancelled',
        'body' => 'Booking :reference was cancelled by the guest. Estimated refund: :refund.',
    ],
    'booking_cancelled_by_host' => [
        'title' => 'Booking cancelled by host',
        'body' => 'The host cancelled booking :reference. Estimated refund: :refund.',
    ],
    'booking_request_accepted' => [
        'title' => 'Request accepted',
        'body' => 'The host accepted booking :reference. One step left: review the payment deadline.',
    ],
    'booking_request_declined' => [
        'title' => 'Request declined',
        'body' => 'The host declined booking :reference. You can adjust your search and choose another place.',
    ],
    'payment_required' => [
        'title' => 'Payment required',
        'body' => 'One step left for :place: review the total and pay before the deadline.',
    ],
    'payment_received' => [
        'title' => 'Payment received',
        'body' => 'Payment for booking :reference is recorded. Your stay details are ready.',
    ],
    'booking_payment_received' => [
        'title' => 'Payment received',
        'body' => 'The guest paid for booking :reference. Check the booking page for arrival details.',
    ],
    'check_in_instructions_available' => [
        'title' => 'Check-in instructions available',
        'body' => 'You can now see the address and check-in notes for booking :reference.',
    ],
    'tomorrow_check_in' => [
        'title' => 'Check-in is tomorrow',
        'body' => 'Your stay at :place starts tomorrow. Review the address and arrival details.',
    ],
    'today_check_in' => [
        'title' => 'Check-in is today',
        'body' => 'Your stay at :place starts today. Keep the host contact and instructions nearby.',
    ],
    'checkout_tomorrow' => [
        'title' => 'Checkout is tomorrow',
        'body' => 'Checkout for :place is tomorrow. Review keys, locker, and house rules before leaving.',
    ],
    'extension_available' => [
        'title' => 'Extension may be available',
        'body' => 'You may be able to stay longer at :place. Check available dates before checkout.',
    ],
    'refund_updated' => [
        'title' => 'Refund updated',
        'body' => 'Refund details for booking :reference were updated. Review the booking page.',
    ],
    'deposit_returned' => [
        'title' => 'Deposit returned',
        'body' => 'The deposit for booking :reference was marked as returned.',
    ],
    'booking_extension_requested' => [
        'title' => 'Extension requested',
        'body' => 'The guest wants to stay longer. Review the new checkout date.',
    ],
    'booking_extension_awaiting_payment' => [
        'title' => 'One step left',
        'body' => 'The extension is ready for payment. Review the extra amount before confirming.',
    ],
    'booking_extension_approved' => [
        'title' => 'Extension approved',
        'body' => 'The booking checkout date was updated.',
    ],
    'booking_extension_declined' => [
        'title' => 'Extension declined',
        'body' => 'The extension request was declined. Check the booking for details.',
    ],
    'booking_extension_paid' => [
        'title' => 'Extension paid',
        'body' => 'The guest paid for the extra nights.',
    ],
    'booking_extension_cancelled' => [
        'title' => 'Extension cancelled',
        'body' => 'The guest cancelled the extension request.',
    ],
    'message_received' => [
        'title' => 'New message',
        'body' => 'Open the conversation when you have a moment.',
    ],
    'review_reminder' => [
        'title' => 'Review reminder',
        'body' => 'How was :place? A short review helps future guests and hosts.',
    ],
    'guest_arriving_soon' => [
        'title' => 'Guest arriving soon',
        'body' => ':guest arrives soon for booking :reference. Check arrival details when convenient.',
    ],
    'guest_checked_in' => [
        'title' => 'Guest checked in',
        'body' => ':guest marked booking :reference as checked in.',
    ],
    'guest_reports_problem' => [
        'title' => 'Guest reported a problem',
        'body' => ':guest reported a problem for booking :reference. Review it calmly and reply.',
    ],
    'guest_checked_out' => [
        'title' => 'Guest checked out',
        'body' => ':guest marked booking :reference as checked out. Review the place when ready.',
    ],
    'review_received' => [
        'title' => 'New review received',
        'body' => 'A new review is available for :place.',
    ],
    'place_availability_changed' => [
        'title' => 'Availability changed',
        'body' => 'Availability for :place was updated.',
    ],
    'action_needed' => [
        'title' => 'One step left',
        'body' => 'Open the booking to see what to do next.',
    ],
    'decision' => [
        'favorite_price_drop' => [
            'title' => 'A favorite is cheaper now',
            'body' => 'Open the place to check the current price before booking.',
        ],
        'waitlist_available' => [
            'title' => 'A waitlisted place is available',
            'body' => 'Your dates may work now. Check the details before booking.',
        ],
        'waitlist_price_drop' => [
            'title' => 'A waitlisted place dropped in price',
            'body' => 'The price is lower than when you joined the waitlist.',
        ],
    ],
    'flash' => [
        'review_submitted' => 'Review submitted. Thank you!',
        'complaint_submitted' => 'Complaint submitted. Reference: :reference',
        'complaint_response_saved' => 'Response saved. Thank you for explaining calmly.',
        'booking_cancelled' => 'Booking cancelled.',
        'search_saved' => 'Search saved.',
        'extension_requested' => 'Extension request submitted.',
        'extension_ready_for_payment' => 'Extension is ready for payment.',
        'extension_payment_recorded' => 'Extension payment recorded.',
        'extension_cancelled' => 'Extension request cancelled.',
        'booking_created' => 'Booking created.',
        'profile_updated' => 'Profile updated.',
        'host_profile_updated' => 'Host profile saved.',
        'guest_preferences_updated' => 'Guest preferences saved.',
        'account_settings_updated' => 'Account settings saved.',
        'privacy_settings_updated' => 'Privacy settings saved.',
        'security_updated' => 'Security settings saved.',
        'extension_approved' => 'Extension approved.',
        'extension_rejected' => 'Extension rejected.',
        'checkin_recorded' => 'Check-in recorded.',
        'checkin_problem_reported' => 'Problem report sent. The host can review it on the booking.',
        'checkout_recorded' => 'Check-out recorded.',
        'host_checkin_confirmed' => 'Check-in confirmed.',
        'host_checkout_confirmed' => 'Check-out confirmed. The booking is completed.',
        'bed_updated' => 'Sleeping place updated.',
        'bed_created' => 'Sleeping place created.',
        'booking_approved' => 'Booking approved.',
        'booking_rejected' => 'Booking rejected.',
        'booking_refunded' => 'Booking cancelled with a full refund.',
        'property_updated' => 'Property updated.',
        'property_created' => 'Property created.',
        'room_updated' => 'Room updated.',
        'room_created' => 'Room created.',
        'room_duplicated' => 'Room duplicated as a draft.',
        'room_deleted' => 'Draft room deleted.',
        'room_delete_only_draft' => 'Only draft rooms can be deleted here.',
        'sleeping_places_generated' => '{1} :count sleeping place draft created.|[2,*] :count sleeping place drafts created.',
        'sleeping_places_already_ready' => 'Sleeping place drafts are already ready.',
        'sleeping_place_created' => 'Sleeping place saved.',
        'sleeping_place_updated' => 'Sleeping place updated.',
        'sleeping_place_duplicated' => 'Sleeping place duplicated as a draft.',
        'host_request_accepted' => 'Request accepted. The guest was notified.',
        'host_request_declined' => 'Request declined. The hold was released and the guest was notified.',
        'host_request_message_sent' => 'Message sent.',
        'host_request_expiry_saved' => 'Payment deadline saved.',
        'payment_confirmed' => 'Payment recorded. The booking is confirmed.',
    ],
];
