<?php

return [
    'title' => 'Guest profile',
    'fields' => [
        'travel_purpose_default' => 'Default trip purpose',
        'preferred_check_in_time' => 'Preferred check-in time',
        'preferred_check_out_time' => 'Preferred check-out time',
        'has_large_luggage' => 'Large luggage',
        'needs_luggage_storage' => 'Needs luggage storage',
        'needs_quiet_place' => 'Needs a quiet place',
        'needs_desk' => 'Needs a desk',
        'needs_fast_wifi' => 'Needs fast internet',
        'needs_registration' => 'Needs residence registration',
        'needs_work_documents' => 'Needs reporting documents',
        'smokes' => 'Smokes',
        'travels_with_pet' => 'Travels with a pet',
        'accepts_shared_room' => 'Accepts a shared room',
        'prefers_private_room' => 'Prefers a private room',
    ],
    'compatibility' => [
        'title' => 'Compatibility',
        'helper' => 'These answers help explain logistics and room fit.',
        'i_like_quiet' => 'Quiet is important to me',
        'i_work_remotely' => 'I work remotely',
        'i_need_fast_internet' => 'I need fast internet',
        'i_accept_living_with_strangers' => 'I can live with people I do not know',
        'i_need_late_entry' => 'I need late entry',
    ],
    'warnings' => [
        'smoking_conflict' => 'The guest smokes and the place has a no-smoking rule.',
        'pet_conflict' => 'The guest travels with a pet and pets are not allowed.',
        'quiet_conflict' => 'The guest needs quiet and the room may be noisy.',
        'remote_work_conflict' => 'The guest needs remote-work conditions that may be missing.',
        'desk_conflict' => 'The guest needs a desk and the room may not have one.',
        'late_entry_conflict' => 'The guest needs late entry and the place may not allow it.',
    ],
    'intake' => [
        'title' => 'Booking intake',
        'trip_purpose' => 'Trip purpose',
    ],
];
