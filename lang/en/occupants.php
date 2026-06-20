<?php

return [
    'title' => 'Who will live nearby',
    'confirmed_title' => 'Your roommates',
    'helper' => 'We show only safe details so you can understand the room atmosphere.',
    'no_occupants' => 'There are no other guests in this room for the selected dates yet.',
    'occupants_count' => 'There will be :count other guest(s) in the room.',
    'languages' => 'Languages: :languages',
    'long_term_guest' => 'Long-term guest',
    'short_term_guest' => 'Short-term guest',
    'tourist' => 'Tourist',
    'student' => 'Student',
    'working' => 'Working',
    'remote_worker' => 'Remote worker',
    'early_bird' => 'Early riser',
    'night_owl' => 'Night owl',
    'normal' => 'Regular schedule',
    'often_home' => 'Often home',
    'balanced' => 'Balanced home time',
    'rarely_home' => 'Rarely home',
    'smokes' => 'Smokes',
    'does_not_smoke' => 'Does not smoke',
    'social' => 'Social',
    'quiet' => 'Prefers quiet',
    'roommate_rating' => 'Roommate rating',
    'checkout_date' => 'Checkout: :date',
    'privacy_note' => 'Personal roommate details are hidden before a booking is confirmed.',

    'fields' => [
        'public_alias' => 'Public alias',
        'age_range' => 'Age range',
        'gender_for_room_policy' => 'Gender, when relevant to room format',
        'languages' => 'Languages',
        'stay_purpose' => 'Stay purpose',
        'guest_type' => 'Guest type',
        'sleep_schedule' => 'Sleep schedule',
        'wake_schedule' => 'Wake schedule',
        'home_presence_level' => 'Time spent at home',
        'smoking_location' => 'Smoking location',
        'has_pet' => 'Has a pet',
        'social_level' => 'Social style',
        'cleanliness_level' => 'Cleanliness style',
        'participates_in_cleaning' => 'Helps with cleaning',
        'respects_personal_space' => 'Respects personal space',
    ],

    'profile' => [
        'title' => 'Co-living profile',
        'helper' => 'Choose the calm details future roommates may use to understand the room atmosphere.',
        'lifestyle' => 'Lifestyle',
    ],

    'privacy' => [
        'title' => 'Roommate privacy',
        'helper' => 'You control what future roommates can see. Sensitive personal data is always hidden.',
        'show_public_alias' => 'Show public alias',
        'show_real_first_name' => 'Show real first name',
        'show_avatar' => 'Show avatar',
        'show_age_range' => 'Show age range',
        'show_gender_if_room_policy' => 'Show gender if room format needs it',
        'show_country' => 'Show country',
        'show_city' => 'Show city',
        'show_languages' => 'Show languages',
        'show_stay_purpose' => 'Show stay purpose',
        'show_guest_type' => 'Show guest type',
        'show_sleep_schedule' => 'Show sleep schedule',
        'show_wake_schedule' => 'Show wake schedule',
        'show_home_presence' => 'Show time spent at home',
        'show_smoking_status' => 'Show smoking status',
        'show_pet_status' => 'Show pet status',
        'show_social_level' => 'Show social style',
        'show_quiet_preference' => 'Show quiet preference',
        'show_cleanliness_level' => 'Show cleanliness style',
        'show_roommate_rating' => 'Show roommate rating',
        'show_checkout_date_to_future_roommates' => 'Show checkout date to future roommates',
        'allow_profile_in_prebooking_summary' => 'Allow safe pre-booking summary',
        'allow_profile_after_confirmed_booking' => 'Allow details after confirmed booking',
    ],

    'options' => [
        'not_set' => 'Not set',
        'age_ranges' => [
            '18_24' => '18-24',
            '25_34' => '25-34',
            '35_44' => '35-44',
            '45_54' => '45-54',
            '55_plus' => '55+',
        ],
        'gender' => [
            'female' => 'Female',
            'male' => 'Male',
            'not_specified' => 'Prefer not to say',
        ],
        'stay_purpose' => [
            'tourism' => 'Tourism',
            'work' => 'Work',
            'study' => 'Study',
            'temporary_housing' => 'Temporary housing',
        ],
        'schedule' => [
            'normal' => 'Regular schedule',
        ],
        'presence' => [
            'balanced' => 'Balanced',
        ],
        'social' => [
            'calm' => 'Calm',
        ],
        'cleanliness' => [
            'basic' => 'Basic',
            'tidy' => 'Tidy',
            'very_tidy' => 'Very tidy',
        ],
    ],

    'placeholders' => [
        'languages' => 'en, ru',
    ],

    'actions' => [
        'save_profile' => 'Save co-living profile',
        'save_privacy' => 'Save privacy settings',
        'saving' => 'Saving...',
    ],

    'messages' => [
        'profile_saved' => 'Co-living profile saved.',
        'privacy_saved' => 'Privacy settings saved.',
    ],

    'warnings' => [
        'quiet_conflict' => 'People in this room prefer quiet. Please consider this before booking.',
        'smoking_conflict' => 'Please note: smoking preferences may differ in this room.',
        'sleep_schedule_conflict' => 'Sleep schedules may differ from yours.',
        'home_presence_conflict' => 'Several guests spend a lot of time at home.',
        'room_full' => 'The room will be almost full for the selected dates.',
    ],

    'compatibility' => [
        'title' => 'Roommate fit',
        'helper' => 'We compare only safe lifestyle signals, never private details.',
        'no_warnings' => 'No major roommate warnings for the selected dates.',
    ],

    'host_preview' => [
        'title' => 'Room demand and occupants',
        'helper' => 'A privacy-safe view of who overlaps these dates.',
        'privacy' => 'Private guest details remain hidden.',
    ],
];
