<?php

return [
    'title' => 'Текущее проживание',
    'host_title' => 'Текущие жильцы',

    'fields' => [
        'booking' => 'Бронирование',
        'guest' => 'Гость',
        'host' => 'Хозяин',
        'property' => 'Помещение',
        'room' => 'Комната',
        'sleeping_place' => 'Спальное место',
        'check_in_date' => 'Дата въезда',
        'planned_check_out_date' => 'Плановая дата выезда',
        'actual_check_out_at' => 'Фактическое время выезда',
        'nights_count' => 'Количество ночей',
        'nights_passed' => 'Прошло ночей',
        'nights_remaining' => 'Осталось ночей',
        'payment_status' => 'Статус оплаты',
        'deposit_status' => 'Статус залога',
        'status' => 'Статус проживания',
        'host_note' => 'Комментарий хозяина',
        'current_occupants_count' => 'Текущие жильцы',
        'free_sleeping_places_count' => 'Свободные спальные места',
    ],

    'statuses' => [
        'not_started' => 'Не началось',
        'pending_check_in_confirmation' => 'Ожидает подтверждения заселения',
        'active' => 'Активно',
        'active_with_warning' => 'Активно с предупреждением',
        'extension_requested' => 'Запрошено продление',
        'extension_approved' => 'Продление одобрено',
        'relocation_requested' => 'Запрошено переселение',
        'relocation_scheduled' => 'Переселение запланировано',
        'checkout_soon' => 'Скоро выезд',
        'checkout_started' => 'Выезд начат',
        'guest_checked_out' => 'Гость выехал',
        'waiting_host_checkout_confirmation' => 'Ожидает подтверждения выезда хозяином',
        'waiting_inspection' => 'Ожидает проверки',
        'completed' => 'Завершено',
        'cancelled' => 'Отменено',
        'disputed' => 'Есть спор',
        'problem_reported' => 'Сообщена проблема',
        'closed' => 'Закрыто',
    ],

    'actions' => [
        'message_host' => 'Написать хозяину',
        'message_guest' => 'Написать гостю',
        'request_extension' => 'Запросить продление',
        'request_relocation' => 'Попросить переселение',
        'report_problem' => 'Сообщить о проблеме',
        'prepare_checkout' => 'Подготовиться к выезду',
        'add_note' => 'Добавить заметку',
        'save_note' => 'Сохранить заметку',
    ],

    'filters' => [
        'all' => 'Все',
        'checkout_today' => 'Сегодня выезд',
        'checkout_soon' => 'Скоро выезд',
        'complaints' => 'Есть жалоба',
        'payment_issue' => 'Проблема с оплатой',
        'extension_requested' => 'Запрошено продление',
        'relocation_requested' => 'Запрошено переселение',
        'by_property' => 'По помещению',
        'by_room' => 'По комнате',
    ],

    'components' => [
        'compatibility' => 'Совместимость с соседями',
        'checkout_soon' => 'Скоро выезд',
        'room_occupancy' => 'Занятость комнаты',
        'property_occupancy' => 'Занятость помещения',
    ],

    'messages' => [
        'current_stay_helper' => 'Ваше активное проживание, соседи и действия собраны в одном спокойном месте.',
        'host_residents_helper' => 'Короткий список гостей, которые сейчас живут в ваших местах.',
        'no_compatibility_warnings' => 'Сейчас нет важных предупреждений о соседях.',
        'note_saved' => 'Заметка по проживанию сохранена.',
    ],

    'compatibility' => [
        'guest_needs_quiet_but_room_has_late_sleepers' => 'В комнате может быть активность поздно вечером.',
        'guest_smokes_but_room_non_smoking' => 'Комната сейчас лучше подходит для некурящих гостей.',
        'guest_needs_late_entry_but_property_restricts_night_entry' => 'Поздний вход может быть ограничен правилами помещения.',
        'guest_wants_private_room_but_room_is_shared' => 'Это общая комната, не отдельная комната.',
        'guest_travels_with_pet_but_pets_not_allowed' => 'Животные для этого проживания не разрешены.',
        'guest_works_at_night_but_room_light_restricted' => 'Ночная работа может конфликтовать с правилами тихих часов.',
    ],

    'validation' => [
        'invalid_status_transition' => 'Проживание пока нельзя перевести в выбранный статус.',
    ],

    'empty' => [
        'no_current_stay' => 'Активного проживания пока нет.',
        'no_current_residents' => 'По этим фильтрам текущих жильцов нет.',
        'no_checkout_soon' => 'В ближайшее время никто не выезжает.',
    ],
];
