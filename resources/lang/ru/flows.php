<?php

return [
    //on error
    'on_create_error' => 'Не удалось создать поток',
    'on_edit_error' => 'Не удалось обновить настройки потока',
    'on_delete_error' => 'Не удалось скрыть поток',
    'on_get_error' => 'Не удалось получить информацию о потоке',
    'on_get_list_error' => 'Не удалось получить список потоков',
    'on_get_list_not_found' => 'Еще не создано ни одного потока',
    'forbidden_error' => 'У вас нет прав для выполнения данной операции',
    'on_clone_error' => 'Не удалось клонировать поток',

    //on success
    'on_create_success' => 'Поток успешно создан',
    'on_edit_success' => 'Настройки потока обновлены',
    'on_delete_success' => 'Поток скрыт',
    'on_clone_success' => 'Поток клонирован',

    //Validation rules
    'landings.required' => 'Выберите хотя бы один лендинг',
    'has_domain_relation' => 'Он связан со следующими доменами:',
    'tb_url.required_if' => 'Укажите traffic back ссылку',
    'tb_url.url' => 'Traffic back ссылка должна быть корректным URL',
    'target_hash.incorrect' => 'Некорректные данные для цели',
	'transit.belongs_error'=> 'Некоторые из выбранных прелендингов не принадлежат к выбранной цели или офферу',
	'landing.belongs_error'=> 'Некоторые из выбранных лендингов не принадлежат к выбранной цели или офферу',

    'default_title' => ':offer_title, :date'
];