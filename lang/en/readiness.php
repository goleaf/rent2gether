<?php

return [
    'title' => 'Place readiness',

    'statuses' => [
        'draft' => 'Draft',
        'checking' => 'Checking',
        'ready' => 'Ready',
        'not_ready' => 'Not ready',
        'blocked' => 'Blocked',
        'waiting_cleaning' => 'Waiting for cleaning',
        'waiting_inspection' => 'Waiting for inspection',
        'waiting_repair' => 'Waiting for repair',
        'waiting_inventory' => 'Waiting for inventory',
        'waiting_access_setup' => 'Waiting for access setup',
        'closed' => 'Closed',
    ],

    'checks' => [
        'checkout_completed' => 'Checkout completed',
        'cleaning_completed' => 'Cleaning completed',
        'inspection_completed' => 'Inspection completed',
        'repair_completed' => 'Repair completed',
        'inventory_ready' => 'Inventory ready',
        'access_ready' => 'Access ready',
        'deposit_review_not_blocking' => 'Deposit review does not block the place',
        'complaint_not_blocking' => 'Complaint does not block the place',
        'calendar_available' => 'Calendar available',
    ],

    'reasons' => [
        'before_check_in' => 'Before check-in',
        'after_checkout' => 'After checkout',
        'after_cleaning' => 'After cleaning',
        'after_inspection' => 'After inspection',
        'after_repair' => 'After repair',
        'same_day_turnover' => 'Same-day turnover',
        'manual' => 'Manual',
    ],

    'messages' => [
        'place_ready' => 'The place is ready for the next guest.',
        'place_not_ready' => 'The place is still being prepared.',
        'blocked_by_cleaning' => 'The place is waiting for cleaning.',
        'blocked_by_inspection' => 'The place is waiting for inspection.',
        'blocked_by_repair' => 'The place is waiting for repair.',
        'same_day_turnover_ok' => 'There is enough time between checkout and the next check-in.',
        'same_day_turnover_risky' => 'There may not be enough time between checkout and the next check-in.',
        'guest_preparing' => 'The host is preparing the place for your check-in.',
        'guest_ready' => 'The place is ready for check-in.',
    ],

    'actions' => [
        'check' => 'Check readiness',
        'mark_ready' => 'Mark ready',
        'mark_not_ready' => 'Mark not ready',
    ],
];
