<?php

return [
    'title' => 'Booking quote',

    'statuses' => [
        'draft' => 'Draft',
        'valid' => 'Quote ready',
        'invalid' => 'Quote unavailable',
        'expired' => 'Quote expired',
        'converted_to_booking' => 'Converted to booking',
        'converted_to_request' => 'Converted to request',
        'cancelled' => 'Cancelled',
    ],

    'availability_statuses' => [
        'unchecked' => 'Availability not checked',
        'available' => 'Available',
        'unavailable' => 'Unavailable',
        'partially_unavailable' => 'Partially unavailable',
        'request_only' => 'Request only',
    ],

    'validation_statuses' => [
        'unchecked' => 'Dates not checked',
        'valid' => 'Dates valid',
        'invalid' => 'Dates invalid',
        'warnings' => 'Warnings',
    ],

    'pricing_statuses' => [
        'unchecked' => 'Price not calculated',
        'calculated' => 'Price calculated',
        'failed' => 'Price failed',
    ],

    'price' => [
        'accommodation' => 'Accommodation',
        'discount' => 'Discount',
        'cleaning_fee' => 'Cleaning fee',
        'service_fee' => 'Service fee',
        'deposit' => 'Deposit',
        'total_without_deposit' => 'Total without deposit',
        'total_payable' => 'Total due now',
        'refundable_amount' => 'Refundable',
        'non_refundable_amount' => 'Non-refundable',
    ],

    'lines' => [
        'title' => 'Price breakdown',
        'helper' => 'Each line explains what is included in this preview.',
        'night' => 'Night',
        'weekday_night' => 'Weekday night',
        'weekend_night' => 'Weekend night',
        'holiday_night' => 'Holiday night',
        'date_override' => 'Special date price',
        'discount' => 'Discount',
        'weekly_discount' => 'Weekly discount',
        'monthly_discount' => 'Monthly discount',
        'long_stay_discount' => 'Long-stay discount',
        'early_booking_discount' => 'Early booking discount',
        'last_minute_discount' => 'Last-minute discount',
        'new_guest_discount' => 'New guest discount',
        'personal_discount' => 'Personal discount',
        'promo_discount' => 'Promo discount',
        'early_check_in_fee' => 'Early check-in fee',
        'late_checkout_fee' => 'Late checkout fee',
        'extra_guest_fee' => 'Extra guest fee',
        'cleaning_fee' => 'Cleaning fee',
        'service_fee' => 'Service fee',
        'tax_future' => 'Tax',
        'city_fee_future' => 'City fee',
        'deposit' => 'Refundable deposit',
        'other' => 'Other',
        'refundable' => 'Refundable',
    ],

    'validation' => [
        'title' => 'Quote messages',
    ],

    'cancellation' => [
        'title' => 'Cancellation preview',
        'not_available' => 'Not available',
    ],

    'timeline' => [
        'title' => 'Important dates',
        'payment_deadline' => 'Payment deadline',
        'free_cancellation_until' => 'Free cancellation until',
        'cancellation_penalty_starts' => 'Cancellation penalty starts',
        'guest_check_in_reminder' => 'Guest check-in reminder',
        'host_check_in_reminder' => 'Host check-in reminder',
        'guest_check_out_reminder' => 'Guest checkout reminder',
        'host_check_out_reminder' => 'Host checkout reminder',
        'deposit_review_start' => 'Deposit review starts',
        'host_payout_due' => 'Host payout due',
        'review_request' => 'Review request',
    ],

    'timeline_statuses' => [
        'pending' => 'Pending',
        'created' => 'Created',
        'cancelled' => 'Cancelled',
        'rescheduled' => 'Rescheduled',
        'completed' => 'Completed',
    ],

    'suggestions' => [
        'title' => 'Available options',
        'nearest_dates' => 'Try these nearby dates.',
        'same_room_place' => 'Another place in this room is available.',
        'same_property_place' => 'Another place in this property is available.',
        'same_host_place' => 'Another place from this host is available.',
        'similar_place' => 'A similar place may fit these dates.',
        'increase_budget' => 'Increasing the budget may show more options.',
        'change_dates' => 'Changing dates may help.',
        'range' => ':check_in to :check_out, :nights nights',
    ],

    'actions' => [
        'continue_to_booking' => 'Continue to booking',
        'send_request' => 'Send request',
        'recalculate' => 'Recalculate',
    ],

    'messages' => [
        'deposit_refundable' => ':amount is a refundable deposit after checkout.',
        'quote_expired' => 'This quote expired. Please recalculate before continuing.',
        'quote_expires_at' => 'This quote is valid until :time.',
        'quote_not_available' => 'This quote is not available.',
        'quote_recalculate_required' => 'Please recalculate this quote before continuing.',
    ],
];
