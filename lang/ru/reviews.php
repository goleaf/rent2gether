<?php

return [
    'title' => 'Отзывы',

    'fields' => [
        'review' => 'Отзыв',
        'overall_rating' => 'Общая оценка',
        'public_comment' => 'Публичный комментарий',
        'private_comment' => 'Личный комментарий',
        'what_liked' => 'Что понравилось',
        'what_disliked' => 'Что не понравилось',
        'advice_to_future_guests' => 'Совет будущим гостям',
        'recommend' => 'Рекомендую',
        'wants_to_return' => 'Хочу вернуться',
        'status' => 'Статус',
        'submitted_at' => 'Отправлен',
        'published_at' => 'Опубликован',
        'due_at' => 'Срок до',
    ],

    'request_types' => [
        'guest_reviews_place' => 'Гость оценивает место',
        'host_reviews_guest' => 'Хозяин оценивает гостя',
        'guest_reviews_roommates' => 'Гость оценивает соседей',
        'guest_reviews_check_in' => 'Гость оценивает заселение',
        'guest_reviews_check_out' => 'Гость оценивает выезд',
        'guest_reviews_problem_resolution' => 'Гость оценивает решение проблемы',
    ],

    'subject_types' => [
        'property' => 'Помещение',
        'room' => 'Комната',
        'sleeping_place' => 'Спальное место',
        'host' => 'Хозяин',
        'guest' => 'Гость',
        'roommates' => 'Соседи',
        'check_in' => 'Заселение',
        'check_out' => 'Выезд',
        'problem_resolution' => 'Решение проблемы',
        'overall_booking' => 'Бронирование в целом',
    ],

    'statuses' => [
        'draft' => 'Черновик',
        'pending' => 'Ожидает',
        'submitted' => 'Отправлен',
        'waiting_other_party' => 'Ожидает вторую сторону',
        'pending_publish' => 'Ожидает публикации',
        'published' => 'Опубликован',
        'hidden' => 'Скрыт',
        'flagged' => 'Отмечен',
        'expired' => 'Срок истёк',
        'cancelled' => 'Отменён',
        'removed_future' => 'Удалён в будущем',
        'disputed_future' => 'Оспаривается в будущем',
        'closed' => 'Закрыт',
    ],

    'request_statuses' => [
        'created' => 'Создан',
        'sent' => 'Отправлен',
        'opened' => 'Открыт',
        'started' => 'Начат',
        'submitted' => 'Отправлен',
        'expired' => 'Срок истёк',
        'cancelled' => 'Отменён',
        'closed' => 'Закрыт',
    ],

    'roommates' => [
        'title' => 'Опыт с соседями',
    ],

    'roommate_fields' => [
        'quiet_roommates' => 'Соседи были тихие',
        'clean_roommates' => 'Соседи были чистоплотные',
        'friendly_roommates' => 'Соседи были дружелюбные',
        'roommates_disturbed_sleep' => 'Соседи мешали спать',
        'roommates_broke_rules' => 'Соседи нарушали правила',
        'conflict_happened' => 'Был конфликт',
    ],

    'filters' => [
        'all' => 'Все',
        'cleanliness' => 'Чистота',
        'noise' => 'Шум',
        'internet' => 'Интернет',
        'roommates' => 'Соседи',
        'photos' => 'С фото',
        'long_stay' => 'Долгое проживание',
        'short_stay' => 'Короткое проживание',
    ],

    'actions' => [
        'leave_review' => 'Оставить отзыв',
        'review_guest' => 'Оценить гостя',
        'submit_review' => 'Отправить отзыв',
        'edit_review' => 'Изменить отзыв',
        'respond_to_review' => 'Ответить на отзыв',
        'publish_response' => 'Опубликовать ответ',
        'upload_photo' => 'Добавить фото',
        'publish' => 'Опубликовать',
        'view_reviews' => 'Посмотреть отзывы',
    ],

    'messages' => [
        'review_requested' => 'Оставьте отзыв о проживании.',
        'double_blind_notice' => 'Отзыв будет опубликован, когда обе стороны оставят отзывы или когда истечёт срок.',
        'submitted' => 'Отзыв отправлен.',
        'published' => 'Отзыв опубликован.',
        'expired' => 'Срок для отзыва истёк.',
        'no_reviews_yet' => 'Отзывов пока нет.',
        'unconfirmed_no_rating_impact' => 'Неподтверждённые проблемы не влияют на рейтинг автоматически.',
        'roommate_privacy_notice' => 'Отзыв о соседях показывается только как безопасная сводка.',
        'photo_privacy_notice' => 'Фото с чувствительными деталями нельзя публиковать.',
        'public_review_helper' => 'Опубликованный отзыв виден по правилам приватности.',
        'host_review_helper' => 'Оцените гостя после реального завершённого проживания.',
        'host_reviews_helper' => 'Запросы отзывов и репутация основаны на завершённых проживаниях.',
    ],

    'validation' => [
        'not_author' => 'Только назначенный автор может отправить этот отзыв.',
        'published_not_editable' => 'Опубликованные отзывы нельзя редактировать.',
        'edit_window_closed' => 'Срок редактирования закончился.',
        'request_not_open' => 'Этот запрос отзыва больше не открыт.',
        'request_expired' => 'Срок запроса отзыва истёк.',
        'wrong_request_type' => 'Эта форма не подходит для запроса отзыва.',
        'overall_required' => 'Выберите общую оценку от 1 до 5.',
        'already_submitted' => 'Отзыв по этому бронированию уже отправлен.',
        'response_not_allowed' => 'Вы не можете ответить на этот отзыв.',
        'score_between' => 'Оценка должна быть от 1 до 5.',
    ],

    'empty_states' => [
        'requests' => 'Сейчас нет запросов отзывов.',
        'reviews' => 'Отзывы появятся здесь после публикации.',
    ],
];
