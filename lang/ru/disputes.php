<?php

return [
    'title' => 'Споры',
    'host_title' => 'Споры хозяина',

    'fields' => [
        'dispute_number' => 'Номер спора',
        'complaint' => 'Жалоба',
        'booking' => 'Бронирование',
        'opened_by' => 'Кто открыл',
        'dispute_type' => 'Тип спора',
        'severity' => 'Серьёзность',
        'status' => 'Статус',
        'amount_disputed' => 'Сумма спора',
        'proposed_resolution' => 'Предложенное решение',
        'final_resolution' => 'Итоговое решение',
        'booking_frozen' => 'Бронирование заморожено',
        'refund_frozen' => 'Возврат заморожен',
        'deposit_frozen' => 'Залог заморожен',
        'host_payout_frozen' => 'Выплата хозяину заморожена',
    ],

    'types' => [
        'refund_dispute' => 'Спор по возврату',
        'deposit_dispute' => 'Спор по залогу',
        'damage_dispute' => 'Спор по повреждению',
        'cancellation_dispute' => 'Спор по отмене',
        'no_show_dispute' => 'Спор по незаезду',
        'host_unresponsive_dispute' => 'Спор: хозяин не отвечал',
        'listing_mismatch_dispute' => 'Спор по несоответствию описанию',
        'payment_dispute' => 'Спор по оплате',
        'relocation_dispute' => 'Спор по переселению',
        'review_dispute_future' => 'Будущий спор по отзыву',
        'safety_dispute' => 'Спор по безопасности',
        'other' => 'Другое',
    ],

    'statuses' => [
        'draft' => 'Черновик',
        'opened' => 'Открыт',
        'evidence_requested' => 'Запрошены доказательства',
        'evidence_submitted' => 'Доказательства отправлены',
        'waiting_guest_response' => 'Ожидает ответ гостя',
        'waiting_host_response' => 'Ожидает ответ хозяина',
        'negotiation' => 'Переговоры',
        'resolution_proposed' => 'Предложено решение',
        'resolution_accepted' => 'Решение принято',
        'resolution_rejected' => 'Решение отклонено',
        'decision_pending_future' => 'Ожидает будущего решения',
        'decision_recorded_future' => 'Будущее решение записано',
        'refund_pending' => 'Ожидает возврат',
        'refund_completed' => 'Возврат завершён',
        'deposit_action_pending' => 'Ожидает действие по залогу',
        'deposit_action_completed' => 'Действие по залогу завершено',
        'booking_frozen' => 'Бронирование заморожено',
        'resolved' => 'Решено',
        'closed' => 'Закрыто',
        'cancelled' => 'Отменено',
    ],

    'resolution_types' => [
        'guest_refund_full' => 'Полный возврат гостю',
        'guest_refund_partial' => 'Частичный возврат гостю',
        'no_refund' => 'Без возврата',
        'deposit_return_full' => 'Полный возврат залога',
        'deposit_return_partial' => 'Частичный возврат залога',
        'deposit_deduction_approved' => 'Удержание залога одобрено',
        'deposit_deduction_rejected' => 'Удержание залога отклонено',
        'host_payout_full' => 'Полная выплата хозяину',
        'host_payout_partial' => 'Частичная выплата хозяину',
        'relocation_required' => 'Нужно переселение',
        'cancellation_confirmed' => 'Отмена подтверждена',
        'complaint_rejected' => 'Жалоба отклонена',
        'complaint_confirmed' => 'Жалоба подтверждена',
        'no_action' => 'Без действия',
        'future_manual_decision' => 'Будущее ручное решение',
    ],

    'freeze_labels' => [
        'booking_frozen' => 'Бронирование',
        'refund_frozen' => 'Возврат',
        'deposit_frozen' => 'Залог',
        'host_payout_frozen' => 'Выплата хозяину',
        'rating_impact_frozen' => 'Влияние на рейтинг',
    ],

    'proposal_statuses' => [
        'offered' => 'Предложено',
        'accepted_by_guest' => 'Принято гостем',
        'accepted_by_host' => 'Принято хозяином',
        'accepted_by_both' => 'Принято обеими сторонами',
        'rejected' => 'Отклонено',
        'expired' => 'Истекло',
        'cancelled' => 'Отменено',
        'applied' => 'Применено',
    ],

    'decision_types' => [
        'mutual_agreement' => 'Взаимное согласие',
        'system_rule' => 'Правило системы',
        'future_reviewer' => 'Будущая проверка',
    ],

    'actions' => [
        'open_dispute' => 'Открыть спор',
        'add_evidence' => 'Добавить доказательства',
        'send_message' => 'Отправить сообщение',
        'create_proposal' => 'Предложить решение',
        'accept_proposal' => 'Принять предложение',
        'reject_proposal' => 'Отклонить предложение',
        'close_dispute' => 'Закрыть спор',
    ],

    'messages' => [
        'dispute_opened' => 'Спор открыт.',
        'some_actions_frozen' => 'Некоторые действия могут быть временно заморожены до решения спора.',
        'proposal_sent' => 'Предложение отправлено.',
        'proposal_accepted' => 'Предложение принято.',
        'dispute_resolved' => 'Спор решён.',
    ],

    'notifications' => [
        'opened' => ['title' => 'Спор открыт', 'body' => 'По этому бронированию открыт спор.'],
        'evidence_requested' => ['title' => 'Запрошены доказательства', 'body' => 'Запрошены дополнительные доказательства по спору.'],
        'evidence_submitted' => ['title' => 'Доказательства добавлены', 'body' => 'В спор добавлены новые доказательства.'],
        'proposal_created' => ['title' => 'Создано предложение', 'body' => 'Создано предложение решения спора.'],
        'proposal_accepted' => ['title' => 'Предложение принято', 'body' => 'Предложение по спору принято.'],
        'proposal_rejected' => ['title' => 'Предложение отклонено', 'body' => 'Предложение по спору отклонено.'],
        'resolved' => ['title' => 'Спор решён', 'body' => 'Спор был решён.'],
        'closed' => ['title' => 'Спор закрыт', 'body' => 'Спор был закрыт.'],
    ],

    'validation' => [
        'cannot_upload_evidence' => 'Вы не можете добавить доказательства к этому спору.',
        'cannot_message' => 'Вы не можете писать в этом споре.',
    ],

    'empty' => [
        'no_disputes' => 'Пока нет споров.',
    ],
];
