<?php

return [
    'title' => 'Профиль гостя',
    'fields' => [
        'travel_purpose_default' => 'Цель поездки по умолчанию',
        'preferred_check_in_time' => 'Предпочтительное время заезда',
        'preferred_check_out_time' => 'Предпочтительное время выезда',
        'has_large_luggage' => 'Есть крупный багаж',
        'needs_luggage_storage' => 'Нужно место для багажа',
        'needs_quiet_place' => 'Нужно тихое место',
        'needs_desk' => 'Нужен стол',
        'needs_fast_wifi' => 'Нужен быстрый интернет',
        'needs_registration' => 'Нужна регистрация проживания',
        'needs_work_documents' => 'Нужны документы для отчётности',
        'smokes' => 'Курю',
        'travels_with_pet' => 'Путешествую с животным',
        'accepts_shared_room' => 'Готов жить в общей комнате',
        'prefers_private_room' => 'Предпочитаю отдельную комнату',
    ],
    'compatibility' => [
        'title' => 'Совместимость',
        'helper' => 'Ответы помогают объяснить правила, быт и подходит ли комната.',
        'i_like_quiet' => 'Мне важна тишина',
        'i_work_remotely' => 'Я работаю удалённо',
        'i_need_fast_internet' => 'Мне нужен быстрый интернет',
        'i_accept_living_with_strangers' => 'Я готов жить с незнакомыми людьми',
        'i_need_late_entry' => 'Мне важно возвращаться поздно',
    ],
    'warnings' => [
        'smoking_conflict' => 'Гость курит, а в месте запрещено курение.',
        'pet_conflict' => 'Гость едет с животным, а животные не разрешены.',
        'quiet_conflict' => 'Гостю нужна тишина, но комната может быть шумной.',
        'remote_work_conflict' => 'Гостю нужны условия для удалённой работы, которых может не быть.',
        'desk_conflict' => 'Гостю нужен стол, но в комнате его может не быть.',
        'late_entry_conflict' => 'Гостю нужен поздний вход, а место может не разрешать его.',
    ],
    'intake' => [
        'title' => 'Анкета перед бронированием',
        'trip_purpose' => 'Цель поездки',
    ],
];
