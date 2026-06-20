<?php

return [
    'create' => [
        'title' => 'Create sleeping place',
        'helper' => 'This is the rentable unit with its own price, calendar, rules, and status.',
    ],

    'edit' => [
        'title' => 'Edit sleeping place',
        'helper' => 'Manage the exact place guests book and sleep in.',
    ],

    'public' => [
        'helper' => 'This page shows the sleeping place with room, property, and host context.',
        'price_title' => 'Price per night',
    ],

    'actions' => [
        'start' => 'Start sleeping place setup',
    ],

    'card' => [
        'base_price' => 'Base price',
        'price_value' => ':amount :currency',
    ],

    'types' => [
        'single' => 'Single bed',
        'double' => 'Double bed',
        'bunk_top' => 'Top bunk',
        'bunk_bottom' => 'Bottom bunk',
        'single_bed' => 'Single bed',
        'double_bed' => 'Double bed',
        'top_bunk' => 'Top bunk',
        'bottom_bunk' => 'Bottom bunk',
        'sofa' => 'Sofa',
        'mattress' => 'Mattress',
        'folding_bed' => 'Folding bed',
        'fold_out' => 'Folding bed',
        'capsule' => 'Capsule',
        'other' => 'Other',
    ],

    'publication' => [
        'missing' => [
            'title' => 'Add a title.',
            'room' => 'Choose a room.',
            'property' => 'Choose a property.',
            'place_type' => 'Choose a sleeping-place type.',
            'base_price' => 'Add a base price.',
        ],
        'recommended' => [
            'mattress' => 'Describe the mattress.',
            'locker' => 'Add locker information.',
            'socket' => 'Add socket information.',
            'bedding' => 'Add bedding information.',
        ],
    ],

    'empty' => [
        'not_found' => 'Sleeping place was not found.',
    ],
];
