<?php

return [
    'title' => 'Расчёт бронирования',

    'statuses' => [
        'draft' => 'Черновик',
        'valid' => 'Расчёт готов',
        'invalid' => 'Расчёт невозможен',
        'expired' => 'Расчёт истёк',
        'converted_to_booking' => 'Преобразован в бронирование',
        'converted_to_request' => 'Преобразован в запрос',
        'cancelled' => 'Отменён',
    ],

    'availability_statuses' => [
        'unchecked' => 'Доступность не проверена',
        'available' => 'Доступно',
        'unavailable' => 'Недоступно',
        'partially_unavailable' => 'Частично недоступно',
        'request_only' => 'Только по запросу',
    ],

    'validation_statuses' => [
        'unchecked' => 'Даты не проверены',
        'valid' => 'Даты подходят',
        'invalid' => 'Даты не подходят',
        'warnings' => 'Есть предупреждения',
    ],

    'pricing_statuses' => [
        'unchecked' => 'Цена не рассчитана',
        'calculated' => 'Цена рассчитана',
        'failed' => 'Не удалось рассчитать',
    ],

    'price' => [
        'accommodation' => 'Проживание',
        'discount' => 'Скидка',
        'cleaning_fee' => 'Сбор за уборку',
        'service_fee' => 'Комиссия сервиса',
        'deposit' => 'Залог',
        'total_without_deposit' => 'Итого без залога',
        'total_payable' => 'Итого к оплате',
        'refundable_amount' => 'Возвращаемая часть',
        'non_refundable_amount' => 'Невозвращаемая часть',
    ],

    'lines' => [
        'title' => 'Разбивка цены',
        'helper' => 'Каждая строка объясняет, из чего состоит предварительный расчёт.',
        'night' => 'Ночь',
        'weekday_night' => 'Ночь в будний день',
        'weekend_night' => 'Ночь выходного дня',
        'holiday_night' => 'Ночь праздника',
        'date_override' => 'Особая цена даты',
        'discount' => 'Скидка',
        'weekly_discount' => 'Недельная скидка',
        'monthly_discount' => 'Месячная скидка',
        'long_stay_discount' => 'Скидка за долгий срок',
        'early_booking_discount' => 'Скидка за раннее бронирование',
        'last_minute_discount' => 'Скидка в последний момент',
        'new_guest_discount' => 'Скидка новому гостю',
        'personal_discount' => 'Персональная скидка',
        'promo_discount' => 'Скидка по промокоду',
        'early_check_in_fee' => 'Плата за ранний заезд',
        'late_checkout_fee' => 'Плата за поздний выезд',
        'extra_guest_fee' => 'Плата за дополнительного гостя',
        'cleaning_fee' => 'Сбор за уборку',
        'service_fee' => 'Комиссия сервиса',
        'tax_future' => 'Налог',
        'city_fee_future' => 'Городской сбор',
        'deposit' => 'Возвращаемый залог',
        'other' => 'Другое',
        'refundable' => 'Возвращается',
    ],

    'validation' => [
        'title' => 'Сообщения расчёта',
    ],

    'cancellation' => [
        'title' => 'Предварительные условия отмены',
        'not_available' => 'Недоступно',
    ],

    'timeline' => [
        'title' => 'Важные даты',
        'payment_deadline' => 'Крайний срок оплаты',
        'free_cancellation_until' => 'Бесплатная отмена до',
        'cancellation_penalty_starts' => 'Начало штрафа за отмену',
        'guest_check_in_reminder' => 'Напоминание гостю о заезде',
        'host_check_in_reminder' => 'Напоминание хозяину о заезде',
        'guest_check_out_reminder' => 'Напоминание гостю о выезде',
        'host_check_out_reminder' => 'Напоминание хозяину о выезде',
        'deposit_review_start' => 'Начало проверки залога',
        'host_payout_due' => 'Дата выплаты хозяину',
        'review_request' => 'Запрос отзыва',
    ],

    'timeline_statuses' => [
        'pending' => 'Ожидает',
        'created' => 'Создано',
        'cancelled' => 'Отменено',
        'rescheduled' => 'Перенесено',
        'completed' => 'Завершено',
    ],

    'suggestions' => [
        'title' => 'Доступные варианты',
        'nearest_dates' => 'Попробуйте ближайшие даты.',
        'same_room_place' => 'В этой комнате есть другое доступное место.',
        'same_property_place' => 'В этом помещении есть другое доступное место.',
        'same_host_place' => 'У этого хозяина есть другое доступное место.',
        'similar_place' => 'Похожее место может подойти на эти даты.',
        'increase_budget' => 'Если увеличить бюджет, вариантов может быть больше.',
        'change_dates' => 'Попробуйте изменить даты.',
        'range' => ':check_in — :check_out, :nights ночей',
    ],

    'actions' => [
        'continue_to_booking' => 'Продолжить бронирование',
        'send_request' => 'Отправить запрос',
        'recalculate' => 'Пересчитать',
    ],

    'messages' => [
        'deposit_refundable' => ':amount — возвращаемый залог после выезда.',
        'quote_expired' => 'Этот расчёт истёк. Пересчитайте его перед продолжением.',
        'quote_expires_at' => 'Расчёт действует до :time.',
        'quote_not_available' => 'Этот расчёт недоступен.',
        'quote_recalculate_required' => 'Пересчитайте расчёт перед продолжением.',
    ],
];
