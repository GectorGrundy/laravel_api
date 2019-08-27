<?php
declare(strict_types=1);

return [
    'on_create_success' => 'Домен успешно припаркован',
    'on_create_error' => 'Не удалось припарковать домен',

    'on_edit_success' => 'Домен успешно отредактирован',
    'on_edit_error' => 'Не удалось отредактировать домен',

    'on_get_list_error' => 'Не удалось получить список доменов',
    'on_get_error' => 'Не удалось получить домен',

    'on_delete_success' => 'Домен удален',
    'on_delete_error' => 'Не удалось удалить домен',

    'on_clear_cache_error' => 'Не удалось удалить кеш',
    'on_clear_cache_success' => 'Кеш успешно удален',

    'on_activate_error' => 'Не удалось активировать домен',
    'on_activate_success' => 'Домен активирован',

    'on_deactivate_error' => 'Не удалось отключить домен',
    'on_deactivate_success' => 'Домен отключен',

    'domain.incorrect' => 'Неверный формат домена',
    'domain.unique' => 'Данный домен уже припаркован',
    'domain.has_relation' => 'Он связан со следующими потоками:',
    'flow.incorrect' => 'Неверный hash потока',
    'flow_transit.incorrect' => 'Некорректный прелендинг для оффера потока',
    'flow_landing.incorrect' => 'Некорректный лендинг для оффера потока',
    'domain.service_cant_park' => 'Нельзя парковать системные домены',
    'realpath.incorrect' => 'Не найден index.html файл по указанном пути',
    'publisher.incorrect_type' => 'Паблишеры могут добавлять только припаркованные домены',
    'is_last' => 'Нельзя отключать последний домен',
];
