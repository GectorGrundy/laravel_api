<?php

return [
    //on error
    'on_create_error' => 'Невозможно создать страну',
    'on_edit_error' => 'Невозможно внести изменения в страну',
    'on_delete_error' => 'Невозможно удалить страну',
    'on_get_error' => 'Невозможно получить страну',
    'on_get_list_error' => 'Невозможно получить список стран',
    'on_get_list_not_found' => 'Страны не найдены',
    'on_upload_flag_error' => 'Не удалось загрузить флаг',

    //on success
    'on_create_success' => 'Страна успешно создана',
    'on_edit_success' => 'Данные страны успешно изменены',
    'on_delete_success' => 'Страна успешно удалена',
    'on_upload_flag_success' => 'Флаг успешно загружен',

    //Validation rules
    'title.required' => 'Не задано поле title',
    'title.unique' => 'Страна с таким названием уже существует',
    'title.notEmpty' => 'Поле title не может быть пустым',
    'title.max' => 'Превышена максимальная длина поля title',

    'code.required' => 'Не задано поле code',
    'code.unique' => 'Страна с таким кодом уже существует',
    'code.max' => 'Превышена максимальная длина поля code',

    'has_related' => 'Невозможно удалить страну, к которой привязаны активные мобильные операторы',
];