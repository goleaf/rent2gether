<?php

return [
    'entities' => [
        'user' => 'Пользователь',
        'guest' => 'Гость',
        'host' => 'Хозяин',
        'property' => 'Помещение',
        'room' => 'Комната',
        'sleeping_place' => 'Спальное место',
        'booking' => 'Бронирование',
        'stay' => 'Проживание',
    ],

    'role_modes' => [
        'guest' => 'Гость',
        'host' => 'Хозяин',
        'guest_host' => 'Гость и хозяин',
    ],

    'main_rule' => [
        'sleeping_place_is_rental_unit' => 'Главная единица аренды — спальное место.',
    ],

    'statuses' => [
        'draft' => 'Черновик',
        'incomplete' => 'Не заполнено',
        'ready_to_publish' => 'Готово к публикации',
        'published' => 'Опубликовано',
        'active' => 'Активно',
        'hidden' => 'Скрыто',
        'paused' => 'На паузе',
        'archived' => 'В архиве',
        'unavailable' => 'Недоступно',
        'repair' => 'Ремонт',
        'maintenance' => 'Обслуживание',
        'closed' => 'Закрыто',
    ],

    'errors' => [
        'host_mode_required' => 'Переключитесь в режим хозяина, чтобы создавать объекты хозяина.',
        'not_property_owner' => 'Можно редактировать только своё помещение.',
        'not_room_owner' => 'Можно редактировать только комнаты в своём помещении.',
        'not_sleeping_place_owner' => 'Можно редактировать только свои спальные места.',
    ],
];
