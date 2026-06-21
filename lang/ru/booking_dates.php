<?php

return [
    'title' => 'Даты проживания',

    'fields' => [
        'check_in_date' => 'Дата въезда',
        'check_in_time' => 'Время въезда',
        'check_out_date' => 'Дата выезда',
        'check_out_time' => 'Время выезда',
        'guests_count' => 'Гостей',
        'nights_count' => 'Ночей',
        'chargeable_days_count' => 'Оплачиваемых суток',
        'calendar_presence_days_count' => 'Календарных дней',
        'early_check_in' => 'Ранний заезд',
        'late_check_out' => 'Поздний выезд',
        'flexible_check_in' => 'Гибкое время заезда',
        'flexible_check_out' => 'Гибкое время выезда',
        'requires_host_time_approval' => 'Нужно согласование времени с хозяином',
        'check_in_comment' => 'Комментарий по времени заезда',
        'check_out_comment' => 'Комментарий по времени выезда',
    ],

    'summary' => [
        'title' => 'Срок проживания',
    ],

    'time_preferences' => [
        'title' => 'Время заезда и выезда',
        'helper' => 'Добавьте только важные пожелания. Хозяин подтвердит особое время, если это нужно.',
    ],

    'warnings' => [
        'title' => 'Заметки по датам',
    ],

    'validation' => [
        'login_required' => 'Войдите, чтобы рассчитать проживание.',
        'checkout_before_checkin' => 'Дата выезда не может быть раньше даты въезда.',
        'checkout_same_day_not_allowed' => 'Дата выезда не может совпадать с датой въезда.',
        'sleeping_place_unavailable' => 'Это спальное место недоступно на выбранные даты.',
        'date_locked_by_another_booking' => 'Эти даты уже удерживаются или забронированы.',
        'property_blocked' => 'Это помещение недоступно на выбранные даты.',
        'room_blocked' => 'Эта комната недоступна на выбранные даты.',
        'room_repair' => 'В комнате идёт ремонт на выбранные даты.',
        'sleeping_place_repair' => 'Это спальное место на ремонте на выбранные даты.',
        'complaint_block' => 'Проживание недоступно из-за активной блокировки по жалобе.',
        'cleaning_gap_required' => 'Перед следующим гостем нужно время на уборку.',
        'inspection_gap_required' => 'После выезда нужна проверка.',
        'below_min_nights' => 'Выбранный срок меньше минимума: :count ночей.',
        'above_max_nights' => 'Выбранный срок больше максимума: :count ночей.',
        'check_in_weekday_not_allowed' => 'Заезд в этот день недели недоступен.',
        'check_out_weekday_not_allowed' => 'Выезд в этот день недели недоступен.',
        'guest_verification_required' => 'Для бронирования нужна проверка гостя.',
        'guest_age_not_allowed' => 'Правило возраста для этой комнаты не подходит гостю.',
        'room_gender_policy_mismatch' => 'Формат комнаты не подходит профилю гостя.',
        'guests_count_too_high' => 'Это спальное место подходит максимум для :count гостей.',
        'host_confirmation_required' => 'Хозяин должен подтвердить проживание.',
        'request_only' => 'Это спальное место доступно только по запросу.',
    ],

    'messages' => [
        'select_dates_helper' => 'Выберите даты для конкретного спального места. Расчёт ещё не является бронированием.',
        'select_check_in_first' => 'Сначала выберите дату въезда.',
        'available_check_out_dates' => 'Доступные даты выезда',
        'choose_checkout_helper' => 'Эти даты соответствуют текущим правилам доступности.',
        'price_recalculated' => 'Цена пересчитана.',
        'nearest_dates_found' => 'Мы нашли ближайшие свободные даты.',
        'nights_short' => ':count ночь|:count ночи|:count ночей',
    ],

    'empty' => [
        'select_check_in_first' => 'Выберите дату въезда, чтобы увидеть даты выезда.',
        'no_checkout_dates' => 'Для этой даты въезда нет доступных дат выезда.',
    ],
];
