<?php

return [
    'title' => 'Кто будет жить рядом',
    'confirmed_title' => 'Ваши соседи по комнате',
    'helper' => 'Мы показываем только безопасную информацию, чтобы вы понимали атмосферу комнаты.',
    'no_occupants' => 'На выбранные даты в комнате пока нет других гостей.',
    'occupants_count' => 'В комнате будет ещё :count гостей.',
    'languages' => 'Языки: :languages',
    'long_term_guest' => 'Долгосрочный жилец',
    'short_term_guest' => 'Краткосрочный гость',
    'tourist' => 'Турист',
    'student' => 'Студент',
    'working' => 'Работает',
    'remote_worker' => 'Работает удалённо',
    'early_bird' => 'Рано встаёт',
    'night_owl' => 'Поздно ложится',
    'normal' => 'Обычный график',
    'often_home' => 'Часто дома',
    'balanced' => 'Средне бывает дома',
    'rarely_home' => 'Редко дома',
    'smokes' => 'Курит',
    'does_not_smoke' => 'Не курит',
    'social' => 'Общительный',
    'quiet' => 'Предпочитает тишину',
    'roommate_rating' => 'Рейтинг как сосед',
    'checkout_date' => 'Выезд: :date',
    'privacy_note' => 'Личные данные жильцов скрыты до подтверждения бронирования.',
    'private_occupant' => 'Жилец с приватным профилем',

    'fields' => [
        'public_alias' => 'Псевдоним',
        'occupant' => 'Жилец',
        'room' => 'Комната',
        'sleeping_place' => 'Спальное место',
        'age_range' => 'Возрастной диапазон',
        'location' => 'Страна или город',
        'city' => 'Город',
        'gender_for_room_policy' => 'Пол, если важен для формата комнаты',
        'languages' => 'Языки общения',
        'stay_purpose' => 'Цель проживания',
        'checkout_date' => 'Дата выезда',
        'guest_type' => 'Тип гостя',
        'sleep_schedule' => 'График сна',
        'smoking_status' => 'Курение',
        'sociability_level' => 'Общительность',
        'wake_schedule' => 'График подъёма',
        'home_presence_level' => 'Как часто бываете дома',
        'smoking_location' => 'Где курите',
        'has_pet' => 'Есть животное',
        'social_level' => 'Уровень общительности',
        'quiet_preference' => 'Предпочтение тишины',
        'roommate_rating' => 'Рейтинг как сосед',
        'cleanliness_level' => 'Отношение к чистоте',
        'participates_in_cleaning' => 'Участвует в уборке',
        'respects_personal_space' => 'Уважает личное пространство',
    ],

    'occupant_types' => [
        'main_guest' => 'Основной гость',
        'second_guest' => 'Второй гость',
        'group_member' => 'Участник группы',
        'child_future' => 'Ребёнок',
    ],

    'purposes' => [
        'tourist' => 'Турист',
        'student' => 'Студент',
        'work' => 'Работает',
        'business_trip' => 'Командировка',
        'medical' => 'Лечение',
        'relocation' => 'Переезд',
        'housing_search' => 'Поиск жилья',
        'long_term_resident' => 'Долгосрочный жилец',
        'short_term_guest' => 'Краткосрочный гость',
        'other' => 'Другое',
    ],

    'sleep_schedules' => [
        'wakes_up_early' => 'Рано встаёт',
        'sleeps_late' => 'Поздно ложится',
        'works_at_night' => 'Работает ночью',
        'often_at_home' => 'Часто дома',
        'rarely_at_home' => 'Редко дома',
    ],

    'wake_schedules' => [
        'early' => 'Рано встаёт',
        'normal' => 'Обычное время подъёма',
        'late' => 'Поздно встаёт',
    ],

    'smoking_statuses' => [
        'smokes' => 'Курит',
        'does_not_smoke' => 'Не курит',
    ],

    'sociability' => [
        'social' => 'Общительный',
        'prefers_quiet' => 'Предпочитает тишину',
    ],

    'profile' => [
        'title' => 'Профиль совместного проживания',
        'helper' => 'Выберите спокойные детали, которые помогут будущим соседям понять атмосферу комнаты.',
        'lifestyle' => 'Образ жизни',
    ],

    'privacy' => [
        'title' => 'Приватность соседей',
        'helper' => 'Вы управляете тем, что видно будущим соседям. Чувствительные данные всегда скрыты.',
        'show_public_alias' => 'Показывать псевдоним',
        'show_real_first_name' => 'Показывать настоящее имя',
        'show_avatar' => 'Показывать аватар',
        'show_age_range' => 'Показывать возрастной диапазон',
        'show_gender_if_room_policy' => 'Показывать пол, если нужен формат комнаты',
        'show_country' => 'Показывать страну',
        'show_city' => 'Показывать город',
        'show_languages' => 'Показывать языки',
        'show_stay_purpose' => 'Показывать цель проживания',
        'show_guest_type' => 'Показывать тип гостя',
        'show_sleep_schedule' => 'Показывать график сна',
        'show_wake_schedule' => 'Показывать график подъёма',
        'show_home_presence' => 'Показывать, как часто бываете дома',
        'show_smoking_status' => 'Показывать отношение к курению',
        'show_pet_status' => 'Показывать животных',
        'show_social_level' => 'Показывать общительность',
        'show_quiet_preference' => 'Показывать предпочтение тишины',
        'show_cleanliness_level' => 'Показывать отношение к чистоте',
        'show_roommate_rating' => 'Показывать рейтинг как сосед',
        'show_checkout_date_to_future_roommates' => 'Показывать дату выезда будущим соседям',
        'allow_profile_in_prebooking_summary' => 'Разрешить безопасную сводку до бронирования',
        'allow_profile_after_confirmed_booking' => 'Разрешить детали после подтверждения бронирования',
    ],

    'options' => [
        'not_set' => 'Не указано',
        'age_ranges' => [
            '18_24' => '18-24',
            '25_34' => '25-34',
            '35_44' => '35-44',
            '45_54' => '45-54',
            '55_plus' => '55+',
        ],
        'gender' => [
            'female' => 'Женский',
            'male' => 'Мужской',
            'not_specified' => 'Не указывать',
        ],
        'stay_purpose' => [
            'tourism' => 'Туризм',
            'work' => 'Работа',
            'study' => 'Учёба',
            'temporary_housing' => 'Временное жильё',
        ],
        'schedule' => [
            'normal' => 'Обычный график',
        ],
        'presence' => [
            'balanced' => 'Средне',
        ],
        'social' => [
            'calm' => 'Спокойный',
        ],
        'cleanliness' => [
            'basic' => 'Обычно',
            'tidy' => 'Аккуратно',
            'very_tidy' => 'Очень аккуратно',
        ],
    ],

    'placeholders' => [
        'languages' => 'en, ru',
    ],

    'actions' => [
        'save_profile' => 'Сохранить профиль',
        'save_privacy' => 'Сохранить приватность',
        'saving' => 'Сохраняем...',
    ],

    'messages' => [
        'profile_saved' => 'Профиль совместного проживания сохранён.',
        'privacy_saved' => 'Настройки приватности сохранены.',
        'roommates_summary_private' => 'Мы показываем только безопасную краткую информацию о текущих соседях.',
        'no_current_roommates' => 'Сейчас в комнате нет других жильцов.',
        'current_roommates_count' => 'Сейчас в комнате: :count жильцов.',
        'roommate' => 'Сосед',
        'private_roommate' => 'Сосед с приватным профилем',
    ],

    'values' => [
        'age_range' => 'Возраст :age',
        'country_city' => ':country, :city',
        'occupant_summary' => ':name: :details',
        'private_occupant' => 'Жилец с приватным профилем',
        'rating' => ':rating / 5',
        'rating_with_reviews' => '{1}:rating / 5 по :count отзыву|[2,4]:rating / 5 по :count отзывам|[5,*]:rating / 5 по :count отзывам',
    ],

    'warnings' => [
        'quiet_conflict' => 'В комнате предпочитают тишину. Это важно учитывать перед бронированием.',
        'smoking_conflict' => 'Обратите внимание: отношение к курению может отличаться.',
        'sleep_schedule_conflict' => 'График сна может отличаться от вашего.',
        'home_presence_conflict' => 'Несколько гостей часто бывают дома.',
        'room_full' => 'На выбранные даты комната будет почти полной.',
    ],

    'compatibility' => [
        'title' => 'Совместимость с соседями',
        'helper' => 'Мы сравниваем только безопасные признаки образа жизни, без личных данных.',
        'no_warnings' => 'На выбранные даты нет важных предупреждений о соседях.',
    ],

    'host_preview' => [
        'title' => 'Спрос и жильцы комнаты',
        'helper' => 'Безопасная сводка пересечений по выбранным датам.',
        'privacy' => 'Приватные данные гостей скрыты.',
    ],
];
