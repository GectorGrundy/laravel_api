<?php
declare(strict_types=1);

return [
    'on_create_error' => 'Невозможно создать оффер',
    'on_create_success' => 'Оффер успешно создан',

    'on_change_privacy_error' => 'Не удалось изменить настройки приватности',
    'on_change_privacy_success' => 'Настройки приватности изменены',

    'on_edit_error' => 'Невозможно внести изменения в оффер',
    'on_delete_error' => 'Не удалось архивировать оффер',
    'on_activate_error' => 'Не удалось активировать оффер',
    'on_force_delete_error' => 'Невозможно удалить оффер',
    'on_restore_error' => 'Невозможно восстановить оффер',
    'on_get_error' => 'Невозможно получить оффер',
    'on_get_list_error' => 'Невозможно получить список офферов',
    'on_get_list_not_found' => 'Офферы не найдены',
    'on_add_to_my_error' => 'Не удалось добавить оффер',
    'on_remove_from_my_error' => 'Не удалось удалить оффер',
    'on_sync_offer_sources_error' => 'Не удалось обновить источники траффика',
    'on_sync_categories_error' => 'Не удалось обновить категории',
    'on_sync_labels_error' => 'Не удалось обновить ярлыки',
    'on_clone_error' => 'Не удалось скопировать оффер',

    'on_edit_success' => 'Данные оффера успешно изменены',
    'on_delete_success' => 'Оффер архивирован',
    'on_activate_success' => 'Оффер активирован',
    'on_force_delete_success' => 'Оффер успешно удален',
    'on_restore_success' => 'Оффер успешно восстановлен',
    'on_add_to_my_success' => 'Оффер успешно добавлен',
    'on_remove_from_my_success' => 'Оффер успешно удален',
    'on_sync_offer_sources_success' => 'Источники трафика сохранены',
    'on_sync_categories_success' => 'Категории сохранены',
    'on_sync_labels_success' => 'Ярлыки сохранены',
    'on_clone_success' => 'Оффер скопирован',

    //Validation rules
    'offer_hash.exists' => 'Оффер с указанным offer_hash не найден',
    'hash.exists' => 'Оффер с указанным hash не найден',
    'title.notEmpty' => 'Поле title не может быть пустым',
    'title.max' => 'Превышена максимальная длина поля title',
    'currency.in' => 'Значение поля currency должно быть usd или rub',
    'is_private.in' => 'Значение поля is_private должно быть 0 или 1',

    'per_page.numeric' => 'Неверный формат поля per_page',
    'per_page.max' => 'Максимальное значения для поля per_page: 100',
    'page.numeric' => 'Неверный формат поля offset',
    'show_trashed.boolean' => 'Неверный формат поля show_trashed',
    'only_trashed.boolean' => 'Неверный формат поля only_trashed',
    'offers.required' => 'Выберите хотя бы 1 оффер',
    'offers_is_empty_string' => 'Значение offers не должно быть пустой строкой',
    'unique_my_list_error' => 'Указанный оффер уже добавлен',
    'not_exists_in_my_list_error' => 'Указанного оффера нет в списке добавленных',
    'targets.exists' => 'Нет целей или нет активной цели по умолчанию',
    'target.landings.exists' => 'не добавлено ни одного лендинга или нет публичный активных',
    'target.target_geo.exists' => 'не добавлено ни одной гео цели или нет по умолчанию',
    'target_geo_rules.exists' => 'не добавлено ни одного правила гео цели или нет по умолчанию',
    'eng_title_required_error' => 'Название EN обязательно для заполнения.',
    'flow.exists' => 'Нельзя закрыть доступ к офферу, так как он используется в потоках:',
    'publisher.flow' => 'У следующих пользователей есть потоки с этим оффером:',
    'user_group.flow' => 'У следующих пользователей этой группы есть активные потоки с этим оффером:'

];