<?php

return [
    //on error
    'on_create_error' => 'Не удалось создать аккаунт',
    'on_get_list_error' => 'Не удалось получить список пользователей',
    'on_block_error' => 'Ошибка блокировки пользователя',
    'on_unblock_error' => 'Ошибка разблокировки пользователя',
    'on_get_error' => 'Не удалось получить информацию о пользователе',
    'on_change_profile_error' => 'Не удалось изменить данные профиля',
    'on_change_passoword_error' => 'Не удалось изменить пароль',
    'on_create_statistic_filter_error' => 'Не удалось создать фильтр',
    'on_update_settings_error' => 'Не удалось сохранить параметры статистики',
    'on_advertiser_reg_error' => 'Ошибка',

    //on success
    'on_create_success' => 'Аккаунт успешно создан',
    'on_change_password_success' => 'Пароль успешно изменен',
    'on_change_profile_success' => 'Профиль успешно изменен',
    'on_block_success' => 'Пользователь заблокирован',
    'on_unblock_success' => 'Пользователь разблокирован',
    'on_create_statistic_filter_success' => 'Фильтр сохранен',
    'on_delete_statistic_filter_success' => 'Фильтр удален',
    'on_update_settings_success' => 'Параметры статистики сохранены',

    //Validation rules
    'name.required' => 'Не задано поле name',
    'email.required' => 'Не задано поле email',
    'email.email' => 'Неверный формат email',
    'email.unique' => 'В системе уже зарегистирован пользователь с данным email',
    'password.required' => 'Введите пароль',
    'password.min' => 'Пароль должен содержать минимум 8 символов',
    'password.incorrect' => 'Неверный старый пароль',
    'new_password.required' => 'Введите новый пароль',
    'new_password.min' => 'Новый пароль должен содержать минимум 8 символов',
    'tl.in' => 'Неверный формат поля tl',
    'skype.not_empty' => 'Неверный формат поля skype',
    'name.not_empty' => 'Неверный формат поля full_name',
    'support_id.exists' => 'Сотрудник службы поддержки с указанным support_id не найден',
    'full_name.required' => 'Поле Ф.И.О обязятельно для заполнения',
    'skype.required' => 'Поле Skype обязятельно для заполнения',
    'rub.required' => 'Рублевый счет обязятельный для заполнения',
    'usd.required' => 'Долларовый счет обязятельный для заполнения',
    'rub.not_editable' => 'Рублевый счет запрещен для редактирования',
    'usd.not_editable' => 'Долларовый счет запрещен для редактирования',
    'filter_id.exists' => 'Такого ярлыка не существует или доступ запрещен',
    'password.confirmed' => 'Вы неверно повторили новый пароль',
    '' => '',
];