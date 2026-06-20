<?php

return [
    'title' => 'Профиль хозяина',
    'fields' => [
        'host_display_name' => 'Имя хозяина для гостей',
        'public_phone_visible' => 'Показывать телефон публично',
    ],
    'public' => [
        'title' => 'Профиль хозяина',
        'verified' => 'Хозяин подтверждён',
    ],
    'representatives' => [
        'title' => 'Представитель хозяина',
        'fields' => [
            'name' => 'Имя',
            'phone' => 'Телефон',
            'can_help_with_check_in' => 'Может помочь с заселением',
        ],
    ],
];
