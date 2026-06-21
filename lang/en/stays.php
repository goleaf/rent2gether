<?php

return [
    'title' => 'Current stay',
    'host_title' => 'Current residents',

    'fields' => [
        'booking' => 'Booking',
        'guest' => 'Guest',
        'host' => 'Host',
        'property' => 'Property',
        'room' => 'Room',
        'sleeping_place' => 'Sleeping place',
        'check_in_date' => 'Check-in date',
        'planned_check_out_date' => 'Planned check-out date',
        'actual_check_out_at' => 'Actual check-out time',
        'nights_count' => 'Nights',
        'nights_passed' => 'Nights passed',
        'nights_remaining' => 'Nights remaining',
        'payment_status' => 'Payment status',
        'deposit_status' => 'Deposit status',
        'status' => 'Stay status',
        'host_note' => 'Host note',
        'current_occupants_count' => 'Current residents',
        'free_sleeping_places_count' => 'Free sleeping places',
    ],

    'statuses' => [
        'not_started' => 'Not started',
        'pending_check_in_confirmation' => 'Waiting for check-in confirmation',
        'active' => 'Active',
        'active_with_warning' => 'Active with warning',
        'extension_requested' => 'Extension requested',
        'extension_approved' => 'Extension approved',
        'relocation_requested' => 'Relocation requested',
        'relocation_scheduled' => 'Relocation scheduled',
        'checkout_soon' => 'Check-out soon',
        'checkout_started' => 'Check-out started',
        'guest_checked_out' => 'Guest checked out',
        'waiting_host_checkout_confirmation' => 'Waiting for host check-out confirmation',
        'waiting_inspection' => 'Waiting for inspection',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'disputed' => 'Disputed',
        'problem_reported' => 'Problem reported',
        'closed' => 'Closed',
    ],

    'actions' => [
        'message_host' => 'Message host',
        'message_guest' => 'Message guest',
        'request_extension' => 'Request extension',
        'request_relocation' => 'Request relocation',
        'report_problem' => 'Report a problem',
        'prepare_checkout' => 'Prepare check-out',
        'add_note' => 'Add note',
        'save_note' => 'Save note',
    ],

    'filters' => [
        'all' => 'All',
        'checkout_today' => 'Checking out today',
        'checkout_soon' => 'Check-out soon',
        'complaints' => 'Complaints',
        'payment_issue' => 'Payment issue',
        'extension_requested' => 'Extension requested',
        'relocation_requested' => 'Relocation requested',
        'by_property' => 'By property',
        'by_room' => 'By room',
    ],

    'components' => [
        'compatibility' => 'Roommate fit',
        'checkout_soon' => 'Check-out soon',
        'room_occupancy' => 'Room occupancy',
        'property_occupancy' => 'Property occupancy',
    ],

    'messages' => [
        'current_stay_helper' => 'Your active stay, roommates, and next actions are kept in one calm place.',
        'host_residents_helper' => 'A focused view of guests currently living in your places.',
        'no_compatibility_warnings' => 'No important roommate warnings right now.',
        'note_saved' => 'Stay note saved.',
    ],

    'compatibility' => [
        'guest_needs_quiet_but_room_has_late_sleepers' => 'The room may be active late in the evening.',
        'guest_smokes_but_room_non_smoking' => 'The room is currently better suited for non-smokers.',
        'guest_needs_late_entry_but_property_restricts_night_entry' => 'Late-night entry may be restricted by property rules.',
        'guest_wants_private_room_but_room_is_shared' => 'This is a shared room, not a private room.',
        'guest_travels_with_pet_but_pets_not_allowed' => 'Pets are not allowed for this stay.',
        'guest_works_at_night_but_room_light_restricted' => 'Night work may conflict with quiet-hour rules.',
    ],

    'validation' => [
        'invalid_status_transition' => 'This stay cannot move to the selected status yet.',
    ],

    'empty' => [
        'no_current_stay' => 'There is no active stay to show yet.',
        'no_current_residents' => 'No current residents match these filters.',
        'no_checkout_soon' => 'No residents are checking out soon.',
    ],
];
