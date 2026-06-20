<?php

return [
    'title' => 'Текущие жильцы',

    'sections' => [
        'page' => 'Текущие жильцы',
        'filters' => 'Фильтры',
        'summary' => 'Сводка проживания',
        'occupant_card' => 'Карточка жильца',
        'details_sheet' => 'Детали проживания',
        'quick_actions' => 'Быстрые действия',
        'payment_status_badge' => 'Статус оплаты',
        'stay_status_badge' => 'Статус проживания',
        'note_sheet' => 'Комментарий хозяина',
        'flags_panel' => 'Нужно внимание',
        'extension_panel' => 'Продление',
        'checkout_panel' => 'Выезд',
    ],

    'helpers' => [
        'page' => 'Посмотрите, кто живёт сейчас, кто заезжает, кто выезжает и где нужно внимание.',
        'filters' => 'Сузьте список по датам, оплате, комнате, месту или флагам внимания.',
        'summary' => 'Короткая сводка активных проживаний и важных дел на сегодня.',
        'occupant_card' => 'Мобильные карточки показывают самые нужные факты о проживании.',
        'details_sheet' => 'Откройте детали для оплаты, пожеланий, заметок, жалоб и связи.',
        'quick_actions' => 'Используйте безопасные действия из карточки. Рискованные действия требуют подтверждения.',
        'payment_status_badge' => 'Статус оплаты берётся из бронирования.',
        'stay_status_badge' => 'Статус проживания считается по датам и действиям заселения.',
        'note_sheet' => 'Личные заметки видит только хозяин.',
        'flags_panel' => 'Флаги показывают выезд, оплату, уборку, продление и жалобы.',
        'extension_panel' => 'Предложите или проверьте продление без скрытого изменения бронирования.',
        'checkout_panel' => 'Действия выезда помогают хозяину спокойно проверить проживание.',
    ],

    'summary' => [
        'current' => 'Сейчас живут: :count',
        'check_ins_today' => 'Сегодня заезжают: :count',
        'check_outs_today' => 'Сегодня выезжают: :count',
        'needs_attention' => 'Нужно внимание: :count',
        'payment_pending' => 'Ожидают оплату: :count',
        'complaints' => 'Жалобы: :count',
    ],

    'summary_labels' => [
        'current' => 'Сейчас',
        'check_ins_today' => 'Заезды',
        'check_outs_today' => 'Выезды',
        'needs_attention' => 'Внимание',
    ],

    'fields' => [
        'guest' => 'Гость',
        'guest_photo' => 'Фото гостя',
        'room' => 'Комната',
        'sleeping_place' => 'Спальное место',
        'check_in_date' => 'Дата въезда',
        'check_out_date' => 'Дата выезда',
        'nights_count' => 'Количество суток',
        'nights_left' => 'Осталось суток',
        'payment_status' => 'Статус оплаты',
        'stay_status' => 'Статус проживания',
        'guest_contact' => 'Контакт гостя',
        'special_requests' => 'Особые пожелания',
        'guest_rating' => 'Рейтинг гостя',
        'has_complaints' => 'Есть жалобы',
        'needs_extension' => 'Нужно продление',
        'needs_checkout' => 'Нужно проверить выезд',
        'host_comment' => 'Комментарий хозяина',
    ],

    'filters' => [
        'title' => 'Быстрые фильтры',
        'all' => 'Все текущие жильцы',
        'check_ins_today' => 'Заезжают сегодня',
        'check_outs_today' => 'Выезжают сегодня',
        'leaving_soon' => 'Скоро выезжают',
        'checkout_overdue' => 'Выезд просрочен',
        'payment_pending' => 'Ожидают оплату',
        'complaints' => 'Есть жалобы',
        'needs_extension' => 'Нужно продление',
        'needs_checkout' => 'Нужно проверить выезд',
        'needs_cleaning' => 'Нужна уборка',
        'property' => 'По помещению',
        'room' => 'По комнате',
        'sleeping_place' => 'По спальному месту',
    ],

    'payment_statuses' => [
        'paid' => 'Оплачено',
        'partial' => 'Частично оплачено',
        'pending' => 'Ожидает оплаты',
        'overdue' => 'Есть задолженность',
        'refunded' => 'Возвращено',
    ],

    'stay_statuses' => [
        'upcoming' => 'Ожидает заезда',
        'checked_in' => 'Заселён',
        'living_now' => 'Проживает сейчас',
        'check_out_today' => 'Выезд сегодня',
        'checkout_overdue' => 'Выезд просрочен',
        'checked_out' => 'Выехал',
        'no_show' => 'Не приехал',
        'cancelled' => 'Отменено',
    ],

    'flags' => [
        'payment_pending' => 'Оплата требует внимания',
        'checkout_today' => 'Выезд сегодня',
        'checkout_overdue' => 'Выезд просрочен',
        'extension_requested' => 'Запрошено продление',
        'complaint_open' => 'Есть открытая жалоба',
        'cleaning_needed' => 'Нужна уборка',
        'inspection_needed' => 'Нужна проверка',
        'repair_needed' => 'Нужен ремонт',
        'special_request' => 'Есть особое пожелание',
        'deposit_issue' => 'Вопрос по залогу',
    ],

    'actions' => [
        'title' => 'Действия',
        'filters' => 'Фильтры',
        'details' => 'Детали',
        'open_booking' => 'Открыть бронирование',
        'message_guest' => 'Написать гостю',
        'mark_checked_in' => 'Отметить заселение',
        'mark_checked_out' => 'Отметить выезд',
        'offer_extension' => 'Предложить продление',
        'create_cleaning' => 'Создать уборку',
        'create_inspection' => 'Создать проверку',
        'add_note' => 'Добавить комментарий',
        'view_complaints' => 'Открыть жалобы',
        'mark_no_show' => 'Отметить незаезд',
        'start_checkout_process' => 'Начать проверку выезда',
    ],

    'actions_results' => [
        'checkout_review_started' => 'Проверка выезда начата.',
        'extension_offered' => 'Предложение продления создано.',
        'inspection_created' => 'Проверка создана.',
        'message_sent' => 'Сообщение отправлено.',
    ],

    'cards' => [
        'title' => 'Карточки жильцов',
    ],

    'empty' => [
        'title' => 'Сейчас нет жильцов',
        'body' => 'Когда подтверждённое проживание пересечётся с сегодняшней датой, оно появится здесь с комнатой, местом, датами, оплатой, заметками и флагами внимания.',
        'guest' => 'Гость',
        'room' => 'Комната',
        'sleeping_place' => 'Спальное место',
    ],

    'confirmations' => [
        'dangerous_action' => 'Подтвердите действие перед изменением проживания.',
        'mark_checked_out' => 'Подтвердите, что гость выехал.',
        'start_checkout_process' => 'Начать ручную проверку выезда.',
    ],

    'validation' => [
        'message_required' => 'Напишите сообщение перед отправкой.',
    ],
];
