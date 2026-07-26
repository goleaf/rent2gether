<?php

return [
    'title' => 'Stay dates',

    'fields' => [
        'check_in_date' => 'Check-in date',
        'check_in_time' => 'Check-in time',
        'check_out_date' => 'Check-out date',
        'check_out_time' => 'Check-out time',
        'guests_count' => 'Guests',
        'nights_count' => 'Nights',
        'chargeable_days_count' => 'Paid days',
        'calendar_presence_days_count' => 'Calendar days',
        'early_check_in' => 'Early check-in',
        'late_check_out' => 'Late checkout',
        'flexible_check_in' => 'Flexible check-in',
        'flexible_check_out' => 'Flexible checkout',
        'requires_host_time_approval' => 'Host time approval required',
        'check_in_comment' => 'Check-in time note',
        'check_out_comment' => 'Checkout time note',
    ],

    'summary' => [
        'title' => 'Stay length',
    ],

    'time_preferences' => [
        'title' => 'Time preferences',
        'helper' => 'Add only what matters. The host can confirm special timing when needed.',
    ],

    'warnings' => [
        'title' => 'Date notes',
    ],

    'validation' => [
        'login_required' => 'Please sign in to calculate this stay.',
        'checkout_before_checkin' => 'Checkout date cannot be before check-in date.',
        'checkout_same_day_not_allowed' => 'Checkout date cannot be the same as check-in date.',
        'sleeping_place_unavailable' => 'This sleeping place is not available for the selected dates.',
        'closed_by_service' => 'The service has temporarily closed these dates.',
        'date_locked_by_another_booking' => 'These dates are already held or booked.',
        'property_blocked' => 'This property is not available for the selected dates.',
        'room_blocked' => 'This room is not available for the selected dates.',
        'room_repair' => 'This room is under repair for the selected dates.',
        'sleeping_place_repair' => 'This sleeping place is under repair for the selected dates.',
        'broken' => 'This sleeping place needs a fix before it can be booked.',
        'complaint_block' => 'This stay is unavailable because of an active complaint block.',
        'hidden' => 'This sleeping place is temporarily hidden for these dates.',
        'cleaning_gap_required' => 'The host needs time to clean before the next guest.',
        'inspection_gap_required' => 'The host needs an inspection before the next guest.',
        'below_min_nights' => 'The selected stay is shorter than the minimum of :count nights.',
        'above_max_nights' => 'The selected stay is longer than the maximum of :count nights.',
        'below_min_nights_checkout' => 'This checkout date is before the minimum stay.',
        'above_max_nights_checkout' => 'This checkout date is after the maximum stay.',
        'check_in_weekday_not_allowed' => 'Check-in is not available on this weekday.',
        'check_out_weekday_not_allowed' => 'Checkout is not available on this weekday.',
        'guest_verification_required' => 'This booking requires guest verification.',
        'guest_age_not_allowed' => 'This room has an age rule that does not match this guest.',
        'room_gender_policy_mismatch' => 'This room format does not match this guest profile.',
        'guests_count_too_high' => 'This sleeping place allows up to :count guests.',
        'host_confirmation_required' => 'The host needs to confirm this stay.',
        'request_only' => 'This sleeping place is available by request.',
    ],

    'messages' => [
        'select_dates_helper' => 'Choose dates for one specific sleeping place. The quote is only a preview until you continue.',
        'select_check_in_first' => 'Choose check-in first.',
        'available_check_out_dates' => 'Available checkout dates',
        'choose_checkout_helper' => 'These dates keep the stay within the current availability rules.',
        'earliest_checkout' => 'Earliest checkout',
        'latest_checkout' => 'Latest checkout',
        'blocked_checkout_dates' => 'Unavailable checkout dates',
        'checkout_unavailable_reason' => ':date is not available: :reason',
        'alternatives_available' => 'There are nearby or similar alternatives if these dates do not fit.',
        'price_recalculated' => 'Price recalculated.',
        'nearest_dates_found' => 'We found nearby available dates.',
        'nights_short' => ':count night|:count nights',
    ],

    'empty' => [
        'select_check_in_first' => 'Choose check-in first to see checkout dates.',
        'no_checkout_dates' => 'No checkout dates are available from this check-in date.',
    ],
];
