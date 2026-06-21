<?php

return [
    'host' => [
        'sleeping_place_title' => 'Sleeping place calendar',
        'sleeping_place_helper' => 'Edit one rentable place without changing other beds in the room.',
        'room_title' => 'Room calendar',
        'room_helper' => 'Review all sleeping places inside this room.',
        'property_title' => 'Property calendar',
        'property_helper' => 'Review rooms and sleeping places for this property.',
    ],
    'day_editor' => [
        'title' => 'Edit day',
        'helper' => 'Change one date without touching existing booking locks.',
    ],
    'block_sheet' => [
        'title' => 'Block period',
        'helper' => 'Add a safe period block for this sleeping place.',
    ],
    'bulk' => [
        'title' => 'Bulk dates',
        'helper' => 'Apply changes only where active locks are not present.',
    ],
    'turnover' => [
        'title' => 'Turnover rules',
        'helper' => 'Control same-day check-in, cleaning time, and inspection time.',
    ],
    'legend' => [
        'title' => 'Status legend',
        'helper' => 'Use these statuses to read the host calendar quickly.',
    ],
    'occupancy' => [
        'title' => 'Availability summary',
        'helper' => 'A compact count for the selected date range.',
        'blocked' => 'Blocked',
    ],
    'fields' => [
        'date' => 'Date',
        'status' => 'Status',
        'note' => 'Note',
        'starts_at' => 'Starts',
        'ends_at' => 'Ends',
        'block_type' => 'Block type',
        'from' => 'From',
        'to' => 'To',
    ],
    'actions' => [
        'save_day' => 'Save day',
        'create_block' => 'Create block',
        'save_turnover' => 'Save turnover rules',
    ],
    'empty' => [
        'days' => 'No calendar days for this range yet.',
    ],
    'validation_attributes' => [
        'date' => 'date',
        'status' => 'status',
        'note' => 'note',
        'startsAt' => 'start date',
        'endsAt' => 'end date',
        'blockType' => 'block type',
        'minGapMinutes' => 'minimum gap',
        'cleaningGapMinutes' => 'cleaning time',
        'inspectionGapMinutes' => 'inspection time',
        'earliestNewCheckInTime' => 'earliest check-in time',
        'latestPreviousCheckOutTime' => 'latest checkout time',
    ],
];
