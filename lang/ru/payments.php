<?php

return [
    'title' => 'Оплата',

    'fields' => [
        'payment_number' => 'Номер платежа',
        'refund_number' => 'Номер возврата',
        'booking' => 'Бронирование',
        'amount' => 'Сумма',
        'currency' => 'Валюта',
        'payment_type' => 'Тип оплаты',
        'payment_method' => 'Способ оплаты',
        'payment_status' => 'Статус оплаты',
        'payment_deadline' => 'Крайний срок оплаты',
        'paid_at' => 'Дата оплаты',
        'failed_at' => 'Дата ошибки',
        'refundable_amount' => 'Возвращаемая часть',
        'non_refundable_amount' => 'Невозвращаемая часть',
        'remaining_amount' => 'Остаток к оплате',
        'attempt_number' => 'Попытка',
    ],

    'cards' => [
        'breakdown' => 'Разбивка оплаты',
        'attempts' => 'Попытки оплаты',
        'receipt' => 'Квитанция',
        'refund' => 'Возврат',
    ],

    'host' => [
        'status_title' => 'Оплата гостя',
        'summary_title' => 'Сводка оплаты',
        'refund_title' => 'Статус возврата',
    ],

    'statuses' => [
        'unpaid' => 'Не оплачено',
        'waiting_payment' => 'Ожидает оплаты',
        'payment_started' => 'Оплата начата',
        'pending' => 'В обработке',
        'partially_paid' => 'Оплачено частично',
        'paid' => 'Оплачено',
        'failed' => 'Ошибка оплаты',
        'expired' => 'Срок оплаты истек',
        'cancelled' => 'Отменено',
        'refunded' => 'Возвращено',
        'partially_refunded' => 'Частично возвращено',
        'disputed' => 'Есть спор по оплате',
    ],

    'attempt_statuses' => [
        'created' => 'Создана',
        'started' => 'Начата',
        'requires_action' => 'Требуется действие',
        'processing' => 'В обработке',
        'succeeded' => 'Успешно',
        'failed' => 'Не прошла',
        'cancelled' => 'Отменена',
        'expired' => 'Истекла',
        'provider_redirect_required' => 'Нужен переход к провайдеру',
        'provider_webhook_pending' => 'Ожидается подтверждение провайдера',
        'provider_confirmed' => 'Провайдер подтвердил',
        'provider_failed' => 'Провайдер отклонил',
    ],

    'methods' => [
        'internal_test' => 'Тестовая оплата',
        'card_future' => 'Банковская карта',
        'bank_transfer_future' => 'Банковский перевод',
        'cash_future' => 'Наличные',
        'manual_confirmation_future' => 'Ручное подтверждение',
        'wallet_future' => 'Кошелек',
        'promo_credit_future' => 'Бонусный баланс',
    ],

    'payment_types' => [
        'full_payment' => 'Полная оплата',
        'partial_payment' => 'Частичная оплата',
        'deposit_only' => 'Только залог',
        'remaining_balance' => 'Остаток к оплате',
        'extension_payment' => 'Оплата продления',
        'relocation_difference' => 'Разница при переселении',
        'manual_future' => 'Ручная оплата',
    ],

    'payment_purposes' => [
        'booking_payment' => 'Оплата бронирования',
        'deposit_payment' => 'Оплата залога',
        'extension_payment' => 'Оплата продления',
        'relocation_payment' => 'Оплата переселения',
        'price_difference_payment' => 'Оплата разницы в цене',
        'cleaning_fee_payment' => 'Оплата уборки',
        'service_fee_payment' => 'Оплата комиссии сервиса',
        'manual_adjustment_future' => 'Ручная корректировка',
    ],

    'allocation_types' => [
        'accommodation' => 'Проживание',
        'cleaning_fee' => 'Уборка',
        'guest_service_fee' => 'Комиссия сервиса',
        'deposit' => 'Залог',
        'tax_future' => 'Налог',
        'city_fee_future' => 'Городской сбор',
        'extra_guest_fee' => 'Дополнительный гость',
        'early_check_in_fee' => 'Ранний заезд',
        'late_checkout_fee' => 'Поздний выезд',
        'extension_amount' => 'Продление',
        'relocation_difference' => 'Разница при переселении',
        'other' => 'Другое',
    ],

    'refund_types' => [
        'full_refund' => 'Полный возврат',
        'partial_refund' => 'Частичный возврат',
        'deposit_refund' => 'Возврат залога',
        'cleaning_fee_refund' => 'Возврат сбора за уборку',
        'service_fee_refund' => 'Возврат комиссии сервиса',
        'cancellation_refund' => 'Возврат при отмене',
        'relocation_refund' => 'Возврат при переселении',
        'overpayment_refund' => 'Возврат переплаты',
        'manual_future' => 'Ручной возврат',
    ],

    'refund_statuses' => [
        'pending' => 'Ожидает',
        'approved' => 'Одобрен',
        'processing' => 'В обработке',
        'completed' => 'Завершен',
        'failed' => 'Ошибка',
        'cancelled' => 'Отменен',
    ],

    'refund_reasons' => [
        'guest_cancelled' => 'Отмена гостем',
        'partial_adjustment' => 'Частичная корректировка',
        'deposit_return' => 'Возврат залога',
    ],

    'deadline_types' => [
        'initial_payment' => 'Первичная оплата',
        'remaining_balance' => 'Остаток к оплате',
        'deposit_payment' => 'Оплата залога',
        'extension_payment' => 'Оплата продления',
        'relocation_payment' => 'Оплата переселения',
        'manual_future' => 'Ручной срок',
    ],

    'deadline_statuses' => [
        'pending' => 'Ожидает',
        'completed' => 'Завершен',
        'expired' => 'Истек',
        'cancelled' => 'Отменен',
        'extended' => 'Продлен',
    ],

    'receipt_statuses' => [
        'draft' => 'Черновик',
        'issued' => 'Выдана',
        'cancelled' => 'Отменена',
        'failed' => 'Ошибка',
    ],

    'actions' => [
        'pay' => 'Оплатить',
        'retry_payment' => 'Попробовать снова',
        'cancel_payment' => 'Отменить оплату',
        'open_receipt' => 'Открыть квитанцию',
        'change_payment_method' => 'Сохранить способ оплаты',
    ],

    'messages' => [
        'payment_required' => 'Для подтверждения бронирования нужна оплата.',
        'payment_succeeded' => 'Оплата прошла успешно.',
        'payment_failed' => 'Оплата не прошла.',
        'payment_expired' => 'Срок оплаты истек.',
        'locks_released' => 'Даты больше не удерживаются.',
        'deposit_refundable' => 'Эта часть возвращается после выезда, если нет проблем.',
    ],

    'validation' => [
        'not_allowed' => 'Вы не можете управлять этим платежом.',
        'deadline_expired' => 'Срок оплаты истек.',
    ],

    'empty_states' => [
        'no_allocations' => 'Строк оплаты пока нет.',
        'no_deadline' => 'Срок оплаты не установлен.',
        'no_attempts' => 'Попыток оплаты пока нет.',
        'no_receipt' => 'Квитанция пока не создана.',
        'no_payment_methods' => 'Способы оплаты пока недоступны.',
        'no_refunds' => 'Возвратов пока нет.',
    ],
];
