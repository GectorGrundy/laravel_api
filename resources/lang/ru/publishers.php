<?php

return [
	//on error
	'on_create_error' => 'Ошибка регистрации',
	'on_edit_error' => 'Не удалось внести изменения в настройки профиля',
	'on_get_error' => 'Не удалось получить информацию об аккаунте',
	'on_get_source_list_error' => 'Не удалось получить список источников',
	'on_generate_api_key_error' => 'Не удалось сгенерировать API ключ',

	//on success
	'on_create_success' => 'Вы успешно прошли регистрацию',
	'on_edit_success' => 'Данные профиля успешно изменены',
	'on_generate_api_key_success' => 'Api ключ успешно сгенерирован',

	//Validation rules
	'email.unique' => 'Пользователь с таким email уже зарегистрирован в системе',
	'name.required' => 'Поле Имя обязательно для заполенения',
	'skype.required' => 'Поле Skype обязательно для заполенения',
	'password.required' => 'Пароль обязателен для заполнения',
	'payments_requisites_type.incorrect_type' => 'Неверный тип платежного реквизита',
	'incorrect_webmoney_usd_details' => 'Неверный формат долларового счета',
	'incorrect_webmoney_rub_details' => 'Неверный формат рублевого счета',

	//
	'registration_subject' => 'Вы успешно зарегистрировались в ' . config('app.name'),
	'registration_fast_title' => 'Вы успешно зарегистрировались в ' . config('app.name'),
	'registration_you_password' => 'Ваш пароль',
];