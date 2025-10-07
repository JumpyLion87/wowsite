<?php

return [
    // Основные заголовки
    'ban_management' => 'Управление банами',
    'ban_management_description' => 'Управление банами пользователей и модерация',
    'ban_details' => 'Детали бана',
    'ban_details_description' => 'Подробная информация о бане',
    'create_ban' => 'Создать бан',
    'ban_information' => 'Информация о бане',
    'account_information' => 'Информация об аккаунте',
    'ban_history' => 'История банов',
    'quick_actions' => 'Быстрые действия',

    // Статистика
    'total_bans' => 'Всего банов',
    'active_bans' => 'Активные баны',
    'expired_bans' => 'Истекшие баны',
    'permanent_bans' => 'Постоянные баны',
    'inactive_bans' => 'Неактивные баны',

    // Фильтры и поиск
    'filter_by_status' => 'Фильтр по статусу',
    'all_statuses' => 'Все статусы',
    'search' => 'Поиск',
    'search_placeholder' => 'Поиск по имени, email или причине...',
    'filter' => 'Фильтровать',
    'clear' => 'Очистить',

    // Таблица
    'user' => 'Пользователь',
    'reason' => 'Причина',
    'banned_by' => 'Забанен',
    'ban_date' => 'Дата бана',
    'unban_date' => 'Дата разбана',
    'status' => 'Статус',
    'actions' => 'Действия',
    'view_details' => 'Просмотр деталей',
    'unban' => 'Разбанить',
    'delete' => 'Удалить',

    // Статусы
    'active' => 'Активный',
    'expired' => 'Истекший',
    'inactive' => 'Неактивный',
    'permanent' => 'Постоянный',
    'unknown' => 'Неизвестно',
    'system' => 'Система',

    // Форма создания бана
    'select_account' => 'Выберите аккаунт',
    'search_account_placeholder' => 'Введите имя пользователя или email...',
    'ban_reason' => 'Причина бана',
    'ban_reason_placeholder' => 'Укажите причину бана...',
    'ban_duration' => 'Длительность бана',
    'ban_duration_placeholder' => 'Количество дней (оставьте пустым для постоянного бана)',
    'ban_duration_help' => 'Оставьте пустым для постоянного бана',
    'ban_type' => 'Тип бана',
    'account_ban' => 'Бан аккаунта',
    'ip_ban' => 'Бан IP',
    'character_ban' => 'Бан персонажа',
    'account_ban' => 'Бан аккаунта',
    'ip_ban' => 'IP бан',
    'type' => 'Тип',
    'email' => 'Email',
    'not_provided' => 'Не указан',
    'ip_address' => 'IP адрес',
    'ip_address_placeholder' => 'Введите IP адрес (например: 192.168.1.1)',
    'ip_address_help' => 'Введите IP адрес для бана. Можно использовать маски (например: 192.168.1.*)',
    'select_character' => 'Выберите персонажа',
    'search_character_placeholder' => 'Введите имя персонажа...',
    'select_account_for_character' => 'Выберите аккаунт для поиска персонажей',
    'select_character_first' => 'Сначала выберите аккаунт',
    'days' => 'дней',

    // Массовые операции
    'bulk_unban' => 'Разбанить выбранные',
    'bulk_delete' => 'Удалить выбранные',
    'apply_action' => 'Применить действие',
    'cancel' => 'Отмена',

    // Сообщения
    'no_bans_found' => 'Баны не найдены',
    'no_bans_description' => 'В системе пока нет банов',
    'no_reason' => 'Причина не указана',
    'ban_not_found' => 'Бан не найден',
    'account_already_banned' => 'Аккаунт уже забанен',
    'ban_created_successfully' => 'Бан успешно создан',
    'ban_creation_failed' => 'Ошибка при создании бана',
    'unban_successful' => 'Пользователь разбанен',
    'unban_failed' => 'Ошибка при разбане',
    'ban_deleted_successfully' => 'Бан удален',
    'ban_deletion_failed' => 'Ошибка при удалении бана',
    'bulk_unban_successful' => 'Разбанено :count банов',
    'bulk_delete_successful' => 'Удалено :count банов',
    'bulk_action_failed' => 'Ошибка при массовой операции',

    // Подтверждения
    'unban_confirm' => 'Вы уверены, что хотите разбанить этого пользователя?',
    'delete_confirm' => 'Вы уверены, что хотите удалить этот бан?',

    // Детали бана
    'username' => 'Имя пользователя',
    'email' => 'Email',
    'account_id' => 'ID аккаунта',
    'last_login' => 'Последний вход',
    'duration' => 'Длительность',
    'banned' => 'Забанен',
    'unbanned' => 'Разбанен',
    'by' => 'от',

    // Действия
    'view_account' => 'Просмотр аккаунта',
    'unban_account' => 'Разбанить аккаунт',
    'unban_character' => 'Разбанить персонажа',
    'unban_ip' => 'Разбанить IP',
    'ip_ban_info' => 'IP баны можно только удалить. Для разбана используйте удаление бана.',
    'delete_ban' => 'Удалить бан',
    'already_unbanned' => 'Уже разбанен',
    'back_to_bans' => 'Назад к банам',
    'back_to_list' => 'Назад к списку',
    'back_to_dashboard' => 'Назад к дашборду',
    
    // Новые ключи для разных типов банов
    'ip_already_banned' => 'IP адрес уже забанен.',
    'character_not_found' => 'Персонаж не найден.',
    'character_already_banned' => 'Персонаж уже забанен.',
];
