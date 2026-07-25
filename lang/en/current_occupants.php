<?php

return [
    'title' => 'Current occupants',

    'sections' => [
        'page' => 'Current occupants',
        'filters' => 'Filters',
        'summary' => 'Stay summary',
        'occupant_card' => 'Occupant card',
        'details_sheet' => 'Stay details',
        'quick_actions' => 'Quick actions',
        'payment_status_badge' => 'Payment status',
        'stay_status_badge' => 'Stay status',
        'note_sheet' => 'Host note',
        'flags_panel' => 'Needs attention',
        'extension_panel' => 'Extension',
        'checkout_panel' => 'Checkout',
    ],

    'helpers' => [
        'page' => 'See who is staying now, who is arriving, who is leaving, and what needs attention.',
        'filters' => 'Narrow the list by date, payment, room, place, or attention flags.',
        'summary' => 'A compact view of today’s active stays and urgent follow-ups.',
        'occupant_card' => 'Mobile cards keep the most useful stay facts visible.',
        'details_sheet' => 'Open the sheet for payment, requests, notes, complaints, and contact actions.',
        'quick_actions' => 'Use safe actions from the card. Risky actions ask for confirmation.',
        'payment_status_badge' => 'Payment labels use the booking payment state.',
        'stay_status_badge' => 'Stay labels come from booking dates and check-in activity.',
        'note_sheet' => 'Private notes are visible only to the host.',
        'flags_panel' => 'Flags highlight checkout, payment, cleaning, extension, and complaint needs.',
        'extension_panel' => 'Offer or review a stay extension without changing the booking silently.',
        'checkout_panel' => 'Checkout actions help the host review the stay before closing it.',
    ],

    'summary' => [
        'current' => 'Currently staying: :count',
        'check_ins_today' => 'Checking in today: :count',
        'check_outs_today' => 'Checking out today: :count',
        'needs_attention' => 'Needs attention: :count',
        'payment_pending' => 'Payment pending: :count',
        'complaints' => 'Complaints: :count',
    ],

    'summary_labels' => [
        'current' => 'Now',
        'check_ins_today' => 'Arrivals',
        'check_outs_today' => 'Departures',
        'needs_attention' => 'Attention',
    ],

    'fields' => [
        'guest' => 'Guest',
        'guest_photo' => 'Guest photo',
        'room' => 'Room',
        'sleeping_place' => 'Sleeping place',
        'check_in_date' => 'Check-in date',
        'check_out_date' => 'Check-out date',
        'nights_count' => 'Nights',
        'nights_left' => 'Nights left',
        'payment_status' => 'Payment status',
        'stay_status' => 'Stay status',
        'guest_contact' => 'Guest contact',
        'special_requests' => 'Special requests',
        'guest_rating' => 'Guest rating',
        'has_complaints' => 'Complaints',
        'needs_extension' => 'Extension needed',
        'needs_checkout' => 'Checkout review needed',
        'host_comment' => 'Host comment',
    ],

    'filters' => [
        'title' => 'Quick filters',
        'all' => 'All current occupants',
        'check_ins_today' => 'Checking in today',
        'check_outs_today' => 'Checking out today',
        'leaving_soon' => 'Leaving soon',
        'checkout_overdue' => 'Checkout overdue',
        'payment_pending' => 'Payment pending',
        'complaints' => 'Complaints',
        'needs_extension' => 'Needs extension',
        'needs_checkout' => 'Needs checkout review',
        'needs_cleaning' => 'Needs cleaning',
        'only_needs_attention' => 'Show only stays that need attention',
        'property' => 'By property',
        'room' => 'By room',
        'sleeping_place' => 'By sleeping place',
    ],

    'payment_statuses' => [
        'paid' => 'Paid',
        'partial' => 'Partially paid',
        'pending' => 'Awaiting payment',
        'overdue' => 'Payment overdue',
        'refunded' => 'Refunded',
    ],

    'stay_statuses' => [
        'upcoming' => 'Waiting for check-in',
        'checked_in' => 'Checked in',
        'living_now' => 'Staying now',
        'check_out_today' => 'Checks out today',
        'checkout_overdue' => 'Checkout overdue',
        'checked_out' => 'Checked out',
        'no_show' => 'No-show',
        'cancelled' => 'Cancelled',
    ],

    'flags' => [
        'payment_pending' => 'Payment needs attention',
        'checkout_today' => 'Checkout is today',
        'checkout_overdue' => 'Checkout is overdue',
        'extension_requested' => 'Extension requested',
        'complaint_open' => 'Open complaint',
        'cleaning_needed' => 'Cleaning needed',
        'inspection_needed' => 'Inspection needed',
        'repair_needed' => 'Repair needed',
        'special_request' => 'Special request',
        'deposit_issue' => 'Deposit issue',
    ],

    'actions' => [
        'title' => 'Actions',
        'filters' => 'Filters',
        'reset_filters' => 'Reset',
        'details' => 'Details',
        'open_booking' => 'Open booking',
        'message_guest' => 'Message guest',
        'mark_checked_in' => 'Mark checked in',
        'mark_checked_out' => 'Mark checked out',
        'offer_extension' => 'Offer extension',
        'create_cleaning' => 'Create cleaning',
        'create_inspection' => 'Create inspection',
        'add_note' => 'Add note',
        'view_complaints' => 'View complaints',
        'mark_no_show' => 'Mark no-show',
        'start_checkout_process' => 'Start checkout review',
    ],

    'actions_results' => [
        'checkout_review_started' => 'Checkout review started.',
        'extension_offered' => 'Extension offer created.',
        'inspection_created' => 'Inspection note created.',
        'message_sent' => 'Message sent.',
    ],

    'cards' => [
        'title' => 'Occupant cards',
        'subtitle' => 'Each card shows the guest, room, dates, payment, stay state, contact option, requests, complaints, extension and checkout needs, and the host comment.',
    ],

    'loading' => [
        'refreshing' => 'Refreshing current occupants...',
    ],

    'values' => [
        'avatar_alt' => 'Photo of :name',
        'not_available' => 'Not available',
        'chat_available' => 'Chat is available',
        'contact_hidden' => 'Contact is hidden until this stay allows it',
        'no_special_requests' => 'No special requests',
        'rating' => ':rating / 5',
        'no_rating' => 'No guest rating yet',
        'complaints_count' => '{0} No complaints|{1} :count open complaint|[2,*] :count open complaints',
        'no_complaints' => 'No complaints',
        'no_host_comment' => 'No host comment yet',
        'yes' => 'Yes',
        'no' => 'No',
    ],

    'empty' => [
        'title' => 'No current occupants',
        'body' => 'When a confirmed stay overlaps today, it will appear here with room, place, dates, payment, notes, and attention flags.',
        'guest' => 'Guest',
        'room' => 'Room',
        'sleeping_place' => 'Sleeping place',
    ],

    'confirmations' => [
        'dangerous_action' => 'Please confirm this action before it changes the stay.',
        'mark_checked_out' => 'Confirm that the guest has checked out.',
        'start_checkout_process' => 'Start a manual checkout review.',
    ],

    'validation' => [
        'message_required' => 'Write a message before sending it.',
    ],
];
