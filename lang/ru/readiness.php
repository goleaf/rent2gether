<?php

return [
    'title' => 'Готовность места',

    'statuses' => [
        'draft' => 'Черновик',
        'checking' => 'Проверяем',
        'ready' => 'Готово',
        'not_ready' => 'Не готово',
        'blocked' => 'Заблокировано',
        'waiting_cleaning' => 'Ожидает уборку',
        'waiting_inspection' => 'Ожидает проверку',
        'waiting_repair' => 'Ожидает ремонт',
        'waiting_inventory' => 'Ожидает инвентарь',
        'waiting_access_setup' => 'Ожидает настройку доступа',
        'closed' => 'Закрыто',
    ],

    'checks' => [
        'checkout_completed' => 'Выезд завершён',
        'cleaning_completed' => 'Уборка завершена',
        'inspection_completed' => 'Проверка завершена',
        'repair_completed' => 'Ремонт завершён',
        'inventory_ready' => 'Инвентарь готов',
        'access_ready' => 'Доступ готов',
        'deposit_review_not_blocking' => 'Проверка залога не блокирует место',
        'complaint_not_blocking' => 'Жалоба не блокирует место',
        'calendar_available' => 'Календарь доступен',
    ],

    'reasons' => [
        'before_check_in' => 'Перед заездом',
        'after_checkout' => 'После выезда',
        'after_cleaning' => 'После уборки',
        'after_inspection' => 'После проверки',
        'after_repair' => 'После ремонта',
        'same_day_turnover' => 'Пересменка в тот же день',
        'manual' => 'Вручную',
    ],

    'messages' => [
        'place_ready' => 'Место готово к следующему гостю.',
        'place_not_ready' => 'Место ещё готовится.',
        'blocked_by_cleaning' => 'Место ожидает уборку.',
        'blocked_by_inspection' => 'Место ожидает проверку.',
        'blocked_by_repair' => 'Место ожидает ремонт.',
        'same_day_turnover_ok' => 'Времени между выездом и новым заездом достаточно.',
        'same_day_turnover_risky' => 'Может не хватить времени между выездом и новым заездом.',
        'guest_preparing' => 'Хозяин готовит место к вашему заезду.',
        'guest_ready' => 'Место готово к заселению.',
    ],

    'actions' => [
        'check' => 'Проверить готовность',
        'mark_ready' => 'Отметить готовым',
        'mark_not_ready' => 'Отметить неготовым',
    ],
];
