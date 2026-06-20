<?php

return [
    'title' => 'Calendar and prices',
    'helper' => 'Set availability, prices, check-in rules, and cleaning gap for each sleeping place.',
    'quick_open' => 'Quick date range',
    'quick_open_helper' => 'Open or close a simple period without a large desktop calendar.',
    'rules_title' => 'Calendar rules',
    'rules_helper' => 'Set default price, stay limits, check-in days, check-out days, and cleaning gap.',
    'empty_places' => 'Add a sleeping place before configuring the calendar.',

    'fields' => [
        'sleeping_place' => 'Sleeping place',
        'start_date' => 'Start date',
        'end_date' => 'End date',
        'price' => 'Price',
        'default_price' => 'Default price',
        'min_nights' => 'Minimum nights',
        'max_nights' => 'Maximum nights',
        'cleaning_gap_hours' => 'Cleaning gap hours',
        'cleaning_gap_days' => 'Cleaning gap days',
        'check_in_time_from' => 'Check-in from',
        'check_out_time_until' => 'Check-out until',
        'check_in_days' => 'Allowed check-in days',
        'check_out_days' => 'Allowed check-out days',
    ],

    'actions' => [
        'open_dates' => 'Open dates',
        'close_dates' => 'Close dates',
        'save_price' => 'Save price',
        'save_settings' => 'Save calendar settings',
    ],

    'weekdays' => [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun',
    ],
];
