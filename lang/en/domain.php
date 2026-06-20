<?php

return [
    'entities' => [
        'user' => 'User',
        'guest' => 'Guest',
        'host' => 'Host',
        'property' => 'Property',
        'room' => 'Room',
        'sleeping_place' => 'Sleeping place',
        'booking' => 'Booking',
        'stay' => 'Stay',
    ],

    'role_modes' => [
        'guest' => 'Guest',
        'host' => 'Host',
        'guest_host' => 'Guest and host',
    ],

    'main_rule' => [
        'sleeping_place_is_rental_unit' => 'The main rentable unit is the sleeping place.',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'incomplete' => 'Incomplete',
        'ready_to_publish' => 'Ready to publish',
        'published' => 'Published',
        'active' => 'Active',
        'hidden' => 'Hidden',
        'paused' => 'Paused',
        'archived' => 'Archived',
        'unavailable' => 'Unavailable',
        'repair' => 'Repair',
        'maintenance' => 'Maintenance',
        'closed' => 'Closed',
    ],

    'errors' => [
        'host_mode_required' => 'Switch to host mode before creating host objects.',
        'not_property_owner' => 'You can edit only your own property.',
        'not_room_owner' => 'You can edit only rooms inside your own property.',
        'not_sleeping_place_owner' => 'You can edit only your own sleeping places.',
    ],
];
